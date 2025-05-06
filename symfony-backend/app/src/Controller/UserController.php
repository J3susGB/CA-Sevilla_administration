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

#[Route('/users')]
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
}
