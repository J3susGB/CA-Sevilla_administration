<?php
// src/Controller/TecnicosController.php

namespace App\Controller;

use App\Entity\Tecnicos;
use App\Entity\TecnicoSession;
use App\Repository\TecnicosRepository;
use App\Repository\TecnicoSessionRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tecnicos')]
class TecnicosController extends AbstractController
{
    public function __construct(
        private readonly TecnicosRepository         $tecRepo,
        private readonly TecnicoSessionRepository   $sessRepo,
        private readonly ArbitrosRepository         $arbRepo,
        private readonly CategoriasRepository       $catRepo,
        private readonly EntityManagerInterface     $em
    ) {}

    private function forbidden(): JsonResponse
    {
        return $this->json([
            'status'=>'error',
            'error'=>['code'=>403,'message'=>'No autorizado']
        ], 403);
    }

    private function allowed(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CAPACITACION');
    }

    // ──────────────────────────────────────────────────
    // LISTAR TODAS LAS SESSIONS TÉCNICAS CON NOTAS 
    // (incluyendo árbitros sin examen con nota = 0)
    // ──────────────────────────────────────────────────
    #[Route('', name: 'tec_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        /** @var TecnicoSession[] $sessions */
        $sessions = $this->sessRepo->findBy([], ['fecha' => 'DESC', 'examNumber' => 'ASC']);

        foreach ($sessions as $session) {
            $cat      = $session->getCategoria();
            /** @var \App\Entity\Arbitros[] $arbitros */
            $arbitros = $this->arbRepo->findBy(['categoria' => $cat]);

            // 1) Construir un mapa { arbitro_id => Tecnicos } de los registros existentes
            $mapNotas = [];
            /** @var Tecnicos $t */
            foreach ($session->getTecnicos() as $t) {
                $mapNotas[$t->getArbitro()->getId()] = $t;
            }

            // 2) Para cada árbitro de la categoría, si existe en BD usamos sus datos;
            //    si no existe, devolvemos nota=0, repesca=false y id=null
            $notasPorArbitro = [];
            foreach ($arbitros as $arb) {
                $arbId = $arb->getId();

                if (isset($mapNotas[$arbId])) {
                    // Registro real en la tabla `tecnicos`
                    $t     = $mapNotas[$arbId];
                    $nota  = $t->getNota();
                    $repe  = $t->isRepesca();
                    $tecId = $t->getId();   // <-- aquí tomamos el ID real
                } else {
                    // No hay registro en BD → nota sintética 0, repesca=false, id=null
                    $nota  = 0.0;
                    $repe  = false;
                    $tecId = null;
                }

                $notasPorArbitro[] = [
                    'id'             => $tecId,                // <–– añadimos este campo
                    'arbitro_id'     => $arb->getId(),
                    'nif'            => $arb->getNif(),
                    'first_surname'  => $arb->getFirstSurname(),
                    'second_surname' => $arb->getSecondSurname(),
                    'name'           => $arb->getName(),
                    'nota'           => (float) $nota,
                    'repesca'        => $repe,
                ];
            }

            $out[] = [
                'id'           => $session->getId(),            // ID de la sesión (TecnicoSession)
                'fecha'        => $session->getFecha()->format('d-m-Y'),
                'examNumber'   => $session->getExamNumber(),
                'categoria'    => $cat->getName(),
                'categoria_id' => $cat->getId(),
                'notas'        => $notasPorArbitro,
            ];
        }

        return $this->json(
            [
                'status' => 'success',
                'data'   => $out,
            ],
            200,
            [],
            // Esto asegura que los floats con decimal “.0” se mantengan como 3.0, 0.0, etc.
            [ 'json_encode_options' => JSON_PRESERVE_ZERO_FRACTION ]
        );
    }

     // ─────────────────────────────────────────────
    // MOSTRAR UNA NOTA TÉCNICA POR SU ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'tec_show', methods: ['GET'])]
    public function show(\App\Entity\Tecnicos $tec): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $session   = $tec->getSession();
        $arb       = $tec->getArbitro();
        $categoria = $session->getCategoria();

        $data = [
            'id'            => $tec->getId(),
            'sessionId'     => $session->getId(),
            'fecha'         => $session->getFecha()->format('d-m-Y'),
            'examNumber'    => $session->getExamNumber(),
            'categoria_id'  => $categoria->getId(),
            'categoria'     => $categoria->getName(),
            'arbitro_id'    => $arb->getId(),
            'nif'           => $arb->getNif(),
            'first_surname' => $arb->getFirstSurname(),
            'second_surname'=> $arb->getSecondSurname(),
            'name'          => $arb->getName(),
            'nota'          => $tec->getNota(),
        ];

        return $this->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    // ─────────────────────────────────────────────
    // LISTADO AGRUPADO: CATEGORÍA → EXAMEN → ÁRBITROS CON NOTA
    // ─────────────────────────────────────────────
    #[Route('/report', name: 'tec_report', methods: ['GET'])]
    public function report(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        $categorias = $this->catRepo->findAll();

        /** @var \App\Entity\Categorias $cat */
        foreach ($categorias as $cat) {
            $catId   = $cat->getId();
            $catName = $cat->getName();

            // Vamos a recopilar los exámenes 1, 2 y 3 para esta categoría
            $exams = [];

            for ($examNum = 1; $examNum <= 3; $examNum++) {
                // Construimos el QueryBuilder
                $qb = $this->tecRepo->createQueryBuilder('t')
                    ->join('t.session', 's')
                    ->join('t.arbitro',  'a')
                    ->select('a.first_surname   AS first_surname')
                    ->addSelect('a.second_surname  AS second_surname')
                    ->addSelect('a.name           AS name')
                    ->addSelect('s.examNumber     AS exam_number')
                    ->addSelect('t.nota           AS nota')
                    ->addSelect('t.repesca        AS repesca')
                    ->where('s.categoria = :cat')
                    ->andWhere('s.examNumber = :num')
                    ->setParameter('cat', $cat)        // <- aquí reemplazamos setParameters([...])
                    ->setParameter('num', $examNum)    // <- por dos llamadas a setParameter()
                    ->orderBy('a.first_surname', 'ASC')
                    ->addOrderBy('a.second_surname', 'ASC')
                    ->getQuery();

                $entries = $qb->getArrayResult();
                // $entries será un array de arrays con:
                // [ 'first_surname'=>..., 'second_surname'=>..., 'name'=>..., 'exam_number'=>..., 'nota'=>..., 'repesca'=>... ]

                // Solo añadimos este examen si hay al menos una nota registrada
                if (count($entries) > 0) {
                    $exams[$examNum] = $entries;
                }
            }

            // Si al menos un examen (1, 2 ó 3) tiene datos, lo incluimos en la salida
            if (count($exams) > 0) {
                $out[] = [
                    'categoria_id' => $catId,
                    'categoria'    => $catName,
                    'exams'        => $exams,
                ];
            }
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // CARGA MASIVA DE NOTAS TÉCNICAS
    // ─────────────────────────────────────────────
#[Route('/bulk-upload', name: 'tec_bulk_upload', methods: ['POST'])]
public function bulkUpload(Request $request): JsonResponse
{
    if (! $this->allowed()) {
        return $this->forbidden();
    }

    $file = $request->files->get('file');
    if (! $file) {
        return $this->json([
            'status' => 'error',
            'error'  => ['code' => 400, 'message' => 'Falta el archivo'],
        ], 400);
    }

    // 1) Guardamos temporalmente el XLS
    $tmpPath = sys_get_temp_dir().'/'.uniqid('tec_bulk_').'.'.$file->getClientOriginalExtension();
    $file->move(\dirname($tmpPath), \basename($tmpPath));

    $sheet = IOFactory::load($tmpPath)->getActiveSheet();
    $rows  = $sheet->toArray();
    array_shift($rows); // quitamos la fila de cabecera

    $created    = [];
    $updated    = [];
    $ignored    = [];
    $currentKey = null;
    $session    = null;

    // Para controlar, por sesión, qué árbitros ya fueron procesados (para luego insertar los faltantes)
    $processedArbitrosPorSession = []; // clave `$currentKey` → array de arbitro_id procesados

    foreach ($rows as $i => $row) {
        $rowNum = $i + 2; // número de fila en el Excel

        // 2) Validar número mínimo de columnas
        if (count($row) < 5) {
            $ignored[] = ['row' => $rowNum, 'reason' => 'Columnas insuficientes'];
            continue;
        }

        // 3) Leer columnas básicas
        $fRaw    = trim((string)$row[0]); // fecha
        $exRaw   = trim((string)$row[1]); // examNumber
        $nifRaw  = mb_strtoupper(trim((string)$row[2]));
        $notaRaw = trim((string)$row[3]);
        $catRaw  = ucfirst(mb_strtolower(trim((string)$row[4])));

        // 4) Parsear fecha y validarla
        $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fRaw)
               ?: \DateTimeImmutable::createFromFormat('d/m/y', $fRaw);
        if (! $fecha) {
            $ignored[] = ['row' => $rowNum, 'reason' => "Fecha inválida: {$fRaw}"];
            continue;
        }

        // 5) Validar examNumber (1,2,3)
        $examNumber = filter_var($exRaw, FILTER_VALIDATE_INT);
        if ($examNumber === false || $examNumber < 1 || $examNumber > 3) {
            $ignored[] = ['row' => $rowNum, 'reason' => "Examén inválido: {$exRaw}"];
            continue;
        }

        // 6) Validar categoría (buscar entidad Categorias)
        $categoria = $this->catRepo->findOneBy(['name' => $catRaw]);
        if (! $categoria) {
            $ignored[] = ['row' => $rowNum, 'reason' => "Categoría no encontrada: {$catRaw}"];
            continue;
        }

        // 7) Construir la “clave de sesión” para agrupar filas del Excel
        //    (por ejemplo “2024-09-10|1|3” → fecha|examNumber|categoria_id)
        $key = "{$fecha->format('Y-m-d')}|{$examNumber}|{$categoria->getId()}";

        // 8) Si la clave actual difiere de $currentKey, significa que acabamos de pasar
        //    de una sesión a otra. Entonces, antes de procesar la nueva, cerramos la sesión anterior:
        if ($key !== $currentKey) {
            // Si ya había una sesión anterior abierta, debemos insertar “faltantes” para esa sesión
            if ($currentKey !== null) {
                // Insertar los árbitros no procesados de la sesión anterior
                $this->insertarFaltantesParaSesion($processedArbitrosPorSession[$currentKey], $session, $created, $updated);
            }
            // Ahora sí, cambiamos a la nueva sesión (o la creamos si no existe)
            $currentKey = $key;
            $session = $this->sessRepo->findOneBy([
                'fecha'      => $fecha,
                'examNumber' => $examNumber,
                'categoria'  => $categoria
            ]) ?? (new TecnicoSession())
                    ->setFecha($fecha)
                    ->setExamNumber($examNumber)
                    ->setCategoria($categoria);

            $this->em->persist($session);

            // Inicializamos el array de árbitros procesados para esta nueva sesión
            $processedArbitrosPorSession[$currentKey] = [];
        }

        // 9) Buscar árbitro en BD
        $arb = $this->arbRepo->findOneBy(['nif' => $nifRaw]);
        if (! $arb) {
            $ignored[] = ['row' => $rowNum, 'nif' => $nifRaw, 'reason' => 'Árbitro no existe'];
            continue;
        }

        // 10) Validar nota
        if (! is_numeric($notaRaw)) {
            $ignored[] = ['row' => $rowNum, 'reason' => "Nota inválida: {$notaRaw}"];
            continue;
        }
        $nota = (float)$notaRaw;

        // 11) Verificar si ya existe un registro Tecnicos para (session, arbitro)
        /** @var Tecnicos|null $t */
        $t = $this->tecRepo->findOneBy([
            'session' => $session,
            'arbitro' => $arb,
        ]);

        if ($t) {
            // Si ya existía, actualizamos nota y dejamos repesca = false (por defecto)
            $t->setNota($nota)
              ->setRepesca(false);

            $updated[] = ['row' => $rowNum, 'nif' => $nifRaw];
        } else {
            // Si no existía, creamos uno nuevo con retención de repesca = false
            $t = (new Tecnicos())
                ->setSession($session)
                ->setArbitro($arb)
                ->setNota($nota)
                ->setRepesca(false);

            $this->em->persist($t);
            $created[] = ['row' => $rowNum, 'nif' => $nifRaw];
        }

        // 12) Marcamos este árbitro como “procesado” para esta sesión,
        //     para no volver a insertar nota=0 más tarde:
        $processedArbitrosPorSession[$currentKey][] = $arb->getId();
    }

        // 13) Después de recorrer todas las filas del Excel,
        //     aún queda pendiente “cerrar” la última sesión abierta:
        if ($currentKey !== null) {
            $this->insertarFaltantesParaSesion($processedArbitrosPorSession[$currentKey], $session, $created, $updated);
        }

        // 14) Persistir todo en la BD y eliminar el fichero temporal
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

    /**
     * Inserta en la BD, para una sesión dada, todos los árbitros de la categoría
     * que NO estén en el array $arbitrosProcesados, con nota=0 y repesca=false.
     *
     * @param int[]             $arbitrosProcesados  IDs de árbitros que YA vinieron en el Excel
     * @param TecnicoSession    $session             La sesión técnica que estamos cerrando
     * @param array             &$created            Referencia al array “created” (añade filas aquí)
     * @param array             &$updated            Referencia al array “updated” (no se usa aquí)
     */
    private function insertarFaltantesParaSesion(array $arbitrosProcesados, TecnicoSession $session, array & $created, array & $updated): void
    {
        // Obtener todos los árbitros que pertenezcan a la categoría de $session
        $categoria = $session->getCategoria();
        /** @var \App\Entity\Arbitros[] $arbitrosDeCategoria */
        $arbitrosDeCategoria = $this->arbRepo->findBy(['categoria' => $categoria]);

        foreach ($arbitrosDeCategoria as $arb) {
            $arbId = $arb->getId();
            // Si este ID NO está en $arbitrosProcesados, significa que el Excel no lo trajo
            if (! in_array($arbId, $arbitrosProcesados, true)) {
                // Verificar de nuevo si por casualidad ya existe en BD (para evitar duplicados)
                $existe = $this->tecRepo->findOneBy([
                    'session' => $session,
                    'arbitro' => $arb,
                ]);

                if (! $existe) {
                    // Insertar nuevo registro con nota = 0, repesca = false
                    $nuevo = (new Tecnicos())
                        ->setSession($session)
                        ->setArbitro($arb)
                        ->setNota(0.0)
                        ->setRepesca(false);

                    $this->em->persist($nuevo);
                    $created[] = [
                        'row' => 'auto',             // “auto” para indicar que no vino del Excel
                        'nif' => $arb->getNif(),
                        'nota'=> 0.0
                    ];
                }
            }
        }
    }

    // ─────────────────────────────────────────────
    // Crear o actualizar nota técnica individual
    // ─────────────────────────────────────────────
    #[Route('', name: 'tec_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        // Validación de campos obligatorios
        if (
            ! isset($data['nif'])
            || ! array_key_exists('nota', $data)
            || ! isset($data['categoria_id'])
            || (! isset($data['sessionId']) && (! isset($data['fecha']) || ! isset($data['examNumber'])))
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Requiere nif, nota, categoria_id y (sessionId ó (fecha,examNumber))']
            ], 400);
        }

        // **Validar categoría**
        $cat = $this->catRepo->find($data['categoria_id']);
        if (! $cat) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        // **1) Si me envían sessionId, busco la sesión directamente**
        if (! empty($data['sessionId'])) {
            $session = $this->sessRepo->find((int)$data['sessionId']);
            if (! $session) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 404, 'message' => 'Sesión no encontrada']
                ], 404);
            }
        }
        // **2) Si me envían fecha/examNumber, buscar o crear sesión**
        else {
            $f = \DateTimeImmutable::createFromFormat('d/m/Y', $data['fecha'])
               ?: \DateTimeImmutable::createFromFormat('d/m/y', $data['fecha']);
            if (! $f) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 400, 'message' => 'Fecha inválida']
                ], 400);
            }

            $examNum = (int)$data['examNumber'];
            $session = $this->sessRepo->findOneBy([
                'fecha'      => $f,
                'examNumber' => $examNum,
                'categoria'  => $cat
            ]) ?? (new TecnicoSession())
                ->setFecha($f)
                ->setExamNumber($examNum)
                ->setCategoria($cat);

            $this->em->persist($session);
        }

        // **Buscar árbitro por NIF**
        $arb = $this->arbRepo->findOneBy(['nif' => mb_strtoupper($data['nif'])]);
        if (! $arb) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Árbitro no encontrado']
            ], 404);
        }

        // **Obtener nota y repesca (opcional)** 
        $nota = (float)$data['nota'];
        // Si viene “repesca” en el JSON y es true (o “sí”), lo interpretamos como booleano true:
        $repesca = false;
        if (isset($data['repesca'])) {
            // Aceptamos “true”, “1”, “yes”, “sí”, “SÍ”, 1… Normalmente bastará con forzar a bool:
            $repesca = filter_var($data['repesca'], FILTER_VALIDATE_BOOLEAN);
        }

        // **Verificar si ya existe ‘Tecnicos’ para (session, arbitro)**
        $tec = $this->tecRepo->findOneBy([
            'session' => $session,
            'arbitro' => $arb
        ]);

        $action = 'created';
        if ($tec) {
            // Actualizamos nota y repesca
            $tec->setNota($nota)
                ->setRepesca($repesca);
            $action = 'updated';
        } else {
            // Creamos nueva entidad
            $tec = (new Tecnicos())
                ->setSession($session)
                ->setArbitro($arb)
                ->setNota($nota)
                ->setRepesca($repesca);
            $this->em->persist($tec);
        }

        $this->em->flush();

        // Construimos la respuesta
        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'          => $tec->getId(),
                'sessionId'   => $session->getId(),
                'fecha'       => $session->getFecha()->format('d-m-Y'),
                'examNumber'  => $session->getExamNumber(),
                'categoria'   => $cat->getName(),
                'arbitro_id'  => $arb->getId(),
                'nif'         => $arb->getNif(),
                'nota'        => $tec->getNota(),
                'repesca'     => $tec->isRepesca(),  // true/false
                'action'      => $action,
            ]
        ], $action === 'created' ? 201 : 200);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR NOTA TÉCNICA
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name:'tec_delete', methods:['DELETE'])]
    public function delete(Tecnicos $tec): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }
        $this->em->remove($tec);
        $this->em->flush();
        return $this->json([
            'status'=>'success',
            'data'=>['message'=>'Nota eliminada']
        ]);
    }

    // ─────────────────────────────────────────────
    // TRUNCATE TABLAS TECNICOS Y TECNICO_SESSION
    // ─────────────────────────────────────────────
    #[Route('/truncate', name: 'tec_truncate', methods: ['POST'])]
    public function truncate(EntityManagerInterface $em): JsonResponse
    {
        // Solo ROLE_ADMIN puede vaciar estas dos tablas
        if (! $this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 403,
                    'message' => 'No autorizado',
                ],
            ], 403);
        }

        // Obtenemos la conexión y la plataforma
        $conn     = $em->getConnection();
        $platform = $conn->getDatabasePlatform();

        // Primero truncamos tecnicos, luego tecnico_session (por orden de clave foránea)
        $conn->executeStatement($platform->getTruncateTableSQL('tecnicos', true));
        $conn->executeStatement($platform->getTruncateTableSQL('tecnico_session', true));

        return $this->json([
            'status' => 'success',
            'data'   => [
                'message' => 'Tablas tecnicos y tecnico_session reseteadas',
            ],
        ]);
    }
}
