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

    #[Route('', name: 'categoria_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        // Paginación por si hace falta usarla en un futuro
        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(100, (int)$request->query->get('limit', 25)));
        $offset = ($page - 1) * $limit;

        $categorias = $this->repository->findBy([], null, $limit, $offset);
        $total      = $this->repository->count([]);

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
        if (!$this->isGranted('ROLE_ADMIN')) {
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['nombre'])) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'El campo nombre es obligatorio']
            ], 400);
        }

        $cat = new Categorias();
        $cat->setName($data['nombre']);
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['nombre'])) {
            $categoria->setName($data['nombre']);
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        $this->em->remove($categoria);
        $this->em->flush();

        // Con 204 No Content no hay body; aquí devuelvo status + message
        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Categoría eliminada']
        ]);
    }
}
