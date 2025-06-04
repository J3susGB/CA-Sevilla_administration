<?php

namespace App\Controller;

use App\Entity\Fisica;
use App\Entity\Categorias;
use App\Entity\Arbitros;
use App\Repository\FisicaRepository;
use App\Repository\CategoriasRepository;
use App\Repository\ArbitrosRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/fisica')]
class FisicaController extends AbstractController
{
    public function __construct(
        private readonly FisicaRepository $fisicaRepo,
        private readonly ArbitrosRepository $arbRepo,
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

    // ─────────────────────────────────────────────
    // LISTAR TODAS LAS NOTAS FÍSICAS
    // ─────────────────────────────────────────────
    #[Route('', name: 'fisica_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $registros = $this->fisicaRepo->findBy([], ['categoria' => 'ASC', 'convocatoria' => 'ASC']);
        $out = [];

        foreach ($registros as $f) {
            $arb = $f->getArbitro();
            $cat = $f->getCategoria();

            $out[] = [
                'id'              => $f->getId(),
                'nif'             => $arb->getNif(),
                'first_surname'   => $arb->getFirstSurname(),
                'second_surname'  => $arb->getSecondSurname(),
                'name'            => $arb->getName(),
                'categoria'       => $cat->getName(),
                'categoria_id'    => $cat->getId(),
                'convocatoria'    => $f->getConvocatoria(),
                'repesca'         => $f->getRepesca(),
                'yoyo'            => $f->getYoyo(),
                'velocidad'       => $f->getVelocidad(),
            ];
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out
        ]);
    }

    // ─────────────────────────────────────────────
    // MOSTRAR UNA NOTA FÍSICA POR SU ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'fisica_show', methods: ['GET'])]
    public function show(Fisica $fisica): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $arb = $fisica->getArbitro();
        $cat = $fisica->getCategoria();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'              => $fisica->getId(),
                'nif'             => $arb->getNif(),
                'first_surname'   => $arb->getFirstSurname(),
                'second_surname'  => $arb->getSecondSurname(),
                'name'            => $arb->getName(),
                'categoria'       => $cat->getName(),
                'categoria_id'    => $cat->getId(),
                'convocatoria'    => $fisica->getConvocatoria(),
                'repesca'         => $fisica->getRepesca(),
                'yoyo'            => $fisica->getYoyo(),
                'velocidad'       => $fisica->getVelocidad(),
            ]
        ]);
    }

    #[Route('/bulk-upload', name: 'fisica_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['status' => 'error', 'error' => ['message' => 'Archivo no encontrado']], 400);
        }

        $tmpPath = sys_get_temp_dir() . '/' . uniqid('fisica_', true) . '.' . $file->getClientOriginalExtension();
        $file->move(\dirname($tmpPath), \basename($tmpPath));

        $sheet = IOFactory::load($tmpPath)->getActiveSheet();
        $rows  = $sheet->toArray();
        $headers = array_map('strtoupper', array_map('trim', array_shift($rows)));

        // Detectamos si la columna VELOCIDAD está presente
        $hasVelocidad = in_array('VELOCIDAD', $headers);

        // Mapa de índice para columnas (por si el orden cambia)
        $map = array_flip($headers);

        $created = [];
        $updated = [];
        $ignored = [];

        // Agrupar filas por clave: categoria_id + convocatoria
        $agrupadas = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            try {
                $nif        = mb_strtoupper(trim((string) $row[$map['NIF']] ?? ''));
                $catRaw     = ucfirst(mb_strtolower(trim((string) $row[$map['CATEGORIA']] ?? '')));
                $convRaw    = (string) $row[$map['CONVOCATORIA']] ?? '';
                $repeRaw    = (string) $row[$map['REPESCA']] ?? '';
                $yoyoRaw    = (string) $row[$map['YOYO']] ?? '';
                $velRaw     = $hasVelocidad ? (string) $row[$map['VELOCIDAD']] ?? '' : null;

                if (!$nif || !$catRaw || !$convRaw || $yoyoRaw === '') {
                    $ignored[] = ['row' => $rowNum, 'reason' => 'Datos incompletos'];
                    continue;
                }

                $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
                if (!$arb) {
                    $ignored[] = ['row' => $rowNum, 'nif' => $nif, 'reason' => 'Árbitro no encontrado'];
                    continue;
                }

                $cat = $this->catRepo->findOneBy(['name' => $catRaw]);
                if (!$cat) {
                    $ignored[] = ['row' => $rowNum, 'reason' => "Categoría inválida: $catRaw"];
                    continue;
                }

                $conv = (int) $convRaw;
                if ($cat->getName() === 'Auxiliar' && $conv !== 1) {
                    $ignored[] = ['row' => $rowNum, 'reason' => 'Auxiliares solo pueden tener convocatoria 1'];
                    continue;
                }

                $repesca = filter_var($repeRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                $yoyo = (float) str_replace(',', '.', $yoyoRaw);
                $velocidad = $hasVelocidad ? (float) str_replace(',', '.', $velRaw) : 0.00;

                $clave = $cat->getId() . '|' . $conv;
                $agrupadas[$clave]['categoria'] = $cat;
                $agrupadas[$clave]['convocatoria'] = $conv;
                $agrupadas[$clave]['rows'][] = [
                    'row'       => $rowNum,
                    'nif'       => $nif,
                    'arbitro'   => $arb,
                    'repesca'   => $repesca,
                    'yoyo'      => $yoyo,
                    'velocidad' => $velocidad
                ];
            } catch (\Throwable $e) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Error interno'];
            }
        }

        foreach ($agrupadas as $clave => $grupo) {
            $cat = $grupo['categoria'];
            $conv = $grupo['convocatoria'];
            $procesados = [];

            foreach ($grupo['rows'] as $fila) {
                $arb = $fila['arbitro'];
                $fisica = $this->fisicaRepo->findOneBy([
                    'arbitro' => $arb,
                    'categoria' => $cat,
                    'convocatoria' => $conv
                ]);

                if ($fisica) {
                    $fisica->setYoyo($fila['yoyo'])
                        ->setVelocidad($fila['velocidad'])
                        ->setRepesca($fila['repesca']);
                    $updated[] = ['row' => $fila['row'], 'nif' => $fila['nif']];
                } else {
                    $fisica = (new Fisica())
                        ->setArbitro($arb)
                        ->setCategoria($cat)
                        ->setConvocatoria($conv)
                        ->setYoyo($fila['yoyo'])
                        ->setVelocidad($fila['velocidad'])
                        ->setRepesca($fila['repesca']);

                    $this->em->persist($fisica);
                    $created[] = ['row' => $fila['row'], 'nif' => $fila['nif']];
                }

                $procesados[] = $arb->getId();
            }

            // Insertar faltantes para esta categoría + convocatoria
            $faltantes = $this->arbRepo->findBy(['categoria' => $cat]);
            foreach ($faltantes as $arb) {
                if (in_array($arb->getId(), $procesados, true)) continue;

                $yaExiste = $this->fisicaRepo->findOneBy([
                    'arbitro' => $arb,
                    'categoria' => $cat,
                    'convocatoria' => $conv
                ]);
                if ($yaExiste) continue;

                $nuevo = (new Fisica())
                    ->setArbitro($arb)
                    ->setCategoria($cat)
                    ->setConvocatoria($conv)
                    ->setRepesca(false)
                    ->setYoyo(0.0)
                    ->setVelocidad(0.0);

                $this->em->persist($nuevo);
                $created[] = [
                    'row' => 'auto',
                    'nif' => $arb->getNif(),
                    'nota' => 0.0
                ];
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
    // TRUNCAR TABLA FISICA – SOLO ADMIN
    // ─────────────────────────────────────────────
    #[Route('/truncate', name: 'fisica_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (! $this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 403,
                    'message' => 'Solo ROLE_ADMIN puede truncar esta tabla'
                ]
            ], 403);
        }

        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        $conn->executeStatement($platform->getTruncateTableSQL('fisica', true));

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Tabla fisica vaciada correctamente']
        ]);
    }

    // ─────────────────────────────────────────────
    // CREAR UNA NOTA FÍSICA
    // ─────────────────────────────────────────────
    #[Route('', name: 'fisica_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true); // ✅ PRIMERO decodificamos

        if (
            !isset($data['nif'], $data['categoria_id'], $data['convocatoria'], $data['yoyo'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => 'Faltan datos obligatorios']
            ], 400);
        }

        $velocidad = isset($data['velocidad']) ? (float) $data['velocidad'] : 0.00;
        $repesca   = isset($data['repesca']) ? filter_var($data['repesca'], FILTER_VALIDATE_BOOLEAN) : false;

        $arb = $this->arbRepo->findOneBy(['nif' => mb_strtoupper($data['nif'])]);
        if (!$arb) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => 'Árbitro no encontrado']
            ], 404);
        }

        $cat = $this->catRepo->find($data['categoria_id']);
        if (!$cat) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => 'Categoría no válida']
            ], 404);
        }

        $conv = (int) $data['convocatoria'];
        if ($cat->getName() === 'Auxiliar' && $conv !== 1) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => 'Auxiliares solo pueden tener convocatoria 1']
            ], 400);
        }

        // Verificar si ya existe
        $exists = $this->fisicaRepo->findOneBy([
            'arbitro' => $arb,
            'categoria' => $cat,
            'convocatoria' => $conv
        ]);
        if ($exists) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => 'Ya existe un registro para este árbitro en esta convocatoria']
            ], 409);
        }

        $fisica = (new Fisica())
            ->setArbitro($arb)
            ->setCategoria($cat)
            ->setConvocatoria($conv)
            ->setYoyo((float) $data['yoyo'])
            ->setVelocidad($velocidad)
            ->setRepesca($repesca);

        $this->em->persist($fisica);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $fisica->getId()]], 201);
    }

    // ─────────────────────────────────────────────
    // ACTUALIZAR NOTA FÍSICA EXISTENTE
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'fisica_update', methods: ['PUT'])]
    public function update(Fisica $fisica, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['yoyo'])) {
            $fisica->setYoyo((float)$data['yoyo']);
        }

        if (isset($data['velocidad'])) {
            $fisica->setVelocidad((float)$data['velocidad']);
        }

        if (isset($data['repesca'])) {
            $fisica->setRepesca(filter_var($data['repesca'], FILTER_VALIDATE_BOOLEAN));
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Registro actualizado']
        ]);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR NOTA FÍSICA POR ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'fisica_delete', methods: ['DELETE'])]
    public function delete(Fisica $fisica): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($fisica);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Registro eliminado']]);
    }

}