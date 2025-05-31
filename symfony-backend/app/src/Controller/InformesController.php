<?php

namespace App\Controller;

use App\Entity\Informes;
use App\Entity\Arbitros;
use App\Entity\Categorias;
use App\Repository\InformesRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/informes')]
final class InformesController extends AbstractController
{
    public function __construct(
        private readonly InformesRepository   $informesRepo,
        private readonly ArbitrosRepository   $arbRepo,
        private readonly CategoriasRepository $catRepo,
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
        return $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_INFORMACION')
            || $this->isGranted('ROLE_CLASIFICACION');
    }

    // ─────────────────────────────────────────────
    // LISTAR TODOS LOS INFORMES
    // ─────────────────────────────────────────────
    #[Route('', name: 'informes_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $out = [];
        $informes = $this->informesRepo->findAll();

        foreach ($informes as $inf) {
            $arb = $inf->getArbitro();
            $cat = $inf->getCategoria();
            $out[] = [
                'id'            => $inf->getId(),
                'arbitro_id'    => $arb->getId(),
                'nif'           => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname'=> $arb->getSecondSurname(),
                'name'          => $arb->getName(),
                'categoria_id'  => $cat->getId(),
                'categoria'     => $cat->getName(),
                'fecha'         => $inf->getFecha()->format('d-m-Y'),
                'nota'          => $inf->getNota(),
            ];
        }

        return $this->json([
            'status' => 'success',
            'data'   => $out,
        ]);
    }

    // ─────────────────────────────────────────────
    // MOSTRAR UN INFORME POR ID
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'informes_show', methods: ['GET'])]
    public function show(Informes $informe): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $arb = $informe->getArbitro();
        $cat = $informe->getCategoria();

        $data = [
            'id'            => $informe->getId(),
            'arbitro_id'    => $arb->getId(),
            'nif'           => $arb->getNif(),
            'first_surname' => $arb->getFirstSurname(),
            'second_surname'=> $arb->getSecondSurname(),
            'name'          => $arb->getName(),
            'categoria_id'  => $cat->getId(),
            'categoria'     => $cat->getName(),
            'fecha'         => $informe->getFecha()->format('d-m-Y'),
            'nota'          => $informe->getNota(),
        ];

        return $this->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    // ─────────────────────────────────────────────
    // CREAR UN INFORME MANUALMENTE
    // ─────────────────────────────────────────────
    #[Route('', name: 'informes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        // Campos obligatorios: nif, nota, categoria_id, fecha
        if (
            ! isset($data['nif']) ||
            ! array_key_exists('nota', $data) ||
            ! isset($data['categoria_id']) ||
            empty($data['fecha'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 400,
                    'message' => 'Requiere nif, nota, categoria_id y fecha'
                ]
            ], 400);
        }

        // Buscamos categoría
        $categoria = $this->catRepo->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        // Solo se permiten informes para árbitros de categoría "Provincial"
        if (strtolower($categoria->getName()) !== 'provincial') {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Solo se pueden asignar informes a árbitros de categoría Provincial']
            ], 400);
        }

        // Buscamos árbitro por NIF
        $nif = mb_strtoupper($data['nif']);
        $arb = $this->arbRepo->findOneBy(['nif' => $nif]);
        if (! $arb) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Árbitro no encontrado']
            ], 404);
        }

        // Verificamos que el árbitro realmente pertenezca a la categoría Provincial
        if ($arb->getCategoria()->getId() !== $categoria->getId()) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'El árbitro no pertenece a la categoría Provincial indicada']
            ], 400);
        }

        // Parseamos fecha (dd/mm/YYYY o dd/mm/yy)
        $fechaRaw = trim($data['fecha']);
        $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
               ?: \DateTimeImmutable::createFromFormat('d/m/y', $fechaRaw);
        if (! $fecha) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Fecha inválida']
            ], 400);
        }

        $nota = (float) $data['nota'];

        // Vemos si ya existe un informe para ese árbitro en esa fecha
        $existing = $this->informesRepo->findOneBy([
            'arbitro'  => $arb,
            'fecha'    => $fecha,
        ]);

        if ($existing) {
            // Si ya existe, actualizamos la nota
            $existing->setNota($nota)
                     ->setCategoria($categoria);
            $action = 'updated';
            $informe = $existing;
        } else {
            // Creamos uno nuevo
            $informe = (new Informes())
                ->setArbitro($arb)
                ->setCategoria($categoria)
                ->setFecha($fecha)
                ->setNota($nota);
            $this->em->persist($informe);
            $action = 'created';
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'           => $informe->getId(),
                'arbitro_id'   => $arb->getId(),
                'nif'          => $arb->getNif(),
                'categoria'    => $categoria->getName(),
                'categoria_id' => $categoria->getId(),
                'fecha'        => $informe->getFecha()->format('d-m-Y'),
                'nota'         => $informe->getNota(),
                'action'       => $action
            ]
        ], $action === 'created' ? 201 : 200);
    }

    // ─────────────────────────────────────────────
    // ACTUALIZAR UN INFORME EXISTENTE
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'informes_update', methods: ['PUT'])]
    public function update(Informes $informe, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        // Podemos actualizar al menos la nota, y opcionalmente fecha o categoría
        if (! isset($data['nota']) && ! isset($data['fecha']) && ! isset($data['categoria_id'])) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Debe indicar al menos nota, fecha o categoria_id para actualizar']
            ], 400);
        }

        // Si proporcionan categoría nueva, validamos
        if (isset($data['categoria_id'])) {
            $newCat = $this->catRepo->find($data['categoria_id']);
            if (! $newCat) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
                ], 404);
            }
            if (strtolower($newCat->getName()) !== 'provincial') {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 400, 'message' => 'Solo se permiten informes para categoría Provincial']
                ], 400);
            }
            $informe->setCategoria($newCat);
        }

        // Si proporcionan fecha nueva, la parseamos
        if (isset($data['fecha'])) {
            $fechaRaw = trim($data['fecha']);
            $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
                   ?: \DateTimeImmutable::createFromFormat('d/m/y', $fechaRaw);
            if (! $fecha) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 400, 'message' => 'Fecha inválida']
                ], 400);
            }
            $informe->setFecha($fecha);
        }

        // Nota
        if (isset($data['nota'])) {
            $informe->setNota((float) $data['nota']);
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'           => $informe->getId(),
                'arbitro_id'   => $informe->getArbitro()->getId(),
                'nif'          => $informe->getArbitro()->getNif(),
                'categoria'    => $informe->getCategoria()->getName(),
                'categoria_id' => $informe->getCategoria()->getId(),
                'fecha'        => $informe->getFecha()->format('d-m-Y'),
                'nota'         => $informe->getNota(),
            ]
        ]);
    }

    // ─────────────────────────────────────────────
    // ELIMINAR UN INFORME
    // ─────────────────────────────────────────────
    #[Route('/{id<\d+>}', name: 'informes_delete', methods: ['DELETE'])]
    public function delete(Informes $informe): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($informe);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Informe eliminado']
        ]);
    }

    // ─────────────────────────────────────────────
    // CARGA MASIVA DE INFORMES DESDE EXCEL
    // ─────────────────────────────────────────────
    #[Route('/bulk-upload', name: 'informes_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        // 1) Sólo ROLE_ADMIN o ROLE_INFORMACION o ROLE_CLASIFICACION pueden acceder
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        // 2) Recibe el archivo
        $file = $request->files->get('file');
        if (! $file) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'No se ha subido ningún archivo'],
            ], 400);
        }

        // 3) Lo guarda temporalmente
        $tmpPath = sys_get_temp_dir().'/'.uniqid('informes_bulk_').'.'.$file->getClientOriginalExtension();
        $file->move(\dirname($tmpPath), \basename($tmpPath));

        // 4) Carga el Excel con PhpSpreadsheet
        try {
            $spreadsheet = IOFactory::load($tmpPath);
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'No se pudo leer el archivo Excel'],
            ], 400);
        }
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray();
        array_shift($rows); // quitamos la fila de encabezado

        $created = [];
        $updated = [];
        $ignored = [];

        // 5) Buscamos UNA SOLA vez la entidad "Provincial" en la tabla Categorias
        $provCatEntity = $this->catRepo->findOneBy(['name' => 'Provincial']);

        // 6) Si no existe la categoría Provincial, no hay nada que hacer
        if (! $provCatEntity) {
            @unlink($tmpPath);
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 500, 'message' => 'Categoría Provincial no existe en la base de datos'],
            ], 500);
        }

        // 7) Cargamos UNA SOLA vez todos los árbitros cuya categoría sea "Provincial"
        $arbitrosProvinciales = $this->arbRepo->findBy(['categoria' => $provCatEntity]);

        // 8) Creamos un mapa de “nombreCompleto → entidad Arbitros”
        //    clave = strtoupper("first_surname second_surname, name")
        $mapArbitroPorNombreCompleto = [];
        foreach ($arbitrosProvinciales as $arb) {
            $fullNameKey = strtoupper(
                trim($arb->getFirstSurname() . ' ' . $arb->getSecondSurname() . ', ' . $arb->getName())
            );
            $mapArbitroPorNombreCompleto[$fullNameKey] = $arb;
        }

        // 9) Recorremos cada fila del Excel
        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // para indicar la fila del Excel (empezando en 2)

            // 9.1) Validamos que haya al menos 4 columnas
            if (count($row) < 4) {
                $ignored[] = [
                    'row'    => $rowNum,
                    'reason' => 'Columnas insuficientes'
                ];
                continue;
            }

            // 9.2) Extraemos las columnas: [0]=Fecha, [1]=Árbitro, [2]=Nota, [3]=Categoría
            $fechaRaw     = trim((string) $row[0]); 
            $arbitroRaw   = trim((string) $row[1]);
            $notaRaw      = trim((string) $row[2]);
            $categoriaRaw = ucfirst(mb_strtolower(trim((string) $row[3])));

            // 9.3) Validamos que la categoría sea “Provincial”
            if ($categoriaRaw !== 'Provincial') {
                $ignored[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw,
                    'reason'  => "Solo se asignan informes a categoría Provincial (se recibió '{$categoriaRaw}')"
                ];
                continue;
            }

            // 9.4) Normalizamos y buscamos el árbitro completo en el mapa preconstruido
            $normalizedKey = strtoupper($arbitroRaw);
            if (! isset($mapArbitroPorNombreCompleto[$normalizedKey])) {
                $ignored[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw,
                    'reason'  => 'Árbitro no encontrado en BD'
                ];
                continue;
            }
            $arb = $mapArbitroPorNombreCompleto[$normalizedKey];

            // 9.5) Parseamos Fecha (probamos 'd/m/Y', 'd/m/y', 'd-m-Y', 'd-m-y')
            $fecha = \DateTimeImmutable::createFromFormat('d/m/Y', $fechaRaw)
                   ?: \DateTimeImmutable::createFromFormat('d/m/y', $fechaRaw);

            if (! $fecha) {
                $fecha = \DateTimeImmutable::createFromFormat('d-m-Y', $fechaRaw)
                       ?: \DateTimeImmutable::createFromFormat('d-m-y', $fechaRaw);
            }

            if (! $fecha) {
                $ignored[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw,
                    'reason'  => "Fecha inválida: '{$fechaRaw}'"
                ];
                continue;
            }

            // 9.6) Validamos Nota
            if (! is_numeric($notaRaw)) {
                $ignored[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw,
                    'reason'  => "Nota inválida: '{$notaRaw}'"
                ];
                continue;
            }
            $nota = (float) $notaRaw;

            // 9.7) Sin límite, creamos o actualizamos el informe
            $informeExistente = $this->informesRepo->findOneBy([
                'arbitro' => $arb,
                'fecha'   => $fecha,
            ]);

            if ($informeExistente) {
                // Actualizamos la nota
                $informeExistente->setNota($nota);
                $action  = 'updated';
                $informe = $informeExistente;
            } else {
                // Creamos uno nuevo
                $informe = (new Informes())
                    ->setArbitro($arb)
                    ->setCategoria($provCatEntity)
                    ->setFecha($fecha)
                    ->setNota($nota);
                $this->em->persist($informe);
                $action = 'created';
            }

            $this->em->flush();

            if ($action === 'created') {
                $created[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw
                ];
            } else {
                $updated[] = [
                    'row'     => $rowNum,
                    'arbitro' => $arbitroRaw
                ];
            }
        }

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
    // TRUNCAR TABLA informes (solo ROLE_ADMIN)
    // ─────────────────────────────────────────────
    #[Route('/truncate', name: 'informes_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        // 1) Solo administrador puede hacerlo
        if (! $this->isGranted('ROLE_ADMIN')) {
            return $this->forbidden();
        }

        // 2) Obtenemos la conexión y la plataforma de la BD
        $conn     = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        // 3) Ejecutamos TRUNCATE en la tabla “informes”
        //    El segundo parámetro “true” indica CASCADE (depende de tu plataforma)
        $conn->executeStatement($platform->getTruncateTableSQL('informes', true));

        // 4) Respondemos con éxito
        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Tabla informes truncada correctamente']
        ]);
    }
}
