<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/api/users')]
final class UserController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // LISTADO DE TODOS LOS USUARIOS — Sólo ADMIN
    #[Route('', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        $users = $userRepository->findAll();
        $data = array_map(function (User $user) {
            return [
                'id'       => $user->getId(),
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'roles'    => $user->getRoles(),
            ];
        }, $users);

        return $this->json($data);
    }

    // LISTADO DE USUARIO POR ID — Sólo ADMIN
    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        return $this->json([
            'id'       => $user->getId(),
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'roles'    => $user->getRoles(),
        ]);
    }

    // CREACIÓN DE UN USUARIO — Sólo ADMIN puede crear y asignar roles
    #[Route('', name: 'user_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);

        // 4) Hashear la contraseña
        $hashed = $this->passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashed);

        // Acepta un array de roles, por defecto ROLE_USER si no se envía nada
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Usuario creado'], 201);
    }

    // EDITAR UN USUARIO POR ID — Sólo ADMIN o el propio usuario
    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    public function update(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Permitir a ADMIN o al usuario que está logueado sobre sí mismo
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $user) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        // Sólo ADMIN puede cambiar roles
        if (isset($data['roles'])) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->json(['error' => 'No autorizado a cambiar roles'], 403);
            }
            $user->setRoles($data['roles']);
        }

        $em->flush();

        return $this->json(['message' => 'Usuario actualizado']);
    }

    // ELIMINAR USUARIO POR ID — Sólo ADMIN
    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Usuario eliminado']);
    }

    #[Route('/bulk-upload', name: 'user_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request, EntityManagerInterface $em, UserRepository $repo): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'No autorizado'], 403);
        }

        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No se ha subido ningún archivo'], 400);
        }

        $tmpPath = sys_get_temp_dir() . '/' . uniqid('bulk_upload_') . '.' . $file->getClientOriginalExtension();
        $created = [];
        $ignored = [];

        try {
            $file->move(dirname($tmpPath), basename($tmpPath));
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            array_shift($rows); // elimina cabecera

            foreach ($rows as $i => $row) {
                if (count($row) < 4) {
                    throw new \Exception("Fila $i: faltan columnas (se esperaban 4)");
                }
                list($username, $email, $password, $roles) = $row;

                // Comprueba existencia por username o email
                $exists = $repo->findOneBy(['username' => $username])
                    || $repo->findOneBy(['email'    => $email]);

                if ($exists) {
                    $ignored[] = [
                        'username' => $username,
                        'email'    => $email,
                        'reason'   => 'Ya existe username o email'
                    ];
                    continue;
                }

                // Crear nuevo usuario
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $hashed = $this->passwordHasher->hashPassword($user, (string)$password);
                $user->setPassword($hashed);
                $user->setRoles(array_map('trim', explode(',', (string)$roles)));

                $em->persist($user);
                $created[] = [
                    'username' => $username,
                    'email'    => $email
                ];
            }

            $em->flush();

            return $this->json([
                'created' => $created,
                'ignored' => $ignored
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'error'   => 'Error en carga masiva',
                'details' => $e->getMessage()
            ], 500);
        } finally {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
