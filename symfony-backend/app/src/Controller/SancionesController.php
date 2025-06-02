<?php

namespace App\Controller;

use App\Entity\Sanciones;
use App\Entity\Arbitros;
use App\Entity\Categorias;
use App\Repository\SancionesRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sanciones')]
class SancionesController extends AbstractController
{
    public function __construct(
        private readonly SancionesRepository $sancionesRepo,
        private readonly ArbitrosRepository $arbRepo,
        private readonly CategoriasRepository $catRepo,
        private readonly EntityManagerInterface $em
    ) {}

    private function forbidden(): JsonResponse
    {
        return $this->json(['status' => 'error', 'error' => ['code' => 403, 'message' => 'No autorizado']], 403);
    }

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') 
            || $this->isGranted('ROLE_INFORMACION') 
            || $this->isGranted('ROLE_CLASIFICACION');
    }

    #[Route('', name: 'sanciones_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        $sanciones = $this->sancionesRepo->findAll();

        foreach ($sanciones as $san) {
            $arb = $san->getArbitro();
            $cat = $san->getCategoria();
            $out[] = [
                'id'            => $san->getId(),
                'nif'           => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname'=> $arb->getSecondSurname(),
                'name'          => $arb->getName(),
                'categoria'     => $cat->getName(),
                'fecha'         => $san->getFecha()->format('d-m-Y'),
                'tipo'          => $san->getTipo(),
                'nota'          => $san->getNota(),
            ];
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'sanciones_show', methods: ['GET'])]
    public function show(Sanciones $sancion): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $arb = $sancion->getArbitro();
        $cat = $sancion->getCategoria();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'            => $sancion->getId(),
                'nif'           => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname'=> $arb->getSecondSurname(),
                'name'          => $arb->getName(),
                'categoria'     => $cat->getName(),
                'fecha'         => $sancion->getFecha()->format('d-m-Y'),
                'tipo'          => $sancion->getTipo(),
                'nota'          => $sancion->getNota(),
            ]
        ]);
    }

    #[Route('', name: 'sanciones_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $data = json_decode($request->getContent(), true);
        if (
            !isset($data['nif']) ||
            !isset($data['categoria_id']) ||
            !isset($data['fecha']) ||
            !isset($data['tipo']) ||
            !isset($data['nota'])
        ) {
            return $this->json(['status' => 'error', 'error' => ['code' => 400, 'message' => 'Faltan campos obligatorios']], 400);
        }

        $arbitro = $this->arbRepo->findOneBy(['nif' => strtoupper($data['nif'])]);
        if (!$arbitro) return $this->json(['status' => 'error', 'error' => ['code' => 404, 'message' => 'Árbitro no encontrado']], 404);

        $categoria = $this->catRepo->find($data['categoria_id']);
        if (!$categoria) return $this->json(['status' => 'error', 'error' => ['code' => 404, 'message' => 'Categoría no encontrada']], 404);

        $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $data['fecha'])
              ?: \DateTimeImmutable::createFromFormat('d-m-Y', $data['fecha']);
        if (!$fecha) return $this->json(['status' => 'error', 'error' => ['code' => 400, 'message' => 'Fecha inválida']], 400);

        $sancion = (new Sanciones())
            ->setArbitro($arbitro)
            ->setCategoria($categoria)
            ->setFecha($fecha)
            ->setTipo($data['tipo'])
            ->setNota((float)$data['nota']);

        $this->em->persist($sancion);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $sancion->getId()]], 201);
    }

    #[Route('/{id}', name: 'sanciones_update', methods: ['PUT'])]
    public function update(Sanciones $sancion, Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $data = json_decode($request->getContent(), true);

        if (isset($data['fecha'])) {
            $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $data['fecha'])
                  ?: \DateTimeImmutable::createFromFormat('d-m-Y', $data['fecha']);
            if ($fecha) $sancion->setFecha($fecha);
        }

        if (isset($data['tipo'])) $sancion->setTipo($data['tipo']);
        if (isset($data['nota'])) $sancion->setNota((float)$data['nota']);

        if (isset($data['categoria_id'])) {
            $cat = $this->catRepo->find($data['categoria_id']);
            if ($cat) $sancion->setCategoria($cat);
        }

        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $sancion->getId()]]);
    }

    #[Route('/{id}', name: 'sanciones_delete', methods: ['DELETE'])]
    public function delete(Sanciones $sancion): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $this->em->remove($sancion);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Sanción eliminada']]);
    }

    #[Route('/bulk-upload', name: 'sanciones_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $file = $request->files->get('file');
        if (!$file) return $this->json(['status' => 'error', 'error' => ['code' => 400, 'message' => 'Archivo no recibido']], 400);

        $path = sys_get_temp_dir().'/'.uniqid('sanciones_').'.'.$file->getClientOriginalExtension();
        $file->move(dirname($path), basename($path));

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Throwable $e) {
            @unlink($path);
            return $this->json(['status' => 'error', 'error' => ['code' => 400, 'message' => 'Error al leer Excel']], 400);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray();
        array_shift($rows); // encabezado

        $created = []; $ignored = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            [$nif, $categoriaRaw, $fechaRaw, $tipo, $notaRaw] = array_map('trim', $row);

            $arbitro = $this->arbRepo->findOneBy(['nif' => strtoupper($nif)]);
            $categoria = $this->catRepo->findOneBy(['name' => ucfirst(strtolower($categoriaRaw))]);

            if (!$arbitro || !$categoria || !is_numeric($notaRaw)) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Datos inválidos'];
                continue;
            }

            $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
                  ?: \DateTimeImmutable::createFromFormat('d-m-Y', $fechaRaw);
            if (!$fecha) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Fecha inválida'];
                continue;
            }

            $sancion = (new Sanciones())
                ->setArbitro($arbitro)
                ->setCategoria($categoria)
                ->setFecha($fecha)
                ->setTipo($tipo)
                ->setNota((float)$notaRaw);

            $this->em->persist($sancion);
            $created[] = ['row' => $rowNum, 'nif' => $nif];
        }

        $this->em->flush();
        @unlink($path);

        return $this->json([
            'status'        => 'success',
            'created_count' => count($created),
            'ignored_count' => count($ignored),
            'created'       => $created,
            'ignored'       => $ignored,
        ], 201);
    }

    #[Route('/truncate', name: 'sanciones_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) return $this->forbidden();

        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeStatement($platform->getTruncateTableSQL('sanciones', true));

        return $this->json(['status' => 'success', 'data' => ['message' => 'Tabla sanciones truncada']]);
    }
}
