<?php

namespace App\Controller;

use App\Entity\Observaciones;
use App\Repository\CategoriasRepository;
use App\Repository\ObservacionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/api/observaciones')]
class ObservacionesController extends AbstractController
{
    public function __construct(
        private readonly ObservacionesRepository $obsRepo,
        private readonly CategoriasRepository $catRepo,
        private readonly EntityManagerInterface $em
    ) {}

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CLASIFICACION');
    }

    private function forbidden(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'error'  => ['code' => 403, 'message' => 'No autorizado'],
        ], 403);
    }

    #[Route('', name: 'observaciones_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        $data = array_map(function (Observaciones $obs) {
            return [
                'id'          => $obs->getId(),
                'codigo'      => $obs->getCodigo(),
                'descripcion' => $obs->getDescripcion(),
                'categoria_id' => $obs->getCategoria()->getId(),
                'categoria'    => $obs->getCategoria()->getName(),
            ];
        }, $this->obsRepo->findAll());

        return $this->json(['status' => 'success', 'data' => $data]);
    }

    #[Route('/{id<\d+>}', name: 'observaciones_show', methods: ['GET'])]
    public function show(Observaciones $obs): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'id'          => $obs->getId(),
                'codigo'      => $obs->getCodigo(),
                'descripcion' => $obs->getDescripcion(),
                'categoria_id' => $obs->getCategoria()->getId(),
                'categoria'    => $obs->getCategoria()->getName(),
            ]
        ]);
    }

    #[Route('', name: 'observaciones_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['categoria_id'], $data['codigo'], $data['descripcion'])) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Faltan datos obligatorios']], 400);
        }

        $cat = $this->catRepo->find($data['categoria_id']);
        if (!$cat) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Categoría no encontrada']], 404);
        }

        $obs = (new Observaciones())
            ->setCategoria($cat)
            ->setCodigo((string) $data['codigo'])
            ->setDescripcion((string) $data['descripcion']);

        $this->em->persist($obs);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $obs->getId()]], 201);
    }

    #[Route('/{id<\d+>}', name: 'observaciones_update', methods: ['PUT'])]
    public function update(Observaciones $obs, Request $request): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['codigo'])) {
            $obs->setCodigo((string) $data['codigo']);
        }

        if (isset($data['descripcion'])) {
            $obs->setDescripcion((string) $data['descripcion']);
        }

        if (isset($data['categoria_id'])) {
            $cat = $this->catRepo->find($data['categoria_id']);
            if ($cat) {
                $obs->setCategoria($cat);
            }
        }

        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Actualizado correctamente']]);
    }

    #[Route('/{id<\d+>}', name: 'observaciones_delete', methods: ['DELETE'])]
    public function delete(Observaciones $obs): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($obs);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Eliminado correctamente']]);
    }

    #[Route('/truncate', name: 'observaciones_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'status' => 'error',
                'error' => ['code' => 403, 'message' => 'Solo ROLE_ADMIN puede truncar esta tabla']
            ], 403);
        }

        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('observaciones', true));

        return $this->json(['status' => 'success', 'data' => ['message' => 'Tabla vaciada correctamente']]);
    }

    #[Route('/bulk-upload', name: 'observaciones_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (!$this->allowed()) {
            return $this->forbidden();
        }

        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Archivo no encontrado']], 400);
        }

        $tmpPath = sys_get_temp_dir() . '/' . uniqid('obs_', true) . '.' . $file->getClientOriginalExtension();
        $file->move(\dirname($tmpPath), \basename($tmpPath));

        $sheet = IOFactory::load($tmpPath)->getActiveSheet();
        $rows  = $sheet->toArray();
        $headers = array_map('strtoupper', array_map('trim', array_shift($rows)));

        $required = ['CATEGORIA', 'CODIGO', 'DESCRIPCION'];
        foreach ($required as $col) {
            if (!in_array($col, $headers)) {
                return $this->json([
                    'status' => 'error',
                    'error'  => "Columna '$col' no encontrada en el Excel"
                ], 400);
            }
        }

        $map = array_flip($headers);
        $created = [];
        $ignored = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            try {
                $catName     = ucfirst(mb_strtolower(trim((string) ($row[$map['CATEGORIA']] ?? ''))));
                $codigo      = trim((string) ($row[$map['CODIGO']] ?? ''));
                $descripcion = trim((string) ($row[$map['DESCRIPCION']] ?? ''));

                if (!$catName || !$codigo || !$descripcion) {
                    $ignored[] = ['row' => $rowNum, 'reason' => 'Datos incompletos'];
                    continue;
                }

                $cat = $this->catRepo->findOneBy(['name' => $catName]);
                if (!$cat) {
                    $ignored[] = ['row' => $rowNum, 'reason' => "Categoría no válida: $catName"];
                    continue;
                }

                $obs = (new Observaciones())
                    ->setCategoria($cat)
                    ->setCodigo($codigo)
                    ->setDescripcion($descripcion);

                $this->em->persist($obs);
                $created[] = ['row' => $rowNum, 'codigo' => $codigo];

            } catch (\Throwable $e) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Excepción: ' . $e->getMessage()];
            }
        }

        $this->em->flush();
        @unlink($tmpPath);

        return $this->json([
            'status'        => 'success',
            'created_count' => count($created),
            'ignored_count' => count($ignored),
            'created'       => $created,
            'ignored'       => $ignored,
        ], 201);
    }

}
