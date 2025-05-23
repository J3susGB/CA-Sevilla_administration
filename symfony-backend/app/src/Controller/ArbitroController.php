<?php

namespace App\Controller;

use App\Entity\Arbitros;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/arbitros')]
final class ArbitroController extends AbstractController
{
    public function __construct(
        private readonly ArbitrosRepository $repository,
        private readonly CategoriasRepository $categoriasRepository,
        private readonly EntityManagerInterface $em
    ) {}

    private function forbidden(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'error'  => ['code' => 403, 'message' => 'No autorizado']
        ], 403);
    }

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CAPACITACION');
    }

    // LISTADO DE TODOS LOS ÁRBITROS — ADMIN y CAPACITACION
    #[Route('', name: 'arbitro_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $page   = max(1, (int) $request->query->get('page', 1));
        $limit  = max(1, min(10000, (int) $request->query->get('limit', 25)));
        $offset = ($page - 1) * $limit;

        $arbitros = $this->repository->findBy([], null, $limit, $offset);
        $total    = $this->repository->count([]);

        $data = array_map(fn(Arbitros $a) => [
            'id'             => $a->getId(),
            'name'           => $a->getName(),
            'first_surname'  => $a->getFirstSurname(),
            'second_surname' => $a->getSecondSurname(),
            'categoria_id'   => $a->getCategoria()->getId(),
        ], $arbitros);

        return $this->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total]
        ]);
    }

    // MOSTRAR ÁRBITRO por ID — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_show', methods: ['GET'])]
    public function show(Arbitros $arbitro): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'categoria_id'   => $arbitro->getCategoria()->getId(),
            ]
        ]);
    }

    // CREAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('', name: 'arbitro_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (
            empty($data['name'])
            || empty($data['first_surname'])
            || empty($data['categoria_id'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 400,
                    'message' => 'Campos obligatorios: name, first_surname, categoria_id'
                ]
            ], 400);
        }

        $categoria = $this->categoriasRepository->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        // Transformamos a mayúsculas nombre y apellidos
        $nombre    = mb_strtoupper($data['name']);
        $ape1      = mb_strtoupper($data['first_surname']);
        $ape2Raw   = $data['second_surname'] ?? null;
        $ape2      = $ape2Raw ? mb_strtoupper($ape2Raw) : null;

        $arbitro = new Arbitros();
        $arbitro->setName($data['name'])
            ->setFirstSurname($data['first_surname'])
            ->setSecondSurname($data['second_surname'] ?? null)
            ->setCategoria($categoria);

        $this->em->persist($arbitro);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'categoria_id'   => $categoria->getId(),
            ]
        ], 201);
    }

    // ACTUALIZAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_update', methods: ['PUT'])]
    public function update(Arbitros $arbitro, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (! \is_array($data)) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'JSON inválido']
            ], 400);
        }

        // Solo actualizo si vienen en el POST
        if (array_key_exists('name', $data) && $data['name'] !== $arbitro->getName()) {
            // Convertir a mayúsculas antes de guardar
            $arbitro->setName(mb_strtoupper($data['name']));
        }

        if (array_key_exists('first_surname', $data) && $data['first_surname'] !== $arbitro->getFirstSurname()) {
            // Convertir a mayúsculas antes de guardar
            $arbitro->setFirstSurname(mb_strtoupper($data['first_surname']));
        }

        // Para second_surname permito null explícito
        if (
            array_key_exists('second_surname', $data)
            && $data['second_surname'] !== $arbitro->getSecondSurname()
        ) {
            $sec = $data['second_surname'];
            // Convertir a mayúsculas, o dejar null si viene vacío
            $arbitro->setSecondSurname($sec !== null ? mb_strtoupper($sec) : null);
        }

        if (array_key_exists('categoria_id', $data)) {
            $cat = $this->categoriasRepository->find($data['categoria_id']);
            if (! $cat) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
                ], 404);
            }
            // Solo reasigno si ha cambiado
            if ($cat->getId() !== $arbitro->getCategoria()->getId()) {
                $arbitro->setCategoria($cat);
            }
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'categoria_id'   => $arbitro->getCategoria()->getId(),
            ]
        ]);
    }



    // ELIMINAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_delete', methods: ['DELETE'])]
    public function delete(Arbitros $arbitro): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($arbitro);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Árbitro eliminado']
        ]);
    }

    // CARGA MASIVA — ADMIN y CAPACITACION
    #[Route('/bulk-upload', name: 'arbitro_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $file = $request->files->get('file');
        if (! $file) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'No se ha subido ningún archivo']
            ], 400);
        }

        $created = [];
        $ignored = [];
        $tmpPath = null;

        try {
            // 1) Guardar temporalmente y leer el XLSX
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('arb_bulk_') . '.' . $file->getClientOriginalExtension();
            $file->move(\dirname($tmpPath), \basename($tmpPath));
            $sheet = IOFactory::load($tmpPath)->getActiveSheet();
            $rows  = $sheet->toArray();
            array_shift($rows); // eliminamos cabecera

            foreach ($rows as $i => $row) {
                if (count($row) < 14) {
                    $ignored[] = ['row' => $i + 1, 'reason' => 'Formato de fila inválido'];
                    continue;
                }

                // 2) Parseo de "Nombre" ➡ name, first_surname, second_surname
                $raw = trim((string)$row[2]);
                $givenName     = '';
                $firstSurname  = '';
                $secondSurname = null;

                if (str_contains($raw, ',')) {
                    [$partA, $partB] = array_map('trim', explode(',', $raw, 2));
                    $surnames        = preg_split('/\s+/', $partA, -1, PREG_SPLIT_NO_EMPTY);
                    $firstSurname    = $surnames[0] ?? '';
                    $secondSurname   = $surnames[1] ?? null;
                    $givenName       = $partB;
                } else {
                    $parts = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($parts) === 1) {
                        $givenName = $parts[0];
                    } elseif (count($parts) === 2) {
                        [$givenName, $firstSurname] = $parts;
                    } else {
                        $givenName     = array_shift($parts);
                        $firstSurname  = array_shift($parts);
                        $secondSurname = array_shift($parts) ?? null;
                    }
                }

                // 3) Normalizar y buscar categoría (columna índice 13)
                $catRaw   = trim((string)$row[13]);
                $catName  = ucfirst(strtolower($catRaw));
                $categoria = $this->categoriasRepository->findOneBy(['name' => $catName]);

                if (! $categoria) {
                    $ignored[] = [
                        'row'    => $i + 1,
                        'reason' => "Categoría “{$catRaw}” no encontrada"
                    ];
                    continue;
                }

                // 4) Comprobar si ya existe este árbitro
                $exists = $this->repository->findOneBy([
                    'name'            => $givenName,
                    'first_surname'   => $firstSurname,
                    'second_surname'  => $secondSurname
                ]);
                if ($exists) {
                    $ignored[] = [
                        'row'    => $i + 1,
                        'name'   => $givenName,
                        'reason' => 'Ya existe'
                    ];
                    continue;
                }

                // 5) Crear y persistir nuevo árbitro
                $arb = new Arbitros();
                $arb->setName($givenName)
                    ->setFirstSurname($firstSurname)
                    ->setSecondSurname($secondSurname)
                    ->setCategoria($categoria);

                $this->em->persist($arb);
                $created[] = [
                    'row'            => $i + 1,
                    'name'           => $givenName,
                    'first_surname'  => $firstSurname,
                    'second_surname' => $secondSurname,
                    'categoria_id'   => $categoria->getId(),
                ];
            }

            // 6) Guardar en base de datos
            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'data'   => [
                    'created_count' => count($created),
                    'ignored_count' => count($ignored),
                    'created'       => $created,
                    'ignored'       => $ignored,
                ]
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 500,
                    'message' => 'Error en carga masiva',
                    'details' => $e->getMessage()
                ]
            ], 500);
        } finally {
            // 7) Eliminar siempre el fichero temporal
            if ($tmpPath && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
