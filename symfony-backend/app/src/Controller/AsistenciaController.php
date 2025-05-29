<?php

namespace App\Controller;

use App\Entity\ClaseSesion;
use App\Entity\Asistencia;
use App\Repository\ClaseSesionRepository;
use App\Repository\AsistenciaRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/asistencias')]
final class AsistenciaController extends AbstractController
{
    public function __construct(
        private readonly AsistenciaRepository   $asistRepo,
        private readonly ClaseSesionRepository  $sesionRepo,
        private readonly ArbitrosRepository     $arbRepo,
        private readonly CategoriasRepository   $catRepo,
        private readonly EntityManagerInterface $em
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
    // LISTAR TODAS LAS SESIONES CON ASISTENCIAS
    // ─────────────────────────────────────────────
    #[Route('', name: 'asistencia_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        $sesiones = $this->sesionRepo->findAll();

        foreach ($sesiones as $s) {
            $cat      = $s->getCategoria();
            $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

            // Mapa de asistencias existentes
            $mapAsist = [];
            foreach ($s->getAsistencias() as $asi) {
                $mapAsist[$asi->getArbitro()->getId()] = $asi->isAsiste();
            }

            // Construir lista completa de asistencias
            $lista = [];
            foreach ($arbitros as $arb) {
                $lista[] = [
                    'arbitro_id'   => $arb->getId(),
                    'nif'          => $arb->getNif(),
                    'asiste'       => $mapAsist[$arb->getId()] ?? false,
                    'categoria_id' => $cat->getId(),
                ];
            }

            $out[] = [
                'id'          => $s->getId(),
                'fecha'       => $s->getFecha()->format('d-m-Y'),
                'tipo'        => $s->getTipo(),
                'categoria'   => $cat->getName(),
                'asistencias' => $lista,
            ];
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // MOSTRAR UNA SESIÓN POR ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'asistencia_show', methods: ['GET'])]
    public function show(ClaseSesion $sesion): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $cat      = $sesion->getCategoria();
        $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

        $mapAsist = [];
        foreach ($sesion->getAsistencias() as $asi) {
            $mapAsist[$asi->getArbitro()->getId()] = $asi->isAsiste();
        }

        $lista = [];
        foreach ($arbitros as $arb) {
            $lista[] = [
                'arbitro_id'   => $arb->getId(),
                'nif'          => $arb->getNif(),
                'asiste'       => $mapAsist[$arb->getId()] ?? false,
                'categoria_id' => $cat->getId(),
            ];
        }

        $data = [
            'id'          => $sesion->getId(),
            'fecha'       => $sesion->getFecha()->format('d-m-Y'),
            'tipo'        => $sesion->getTipo(),
            'categoria'   => $cat->getName(),
            'asistencias' => $lista,
        ];

        return $this->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    // ─────────────────────────────────────────────
    // RESUMEN: TOTAL DE ASISTENCIAS POR ÁRBITRO
    // ─────────────────────────────────────────────
    #[Route('/totals', name: 'asistencia_totals', methods: ['GET'])]
    public function totals(): JsonResponse
    {
        if (! $this->allowed()) {
        return $this->forbidden();
        }

        $out = [];
        // 1) Carga todas las categorías
        $categorias = $this->catRepo->findAll();

        foreach ($categorias as $cat) {
            // 2) Arroba todos los árbitros de esta categoría
            $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

            // 3) Para cada árbitro, cuenta sus asistencias = true
            $lista = [];
            foreach ($arbitros as $arb) {
                $count = $this->asistRepo->count([
                    'arbitro' => $arb,
                    'asiste'  => true,
                ]);

                $lista[] = [
                    'arbitro_id'       => $arb->getId(),
                    'second_surname'   => $arb->getSecondSurname(),
                    'first_surname'    => $arb->getFirstSurname(),
                    'name'             => $arb->getName(),
                    'nif'              => $arb->getNif(),
                    'total_asistencias'=> $count,
                ];
            }

            // 4) Solo incluimos categoría si tiene árbitros (o quítalo si quieres siempre mostrarla)
            if (count($lista) > 0) {
                $out[] = [
                    'categoria_id'   => $cat->getId(),
                    'categoria'      => $cat->getName(),
                    'arbitros'       => $lista,
                ];
            }
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // CREAR ASISTENCIA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('', name: 'asistencia_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        // Validar
        if (
            empty($data['fecha']) ||
            empty($data['tipo']) ||
            empty($data['categoria_id']) ||
            empty($data['nif']) ||
            ! isset($data['asiste'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Campos obligatorios: fecha, tipo, categoria_id, nif, asiste']
            ], 400);
        }

        // Parsear fecha
        $f = \DateTimeImmutable::createFromFormat('d/m/Y', $data['fecha'])
           ?: \DateTimeImmutable::createFromFormat('d/m/y', $data['fecha']);
        if (! $f) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Fecha inválida']
            ], 400);
        }

        $tipo = mb_strtolower($data['tipo']);
        $categoria = $this->catRepo->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        // Obtener o crear sesión
        $sesion = $this->sesionRepo->findOneBy([
            'fecha'     => $f,
            'tipo'      => $tipo,
            'categoria' => $categoria
        ]) ?? (new ClaseSesion())
            ->setFecha($f)
            ->setTipo($tipo)
            ->setCategoria($categoria);
        $this->em->persist($sesion);

        // Buscar árbitro
        $nif = mb_strtoupper($data['nif']);
        $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
        if (! $arb) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Árbitro no encontrado']
            ], 404);
        }

        // Crear asistencia
        $asiste = (bool)$data['asiste'];
        $asi = (new Asistencia())
            ->setSesion($sesion)
            ->setArbitro($arb)
            ->setAsiste($asiste)
            ->setCategoria($categoria);
        $this->em->persist($asi);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'       => $asi->getId(),
                'fecha'    => $f->format('d-m-Y'),
                'tipo'     => $tipo,
                'categoria'=> $categoria->getName(),
                'arbitro_id'=> $arb->getId(),
                'nif'      => $arb->getNif(),
                'asiste'   => $asi->isAsiste(),
            ]
        ], 201);
    }

    // ─────────────────────────────────────────────
    // ACTUALIZAR ASISTENCIA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'asistencia_update', methods: ['PUT'])]
    public function update(Asistencia $asi, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (! isset($data['asiste'])) {
            return $this->json([
                'status'=>'error',
                'error'=>['code'=>400,'message'=>'Campo obligatorio: asiste']
            ], 400);
        }

        $asi->setAsiste((bool)$data['asiste']);
        $this->em->flush();

        return $this->json([
            'status'=>'success',
            'data'=>[
                'id'     => $asi->getId(),
                'asiste' => $asi->isAsiste()
            ]
        ]);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR ASISTENCIA INDIVIDUAL
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'asistencia_delete', methods: ['DELETE'])]
    public function delete(Asistencia $asi): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($asi);
        $this->em->flush();

        return $this->json([
            'status'=>'success',
            'data'=>['message'=>'Asistencia eliminada']
        ]);
    }

    // ─────────────────────────────────────────────
    // CARGA MASIVA DE ASISTENCIAS
    // ─────────────────────────────────────────────
    #[Route('/bulk-upload', name: 'asistencia_bulk_upload', methods: ['POST'])]
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

        $tmpPath = sys_get_temp_dir().'/'.uniqid('asi_bulk_').'.'.$file->getClientOriginalExtension();
        $file->move(\dirname($tmpPath), \basename($tmpPath));

        $sheet = IOFactory::load($tmpPath)->getActiveSheet();
        $rows  = $sheet->toArray();
        array_shift($rows); // eliminar cabecera

        $created    = [];
        $updated    = [];
        $ignored    = [];
        $currentKey = null;
        $sesion     = null;

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            if (count($row) < 5) {
                $ignored[] = ['row' => $rowNum, 'reason' => 'Columnas insuficientes'];
                continue;
            }

            // 1) Fecha + Tipo
            $fechaRaw = trim((string)$row[0]);
            $tipoRaw  = mb_strtolower(trim((string)$row[1]));

            $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
                  ?: \DateTimeImmutable::createFromFormat('d/m/y', $fechaRaw);
            if (! $fecha) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Fecha inválida: {$fechaRaw}"];
                continue;
            }

            // 2) Categoría de la sesión
            $catRaw    = ucfirst(mb_strtolower(trim((string)$row[4])));
            $categoria = $this->catRepo->findOneBy(['name' => $catRaw]);
            if (! $categoria) {
                $ignored[] = ['row' => $rowNum, 'reason' => "Categoría no encontrada: {$catRaw}"];
                continue;
            }

            // 3) Reusar o crear sesión
            $key = $fecha->format('Y-m-d').'|'.$tipoRaw.'|'.$categoria->getId();
            if ($key !== $currentKey) {
                $currentKey = $key;
                $sesion = $this->sesionRepo->findOneBy([
                    'fecha'     => $fecha,
                    'tipo'      => $tipoRaw,
                    'categoria' => $categoria
                ]) ?? (new ClaseSesion())
                        ->setFecha($fecha)
                        ->setTipo($tipoRaw)
                        ->setCategoria($categoria);
                $this->em->persist($sesion);
            }

            // 4) NIF → Árbitro
            $nif = mb_strtoupper(trim((string)$row[2]));
            $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
            if (! $arb) {
                $ignored[] = ['row' => $rowNum, 'nif' => $nif, 'reason' => 'Árbitro no encontrado'];
                continue;
            }

            // 5) Asiste?
            $asisteRaw = mb_strtolower(trim((string)$row[3]));
            $asiste    = in_array($asisteRaw, ['1','si','sí','true'], true);

            // 6) Crear/actualizar Asistencia
            $asi = $this->asistRepo->findOneBy([
                'sesion'  => $sesion,
                'arbitro' => $arb,
            ]) ?? (new Asistencia())
                    ->setSesion($sesion)
                    ->setArbitro($arb);

            $asi->setAsiste($asiste)
                ->setCategoria($categoria);

            $this->em->persist($asi);

            if ($asi->getId() === null) {
                $created[] = ['row' => $rowNum, 'nif' => $nif];
            } else {
                $updated[] = ['row' => $rowNum, 'nif' => $nif];
            }
        }

        $this->em->flush();
        @unlink($tmpPath);

        return $this->json([
            'status'        => 'success',
            'created_count' => count($created),
            'updated_count' => count($updated),
            'ignored_count' => count($ignored),
            'created'       => $created,
            'updated'       => $updated,
            'ignored'       => $ignored,
        ], 201);
    }

    // ─────────────────────────────────────────────
    // TRUNCATE TABLA ASISTENCIAS Y SESIONES
    // ─────────────────────────────────────────────
    #[Route('/truncate', name: 'asistencia_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (! $this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        $conn     = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        // Truncate asistencia primero (y reinicia PKs, cascada por FK)
        $sqlAsist = $platform->getTruncateTableSQL('asistencia', true);
        $conn->executeStatement($sqlAsist);

        // Luego truncate clase_sesion (y reinicia PKs)
        $sqlSesion = $platform->getTruncateTableSQL('clase_sesion', true);
        $conn->executeStatement($sqlSesion);

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Tablas asistencia y clase_sesion reseteadas']
        ]);
    }

}
