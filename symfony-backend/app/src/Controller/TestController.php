<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\TestSession;
use App\Repository\TestRepository;
use App\Repository\TestSessionRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tests')]
final class TestController extends AbstractController
{
    public function __construct(
        private readonly TestRepository           $testRepo,
        private readonly TestSessionRepository    $sessionRepo,
        private readonly ArbitrosRepository       $arbRepo,
        private readonly CategoriasRepository     $catRepo,
        private readonly EntityManagerInterface   $em
    ) {}

    private function forbidden(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'error'  => ['code' => 403, 'message' => 'No autorizado'],
        ], 403);
    }

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CAPACITACION');
    }

    // ─────────────────────────────────────────────
    // LISTAR TODAS LAS SESSIONS DE TEST CON NOTAS
    // ─────────────────────────────────────────────
    #[Route('', name: 'test_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        $sessions = $this->sessionRepo->findAll();

        foreach ($sessions as $s) {
            $cat      = $s->getCategoria();
            $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

            // Map de { arbitroId => Test entity }
            $mapTests = [];
            foreach ($s->getTests() as $tEntity) {
                $mapTests[$tEntity->getArbitro()->getId()] = $tEntity;
            }

            $lista = [];
            foreach ($arbitros as $arb) {
                if (isset($mapTests[$arb->getId()])) {
                    $t = $mapTests[$arb->getId()];
                    $lista[] = [
                        'id'             => $t->getId(),
                        'arbitro_id'     => $arb->getId(),
                        'nif'            => $arb->getNif(),
                        'nota'           => $t->getNota(),
                        'categoria_id'   => $cat->getId(),
                        'second_surname' => $arb->getSecondSurname(),
                        'first_surname'  => $arb->getFirstSurname(),
                        'name'           => $arb->getName(),
                    ];
                } else {
                    // nota nueva (aún no existe)
                    $lista[] = [
                        'id'             => null,
                        'arbitro_id'     => $arb->getId(),
                        'nif'            => $arb->getNif(),
                        'nota'           => null,
                        'categoria_id'   => $cat->getId(),
                        'second_surname' => $arb->getSecondSurname(),
                        'first_surname'  => $arb->getFirstSurname(),
                        'name'           => $arb->getName(),
                    ];
                }
            }

            $out[] = [
                'id'         => $s->getId(),
                'fecha'      => $s->getFecha()->format('d-m-Y'),
                'testNumber' => $s->getTestNumber(),
                'categoria'  => $cat->getName(),
                'notas'      => $lista,
            ];
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // MOSTRAR UNA SESSION DE TEST POR ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'test_show', methods: ['GET'])]
    public function show(TestSession $session): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $cat      = $session->getCategoria();
        $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

        $mapTests = [];
        foreach ($session->getTests() as $tEntity) {
            $mapTests[$tEntity->getArbitro()->getId()] = $tEntity;
        }

        $lista = [];
        foreach ($arbitros as $arb) {
            if (isset($mapTests[$arb->getId()])) {
                $t = $mapTests[$arb->getId()];
                $lista[] = [
                    'id'             => $t->getId(),
                    'arbitro_id'     => $arb->getId(),
                    'nif'            => $arb->getNif(),
                    'nota'           => $t->getNota(),
                    'categoria_id'   => $cat->getId(),
                    'second_surname' => $arb->getSecondSurname(),
                    'first_surname'  => $arb->getFirstSurname(),
                    'name'           => $arb->getName(),
                ];
            } else {
                $lista[] = [
                    'id'             => null,
                    'arbitro_id'     => $arb->getId(),
                    'nif'            => $arb->getNif(),
                    'nota'           => null,
                    'categoria_id'   => $cat->getId(),
                    'second_surname' => $arb->getSecondSurname(),
                    'first_surname'  => $arb->getFirstSurname(),
                    'name'           => $arb->getName(),
                ];
            }
        }

        $data = [
            'id'         => $session->getId(),
            'fecha'      => $session->getFecha()->format('d-m-Y'),
            'testNumber' => $session->getTestNumber(),
            'categoria'  => $cat->getName(),
            'notas'      => $lista,
        ];

        return $this->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    // ─────────────────────────────────────────────
    // RESUMEN: TOTAL DE ACIERTOS POR ÁRBITRO
    // ─────────────────────────────────────────────
    #[Route('/totals', name: 'test_totals', methods: ['GET'])]
    public function totals(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out        = [];
        $categorias = $this->catRepo->findAll();

        foreach ($categorias as $cat) {
            $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);
            $lista    = [];

            foreach ($arbitros as $arb) {
                // Suma todos los aciertos (nota) de este árbitro en esta categoría
                $sum = $this->testRepo->createQueryBuilder('t')
                    ->select('SUM(t.nota)')
                    ->where('t.arbitro = :arb')
                    ->andWhere('t.categoria = :cat')
                    ->setParameters(['arb' => $arb, 'cat' => $cat])
                    ->getQuery()
                    ->getSingleScalarResult();

                $totalAciertos = (int)$sum;

                $lista[] = [
                    'arbitro_id'     => $arb->getId(),
                    'second_surname' => $arb->getSecondSurname(),
                    'first_surname'  => $arb->getFirstSurname(),
                    'name'           => $arb->getName(),
                    'nif'            => $arb->getNif(),
                    'total_aciertos' => $totalAciertos,
                ];
            }

            if (count($lista) > 0) {
                $out[] = [
                    'categoria_id' => $cat->getId(),
                    'categoria'    => $cat->getName(),
                    'arbitros'     => $lista,
                ];
            }
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // CREAR NOTA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('', name: 'test_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        if (
            empty($data['fecha']) ||
            ! isset($data['testNumber']) ||
            empty($data['categoria_id']) ||
            empty($data['nif']) ||
            ! isset($data['nota'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Campos obligatorios: fecha, testNumber, categoria_id, nif, nota']
            ], 400);
        }

        $f = \DateTimeImmutable::createFromFormat('d/m/Y', $data['fecha'])
           ?: \DateTimeImmutable::createFromFormat('d/m/y', $data['fecha']);
        if (! $f) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Fecha inválida']
            ], 400);
        }

        $testNumber = (int)$data['testNumber'];
        $categoria  = $this->catRepo->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        $session = $this->sessionRepo->findOneBy([
            'fecha'      => $f,
            'testNumber' => $testNumber,
            'categoria'  => $categoria
        ]) ?? (new TestSession())
            ->setFecha($f)
            ->setTestNumber($testNumber)
            ->setCategoria($categoria);
        $this->em->persist($session);

        $nif = mb_strtoupper($data['nif']);
        $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
        if (! $arb) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Árbitro no encontrado']
            ], 404);
        }

        $nota = (float)$data['nota'];
        $test = (new Test())
            ->setSession($session)
            ->setArbitro($arb)
            ->setNota($nota)
            ->setCategoria($categoria);
        $this->em->persist($test);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'         => $test->getId(),
                'fecha'      => $f->format('d-m-Y'),
                'testNumber' => $testNumber,
                'categoria'  => $categoria->getName(),
                'arbitro_id' => $arb->getId(),
                'nif'        => $arb->getNif(),
                'nota'       => $nota,
            ]
        ], 201);
    }

    // ─────────────────────────────────────────────
    // ACTUALIZAR NOTA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'test_update', methods: ['PUT'])]
    public function update(Test $test, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (! isset($data['nota'])) {
            return $this->json([
                'status'=>'error',
                'error'=>['code'=>400,'message'=>'Campo obligatorio: nota']
            ], 400);
        }

        $test->setNota((float)$data['nota']);
        $this->em->flush();

        return $this->json([
            'status'=>'success',
            'data'=>[
                'id'   => $test->getId(),
                'nota' => $test->getNota()
            ]
        ]);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR NOTA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'test_delete', methods: ['DELETE'])]
    public function delete(Test $test): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($test);
        $this->em->flush();

        return $this->json([
            'status'=>'success',
            'data'=>['message'=>'Nota eliminada']
        ]);
    }

    // ─────────────────────────────────────────────
    // CARGA MASIVA DE NOTAS
    // ─────────────────────────────────────────────
    #[Route('/bulk-upload', name: 'test_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $file = $request->files->get('file');
        if (! $file) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'No se ha subido ningún archivo'],
            ], 400);
        }

        $tmpPath = sys_get_temp_dir().'/'.uniqid('test_bulk_').'.'.$file->getClientOriginalExtension();
        $file->move(\dirname($tmpPath), \basename($tmpPath));

        $sheet = IOFactory::load($tmpPath)->getActiveSheet();
        $rows  = $sheet->toArray();
        array_shift($rows);

        $created    = [];
        $updated    = [];
        $ignored    = [];
        $currentKey = null;
        $session    = null;

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            if (count($row) < 5) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Columnas insuficientes'];
                continue;
            }

            $fechaRaw    = trim((string)$row[0]);
            $testNumRaw  = trim((string)$row[1]);
            $fecha       = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
                        ?: \DateTimeImmutable::createFromFormat('d/m/y', $fechaRaw);
            if (! $fecha) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Fecha inválida: {$fechaRaw}"];
                continue;
            }

            $testNumber = filter_var($testNumRaw, FILTER_VALIDATE_INT);
            if ($testNumber === false) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Número de test inválido: {$testNumRaw}"];
                continue;
            }

            $catRaw    = ucfirst(mb_strtolower(trim((string)$row[4])));
            $categoria = $this->catRepo->findOneBy(['name' => $catRaw]);
            if (! $categoria) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Categoría no encontrada: {$catRaw}"];
                continue;
            }

            $key = $fecha->format('Y-m-d').'|'.$testNumber.'|'.$categoria->getId();
            if ($key !== $currentKey) {
                $currentKey = $key;
                $session = $this->sessionRepo->findOneBy([
                    'fecha'      => $fecha,
                    'testNumber' => $testNumber,
                    'categoria'  => $categoria
                ]) ?? (new TestSession())
                        ->setFecha($fecha)
                        ->setTestNumber($testNumber)
                        ->setCategoria($categoria);
                $this->em->persist($session);
            }

            $nif = mb_strtoupper(trim((string)$row[2]));
            $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
            if (! $arb) {
                $ignored[] = ['row' => $rowNum, 'nif' => $nif, 'reason' => 'Árbitro no encontrado'];
                continue;
            }

            $notaRaw = trim((string)$row[3]);
            if (! is_numeric($notaRaw)) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Nota inválida: {$notaRaw}"];
                continue;
            }
            $nota = (float)$notaRaw;

            $t = $this->testRepo->findOneBy([
                'session'  => $session,
                'arbitro'  => $arb,
            ]) ?? (new Test())
                    ->setSession($session)
                    ->setArbitro($arb);

            $t->setNota($nota)
              ->setCategoria($categoria);

            $this->em->persist($t);

            if ($t->getId() === null) {
                $created[] = ['row' => $rowNum, 'nif' => $nif];
            } else {
                $updated[] = ['row' => $rowNum, 'nif' => $nif];
            }
        }

        $this->em->flush();
        @unlink($tmpPath);

        return $this->json([
            'status'         => 'success',
            'created_count'  => count($created),
            'updated_count'  => count($updated),
            'ignored_count'  => count($ignored),
            'created'        => $created,
            'updated'        => $updated,
            'ignored'        => $ignored,
        ], 201);
    }

    // ─────────────────────────────────────────────
    // TRUNCATE TABLA TEST Y TEST_SESSION
    // ─────────────────────────────────────────────
    #[Route('/truncate', name: 'test_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (! $this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        $conn     = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        // Truncate test primero
        $conn->executeStatement($platform->getTruncateTableSQL('test', true));
        // Luego truncate test_session
        $conn->executeStatement($platform->getTruncateTableSQL('test_session', true));

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Tablas test y test_session reseteadas']
        ]);
    }
}
