<?php

namespace App\Controller;

use App\Entity\Arbitros;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/arbitros')]
final class ArbitroController extends AbstractController
{
    public function __construct(
        private readonly ArbitrosRepository $repository,
        private readonly CategoriasRepository $categoriasRepository,
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
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CAPACITACION');
    }

    // LISTADO DE TODOS LOS ÁRBITROS — ADMIN y CAPACITACION
    #[Route('', name: 'arbitro_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $page   = max(1, (int) $request->query->get('page', 1));
        $limit  = max(1, min(10000, (int) $request->query->get('limit', 25)));
        $offset = ($page - 1) * $limit;

        $arbitros = $this->repository->findBy([], null, $limit, $offset);
        $total    = $this->repository->count([]);

        $data = array_map(fn(Arbitros $a) => [
            'id'             => $a->getId(),
            'nif'            => $a->getNif(),
            'name'           => $a->getName(),
            'first_surname'  => $a->getFirstSurname(),
            'second_surname' => $a->getSecondSurname(),
            'sexo'           => $a->getSexo(),
            'categoria_id'   => $a->getCategoria()->getId(),
        ], $arbitros);

        return $this->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => ['page' => $page, 'limit' => $limit, 'total' => $total]
        ]);
    }

    // MOSTRAR ÁRBITRO por ID — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_show', methods: ['GET'])]
    public function show(Arbitros $arbitro): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'nif'            => $arbitro->getNif(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'sexo'           => $a->getSexo(),
                'categoria_id'   => $arbitro->getCategoria()->getId(),
            ]
        ]);
    }

    // CREAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('', name: 'arbitro_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);

        // Validar campos obligatorios
        if (
            empty($data['nif']) ||
            empty($data['name']) ||
            empty($data['first_surname']) ||
            empty($data['categoria_id']) ||
            empty($data['sexo'])
        ) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 400,
                    'message' => 'Campos obligatorios: nif, name, first_surname, categoria_id, sexo'
                ]
            ], 400);
        }

        $categoria = $this->categoriasRepository->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        $arbitro = (new Arbitros())
            ->setNif($data['nif'])
            ->setName($data['name'])
            ->setFirstSurname($data['first_surname'])
            ->setSecondSurname($data['second_surname'] ?? null)
            ->setSexo($data['sexo'])
            ->setCategoria($categoria);

        $this->em->persist($arbitro);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'nif'            => $arbitro->getNif(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'categoria_id'   => $categoria->getId(),
            ]
        ], 201);
    }

    // ACTUALIZAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_update', methods: ['PUT'])]
    public function update(Arbitros $arbitro, Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $data = json_decode($request->getContent(), true);
        if (! \is_array($data)) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'JSON inválido']
            ], 400);
        }

        if (array_key_exists('nif', $data) && $data['nif'] !== $arbitro->getNif()) {
            $arbitro->setNif($data['nif']);
        }

        if (array_key_exists('name', $data) && $data['name'] !== $arbitro->getName()) {
            $arbitro->setName($data['name']);
        }

        if (
            array_key_exists('first_surname', $data) &&
            $data['first_surname'] !== $arbitro->getFirstSurname()
        ) {
            $arbitro->setFirstSurname($data['first_surname']);
        }

        if (
            array_key_exists('second_surname', $data) &&
            $data['second_surname'] !== $arbitro->getSecondSurname()
        ) {
            $arbitro->setSecondSurname($data['second_surname'] ?? null);
        }

        if (array_key_exists('sexo', $data) && $data['sexo'] !== $arbitro->getSexo()) {
            $arbitro->setSexo($data['sexo']);
        }

        if (array_key_exists('categoria_id', $data)) {
            $cat = $this->categoriasRepository->find($data['categoria_id']);
            if (! $cat) {
                return $this->json([
                    'status' => 'error',
                    'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
                ], 404);
            }
            if ($cat->getId() !== $arbitro->getCategoria()->getId()) {
                $arbitro->setCategoria($cat);
            }
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => [
                'id'             => $arbitro->getId(),
                'nif'            => $arbitro->getNif(),
                'name'           => $arbitro->getName(),
                'first_surname'  => $arbitro->getFirstSurname(),
                'second_surname' => $arbitro->getSecondSurname(),
                'categoria_id'   => $arbitro->getCategoria()->getId(),
            ]
        ]);
    }

    // ELIMINAR ÁRBITRO — ADMIN y CAPACITACION
    #[Route('/{id}', name: 'arbitro_delete', methods: ['DELETE'])]
    public function delete(Arbitros $arbitro): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $this->em->remove($arbitro);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'data'   => ['message' => 'Árbitro eliminado']
        ]);
    }

    // CARGA MASIVA — ADMIN y CAPACITACION
    #[Route('/bulk-upload', name: 'arbitro_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $this->allowed()) {
            return $this->forbidden();
        }

        $file = $request->files->get('file');
        if (! $file) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'No se ha subido ningún archivo']
            ], 400);
        }

        $created = [];
        $ignored = [];
        $tmpPath = null;

        try {
            // 1) Guardar temporalmente y leer el XLSX
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('arb_bulk_') . '.' . $file->getClientOriginalExtension();
            $file->move(\dirname($tmpPath), \basename($tmpPath));
            $sheet  = IOFactory::load($tmpPath)->getActiveSheet();
            $rows   = $sheet->toArray();
            array_shift($rows); // eliminamos cabecera

            foreach ($rows as $i => $row) {
                $rowNum = $i + 2; // para referencia en reportes

                if (count($row) < 14) {
                    $ignored[] = ['row' => $rowNum, 'reason' => 'Formato de fila inválido'];
                    continue;
                }

                // 1) Parseo de nombre completo ➡ name, first_surname, second_surname
                $raw = trim((string)$row[2]);
                $givenName     = '';
                $firstSurname  = '';
                $secondSurname = null;
                if (str_contains($raw, ',')) {
                    [$partA, $partB] = array_map('trim', explode(',', $raw, 2));
                    $names           = preg_split('/\s+/', $partA, -1, PREG_SPLIT_NO_EMPTY);
                    $firstSurname    = $names[0] ?? '';
                    $secondSurname   = $names[1] ?? null;
                    $givenName       = $partB;
                } else {
                    $parts = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($parts) === 1) {
                        $givenName = $parts[0];
                    } elseif (count($parts) === 2) {
                        [$givenName, $firstSurname] = $parts;
                    } else {
                        $givenName     = array_shift($parts);
                        $firstSurname  = array_shift($parts);
                        $secondSurname = array_shift($parts) ?? null;
                    }
                }

                // 2) NIF (columna 1)
                $nifRaw = trim((string)$row[1]);
                if ($nifRaw === '') {
                    $ignored[] = ['row' => $rowNum, 'reason' => 'NIF vacío'];
                    continue;
                }
                $nifNormalized = mb_strtoupper($nifRaw);

                // 3) Sexo (columna 3)
                $sexoRaw = trim((string)$row[3]);
                $sexoNormalized = mb_strtoupper($sexoRaw);
                if (!in_array($sexoNormalized, ['MASCULINO', 'FEMENINO'])) {
                    $ignored[] = [
                        'row' => $rowNum,
                        'nif' => $nifRaw,
                        'reason' => "Sexo inválido: “{$sexoRaw}”. Debe ser MASCULINO o FEMENINO"
                    ];
                    continue;
                }

                // 4) Categoría (columna 13)
                $catRaw    = trim((string)$row[13]);
                $catName   = ucfirst(strtolower($catRaw));
                $categoria = $this->categoriasRepository->findOneBy(['name' => $catName]);
                if (! $categoria) {
                    $ignored[] = [
                        'row'    => $rowNum,
                        'nif'    => $nifRaw,
                        'reason' => "Categoría “{$catRaw}” no encontrada"
                    ];
                    continue;
                }

                // 5) Comprobar duplicado por NIF
                if ($this->repository->findOneBy(['nif' => $nifNormalized])) {
                    $ignored[] = [
                        'row'    => $rowNum,
                        'nif'    => $nifRaw,
                        'reason' => 'Ya existe'
                    ];
                    continue;
                }

                // 6) Crear y persistir árbitro
                $arb = (new Arbitros())
                    ->setNif($nifRaw)
                    ->setName($givenName)
                    ->setFirstSurname($firstSurname)
                    ->setSecondSurname($secondSurname)
                    ->setSexo($sexoNormalized)
                    ->setCategoria($categoria);

                $this->em->persist($arb);
                $created[] = [
                    'row'            => $rowNum,
                    'nif'            => $arb->getNif(),
                    'name'           => $givenName,
                    'first_surname'  => $firstSurname,
                    'second_surname' => $secondSurname,
                    'sexo'           => $sexoNormalized,
                    'categoria_id'   => $categoria->getId(),
                ];
            }

            // 7) Guardar en base de datos
            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'data'   => [
                    'created_count' => count($created),
                    'ignored_count' => count($ignored),
                    'created'       => $created,
                    'ignored'       => $ignored,
                ]
            ], 201);
        } catch (\Throwable $e) {
            return $this->json([
                'status' => 'error',
                'error'  => [
                    'code'    => 500,
                    'message' => 'Error en carga masiva',
                    'details' => $e->getMessage()
                ]
            ], 500);
        } finally {
            if ($tmpPath && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

}
