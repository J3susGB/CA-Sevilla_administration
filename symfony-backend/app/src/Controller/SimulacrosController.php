<?php

namespace App\Controller;

use App\Entity\Simulacros;
use App\Repository\SimulacrosRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/simulacros')]
class SimulacrosController extends AbstractController
{
    public function __construct(
        private readonly SimulacrosRepository $simRepo,
        private readonly ArbitrosRepository $arbRepo,
        private readonly CategoriasRepository $catRepo,
        private readonly EntityManagerInterface $em
    ) {}

    private function forbidden(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'error' => ['code' => 403, 'message' => 'No autorizado'],
        ], 403);
    }

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CLASIFICACION');
    }

    #[Route('', name: 'simulacros_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $data = [];
        foreach ($this->simRepo->findAllOrderedByFecha() as $sim) {
            $arb = $sim->getArbitro();
            $cat = $sim->getCategoria();
            $data[] = [
                'id' => $sim->getId(),
                'arbitro_id' => $arb->getId(),
                'nif' => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname' => $arb->getSecondSurname(),
                'name' => $arb->getName(),
                'categoria_id' => $cat->getId(),
                'categoria' => $cat->getName(),
                'fecha' => $sim->getFecha()->format('d-m-Y'),
                'periodo' => $sim->getPeriodo(),
            ];
        }

        return $this->json(['status' => 'success', 'data' => $data]);
    }

    #[Route('', name: 'simulacros_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nif'], $data['categoria_id'], $data['fecha'], $data['periodo'])) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Faltan campos obligatorios']], 400);
        }

        $nif = mb_strtoupper($data['nif']);
        $arbitro = $this->arbRepo->findOneBy(['nif' => $nif]);
        $categoria = $this->catRepo->find($data['categoria_id']);
        $fecha = \DateTime::createFromFormat('Y-m-d', $data['fecha']);

        if (!$arbitro || !$categoria || !$fecha) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Datos inválidos']], 400);
        }

        $sim = new Simulacros();
        $sim->setArbitro($arbitro)
            ->setCategoria($categoria)
            ->setFecha($fecha)
            ->setPeriodo((float) $data['periodo']);

        $this->em->persist($sim);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $sim->getId()]], 201);
    }

    #[Route('/{id<\d+>}', name: 'simulacros_show', methods: ['GET'])]
    public function show(Simulacros $sim): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $sim->getId(),
                'arbitro_id' => $sim->getArbitro()->getId(),
                'nif' => $sim->getArbitro()->getNif(),
                'first_surname' => $sim->getArbitro()->getFirstSurname(),
                'second_surname' => $sim->getArbitro()->getSecondSurname(),
                'name' => $sim->getArbitro()->getName(),
                'categoria_id' => $sim->getCategoria()->getId(),
                'categoria' => $sim->getCategoria()->getName(),
                'fecha' => $sim->getFecha()->format('d-m-Y'),
                'periodo' => $sim->getPeriodo(),
            ]
        ]);
    }

    #[Route('/{id<\d+>}', name: 'simulacros_update', methods: ['PUT'])]
    public function update(Simulacros $sim, Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();
        $data = json_decode($request->getContent(), true);

        if (isset($data['fecha'])) {
            $fecha = \DateTime::createFromFormat('d-m-Y', $data['fecha']);
            if ($fecha) $sim->setFecha($fecha);
        }

        if (isset($data['periodo'])) {
            $sim->setPeriodo((float) $data['periodo']);
        }

        $this->em->flush();
        return $this->json(['status' => 'success', 'data' => ['id' => $sim->getId()]]);
    }

    #[Route('/{id<\d+>}', name: 'simulacros_delete', methods: ['DELETE'])]
    public function delete(Simulacros $sim): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();

        $this->em->remove($sim);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Simulacro eliminado']]);
    }

    #[Route('/bulk-upload', name: 'simulacros_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (!$this->allowed()) return $this->forbidden();
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Archivo no encontrado']], 400);
        }

        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_map(fn($h) => strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($h))), array_shift($rows));
        $normalize = fn(string $s) => trim(preg_replace('/\s+/u', ' ', strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s))));

        $arbitros = $this->arbRepo->findAll();
        $mapArbitros = [];
        foreach ($arbitros as $arb) {
            $key = $normalize($arb->getFirstSurname().' '.$arb->getSecondSurname().' '.$arb->getName());
            $mapArbitros[$key] = $arb;
        }

        $procesados = [];
        $created = [];
        $skipped = [];

        foreach ($rows as $i => $row) {
            $data = array_combine($headers, array_map('trim', $row));
            $nombre = $data['ARBITRO'] ?? null;
            $fecha = \DateTime::createFromFormat('d/m/y', $data['FECHA'] ?? '') ?: \DateTime::createFromFormat('Y-m-d', $data['FECHA'] ?? '');
            $periodo = isset($data['PERIODO']) ? (float) $data['PERIODO'] : null;

            if (!$nombre || !$fecha || $periodo === null) {
                $skipped[] = ['row' => $i + 2, 'reason' => 'Campos incompletos'];
                continue;
            }

            $key = $normalize($nombre);
            if (!isset($mapArbitros[$key])) {
                $skipped[] = ['row' => $i + 2, 'arbitro' => $nombre, 'reason' => 'No encontrado'];
                continue;
            }

            $arb = $mapArbitros[$key];
            $cat = $arb->getCategoria();
            $procesados[] = $arb->getId();

            $sim = new Simulacros();
            $sim->setArbitro($arb)->setCategoria($cat)->setFecha($fecha)->setPeriodo($periodo);

            $this->em->persist($sim);
            $created[] = ['arbitro' => $nombre, 'fecha' => $fecha->format('Y-m-d'), 'periodo' => $periodo];
        }

        // Crear simulacros en blanco (0) para los no incluidos
        $fechaActual = new \DateTime();
        foreach ($arbitros as $arb) {
            if (in_array($arb->getId(), $procesados)) continue;
            $categoria = $arb->getCategoria()?->getName();
            if (!in_array($categoria, ['Provincial', 'Oficial'])) continue;

            $sim = new Simulacros();
            $sim->setArbitro($arb)->setCategoria($arb->getCategoria())->setFecha($fechaActual)->setPeriodo(0);
            $this->em->persist($sim);

            $created[] = [
                'arbitro' => $arb->getFirstSurname().' '.$arb->getSecondSurname().' '.$arb->getName(),
                'fecha' => $fechaActual->format('Y-m-d'),
                'periodo' => 0,
            ];
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'created' => $created,
            'skipped' => $skipped,
            'created_count' => count($created),
            'skipped_count' => count($skipped)
        ]);
    }

    #[Route('/truncate', name: 'simulacros_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) return $this->forbidden();

        $this->em->getConnection()->executeStatement(
            $this->em->getConnection()->getDatabasePlatform()->getTruncateTableSQL('simulacros', true)
        );

        return $this->json(['status' => 'success', 'data' => ['message' => 'Tabla truncada']]);
    }
}
