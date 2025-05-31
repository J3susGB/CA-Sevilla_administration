<?php

namespace App\Controller;

use App\Entity\Categorias;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categorias')]
final class CategoriaController extends AbstractController
{
    public function __construct(
        private readonly CategoriasRepository   $repository,
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
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CAPACITACION') || $this->isGranted('ROLE_INFORMACION');
    }

    #[Route('', name: 'categoria_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $page   = max(1, (int)$request->query->get('page', 1));
        $limit  = max(1, min(100, (int)$request->query->get('limit', 25)));
        $offset = ($page - 1) * $limit;

        // Orden alfabético ascendente por 'name'
        $categorias = $this->repository->findBy(
            [],                         // sin filtros
            ['name' => 'ASC'],          // ordenar por nombre A→Z
            $limit,
            $offset
        );
        $total = $this->repository->count([]);

        $data = array_map(fn(Categorias $c) => [
            'id'     => $c->getId(),
            'nombre' => $c->getName(),
        ], $categorias);

        return $this->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ]);
    }

    #[Route('/{id}', name: 'categoria_show', methods: ['GET'])]
    public function show(Categorias $categoria): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'     => $categoria->getId(),
                'nombre' => $categoria->getName(),
            ]
        ]);
    }

    #[Route('', name: 'categoria_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['nombre'])) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'El campo nombre es obligatorio']
            ], 400);
        }

        // 1) Pasa todo a minúsculas
        $normalized = mb_strtolower($data['nombre'], 'UTF-8');
        // 2) Primera letra en mayúscula
        $normalized = mb_strtoupper(mb_substr($normalized, 0, 1), 'UTF-8')
                      . mb_substr($normalized, 1);

        $cat = new Categorias();
        $cat->setName($normalized);
        $this->em->persist($cat);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['id' => $cat->getId(), 'nombre' => $cat->getName()]
        ], 201);
    }

    #[Route('/{id}', name: 'categoria_update', methods: ['PUT'])]
    public function update(Categorias $categoria, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['nombre'])) {
            // Normalizar de la misma forma
            $normalized = mb_strtolower($data['nombre'], 'UTF-8');
            $normalized = mb_strtoupper(mb_substr($normalized, 0, 1), 'UTF-8')
                          . mb_substr($normalized, 1);

            $categoria->setName($normalized);
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['id' => $categoria->getId(), 'nombre' => $categoria->getName()]
        ]);
    }

    #[Route('/{id}', name: 'categoria_delete', methods: ['DELETE'])]
    public function delete(Categorias $categoria): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($categoria);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Categoría eliminada']
        ]);
    }
}