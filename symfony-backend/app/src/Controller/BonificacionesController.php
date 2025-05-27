<?php

namespace App\Controller;

use App\Entity\Bonificaciones;
use App\Repository\BonificacionesRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/bonificaciones')]
final class BonificacionesController extends AbstractController
{
    public function __construct(
        private readonly BonificacionesRepository $repository,
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

    #[Route('', name: 'bonificacion_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $page   = max(1, (int) $request->query->get('page', 1));
        $limit  = max(1, min(10000, (int) $request->query->get('limit', 250)));
        $offset = ($page - 1) * $limit;

        $bonificaciones = $this->repository->findBy([], null, $limit, $offset);
        $total          = $this->repository->count([]);

        $data = array_map(fn(Bonificaciones $b) => [
            'id'           => $b->getId(),
            'name'         => $b->getName(),
            'valor'        => $b->getValor(),
            'categoria_id' => $b->getCategoria()->getId(),
        ], $bonificaciones);

        return $this->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total]
        ]);
    }

    #[Route('/{id}', name: 'bonificacion_show', methods: ['GET'])]
    public function show(Bonificaciones $bonificacion): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'           => $bonificacion->getId(),
                'name'         => $bonificacion->getName(),
                'valor'        => $bonificacion->getValor(),
                'categoria_id' => $bonificacion->getCategoria()->getId(),
            ]
        ]);
    }

    #[Route('', name: 'bonificacion_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (
            empty($data['name']) ||
            empty($data['valor']) ||
            empty($data['categoria_id'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Campos obligatorios: name, valor, categoria_id']
            ], 400);
        }

        $categoria = $this->categoriasRepository->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        $bonificacion = new Bonificaciones();
        $bonificacion->setName($data['name'])
            ->setValor($data['valor'])
            ->setCategoria($categoria);

        $this->em->persist($bonificacion);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'           => $bonificacion->getId(),
                'name'         => $bonificacion->getName(),
                'valor'        => $bonificacion->getValor(),
                'categoria_id' => $categoria->getId(),
            ]
        ], 201);
    }

    #[Route('/{id}', name: 'bonificacion_update', methods: ['PUT'])]
    public function update(Bonificaciones $bonificacion, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (! is_array($data)) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'JSON inválido']
            ], 400);
        }

        if (array_key_exists('name', $data) && $data['name'] !== $bonificacion->getName()) {
            $bonificacion->setName($data['name']);
        }

        if (array_key_exists('valor', $data) && $data['valor'] !== $bonificacion->getValor()) {
            $bonificacion->setValor($data['valor']);
        }

        if (array_key_exists('categoria_id', $data)) {
            $cat = $this->categoriasRepository->find($data['categoria_id']);
            if (! $cat) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
                ], 404);
            }
            if ($cat->getId() !== $bonificacion->getCategoria()->getId()) {
                $bonificacion->setCategoria($cat);
            }
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'           => $bonificacion->getId(),
                'name'         => $bonificacion->getName(),
                'valor'        => $bonificacion->getValor(),
                'categoria_id' => $bonificacion->getCategoria()->getId(),
            ]
        ]);
    }

    #[Route('/{id}', name: 'bonificacion_delete', methods: ['DELETE'])]
    public function delete(Bonificaciones $bonificacion): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($bonificacion);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Bonificación eliminada']
        ]);
    }

    #[Route('/bulk-upload', name: 'bonificacion_bulk_upload', methods: ['POST'])]
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
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('bon_bulk_') . '.' . $file->getClientOriginalExtension();
            $file->move(dirname($tmpPath), basename($tmpPath));
            $sheet = IOFactory::load($tmpPath)->getActiveSheet();
            $rows  = $sheet->toArray();
            array_shift($rows);

            foreach ($rows as $i => $row) {
                if (count($row) < 3) {
                    $ignored[] = ['row' => $i + 1, 'reason' => 'Formato de fila inválido'];
                    continue;
                }

                $name  = trim((string)$row[0]);
                $valor = trim((string)$row[1]);
                $catId = (int)$row[2];

                $categoria = $this->categoriasRepository->find($catId);
                if (! $categoria) {
                    $ignored[] = ['row' => $i + 1, 'reason' => "Categoría ID {$catId} no encontrada"];
                    continue;
                }

                $exists = $this->repository->findOneBy([
                    'name'      => $name,
                    'valor'     => $valor,
                    'categoria' => $categoria
                ]);
                if ($exists) {
                    $ignored[] = ['row' => $i + 1, 'name' => $name, 'reason' => 'Ya existe'];
                    continue;
                }

                $b = new Bonificaciones();
                $b->setName($name)
                  ->setValor($valor)
                  ->setCategoria($categoria);

                $this->em->persist($b);
                $created[] = ['row' => $i + 1, 'name' => $name, 'valor' => $valor, 'categoria_id' => $categoria->getId()];
            }

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
                'error'  => ['code' => 500, 'message' => 'Error en carga masiva', 'details' => $e->getMessage()]
            ], 500);
        } finally {
            if ($tmpPath && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}