<?php

namespace App\Controller;

use App\Entity\Entrenamientos;
use App\Repository\EntrenamientosRepository;
use App\Repository\ArbitrosRepository;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/entrenamientos')]
class EntrenamientosController extends AbstractController
{
    public function __construct(
        private readonly EntrenamientosRepository $entRepo,
        private readonly ArbitrosRepository $arbRepo,
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
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CLASIFICACION');
    }

    #[Route('', name: 'entrenamientos_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $data = json_decode($request->getContent(), true);

        if (!isset($data['nif']) || !isset($data['categoria_id'])) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 400, 'message' => 'Faltan campos obligatorios: nif y categoria_id']
            ], 400);
        }

        $nif = mb_strtoupper($data['nif']);
        $arbitro = $this->arbRepo->findOneBy(['nif' => $nif]);
        if (! $arbitro) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Árbitro no encontrado']
            ], 404);
        }

        $categoria = $this->catRepo->find($data['categoria_id']);
        if (! $categoria) {
            return $this->json([
                'status' => 'error',
                'error'  => ['code' => 404, 'message' => 'Categoría no encontrada']
            ], 404);
        }

        $ent = new Entrenamientos();
        $ent->setArbitro($arbitro)->setCategoria($categoria);

        foreach (['septiembre','octubre','noviembre','diciembre','enero','febrero','marzo','abril'] as $mes) {
            if (isset($data[$mes])) {
                $ent->{'set' . ucfirst($mes)}((int) $data[$mes]);
            }
        }

        $this->em->persist($ent);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $ent->getId()]], 201);
    }

    #[Route('', name: 'entrenamientos_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $entrenamientos = $this->entRepo->findAllOrderedByArbitro();
        $out = [];

        foreach ($entrenamientos as $ent) {
            $arb = $ent->getArbitro();
            $cat = $ent->getCategoria();

            $out[] = [
                'id' => $ent->getId(),
                'arbitro_id' => $arb->getId(),
                'nif' => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname' => $arb->getSecondSurname(),
                'name' => $arb->getName(),
                'categoria_id' => $cat->getId(),
                'categoria' => $cat->getName(),
                'septiembre' => $ent->getSeptiembre(),
                'octubre' => $ent->getOctubre(),
                'noviembre' => $ent->getNoviembre(),
                'diciembre' => $ent->getDiciembre(),
                'enero' => $ent->getEnero(),
                'febrero' => $ent->getFebrero(),
                'marzo' => $ent->getMarzo(),
                'abril' => $ent->getAbril(),
            ];
        }

        return $this->json(['status' => 'success', 'data' => $out]);
    }

    #[Route('/{id<\d+>}', name: 'entrenamientos_show', methods: ['GET'])]
    public function show(Entrenamientos $ent): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $arb = $ent->getArbitro();
        $cat = $ent->getCategoria();

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $ent->getId(),
                'arbitro_id' => $arb->getId(),
                'nif' => $arb->getNif(),
                'first_surname' => $arb->getFirstSurname(),
                'second_surname' => $arb->getSecondSurname(),
                'name' => $arb->getName(),
                'categoria_id' => $cat->getId(),
                'categoria' => $cat->getName(),
                'septiembre' => $ent->getSeptiembre(),
                'octubre' => $ent->getOctubre(),
                'noviembre' => $ent->getNoviembre(),
                'diciembre' => $ent->getDiciembre(),
                'enero' => $ent->getEnero(),
                'febrero' => $ent->getFebrero(),
                'marzo' => $ent->getMarzo(),
                'abril' => $ent->getAbril(),
            ]
        ]);
    }

    #[Route('/{id<\d+>}', name: 'entrenamientos_update', methods: ['PUT'])]
    public function update(Entrenamientos $ent, Request $request): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $data = json_decode($request->getContent(), true);

        if (isset($data['categoria_id'])) {
            $categoria = $this->catRepo->find($data['categoria_id']);
            if ($categoria) {
                $ent->setCategoria($categoria);
            }
        }

        foreach (['septiembre','octubre','noviembre','diciembre','enero','febrero','marzo','abril'] as $mes) {
            if (isset($data[$mes])) {
                $ent->{'set' . ucfirst($mes)}((int) $data[$mes]);
            }
        }

        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['id' => $ent->getId()]]);
    }

    #[Route('/{id<\d+>}', name: 'entrenamientos_delete', methods: ['DELETE'])]
    public function delete(Entrenamientos $ent): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $this->em->remove($ent);
        $this->em->flush();

        return $this->json(['status' => 'success', 'data' => ['message' => 'Entrenamiento eliminado']]);
    }

    #[Route('/bulk-upload', name: 'entrenamientos_bulk_upload', methods: ['POST'])]
    public function bulkUpload(Request $request): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $file = $request->files->get('file');
        if (! $file) {
            return $this->json(['status' => 'error', 'error' => ['code' => 400, 'message' => 'No se ha subido ningún archivo']], 400);
        }

        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headers = array_map('strtoupper', array_map('trim', array_shift($rows)));
        $mesesValidos = ['SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE','ENERO','FEBRERO','MARZO','ABRIL'];
        $mesesEnExcel = array_filter($headers, fn($h) => in_array($h, $mesesValidos));

        $normalize = fn(string $s) => trim(preg_replace('/\s+/u', ' ', strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s))));

        $todosArbitros = $this->arbRepo->findAll();
        $mapArbitros = [];
        foreach ($todosArbitros as $arb) {
            $key = $normalize($arb->getFirstSurname().' '.$arb->getSecondSurname().' '.$arb->getName());
            $mapArbitros[$key] = $arb;
        }

        $created = $updated = $skipped = $missing = $procesados = [];

        foreach ($rows as $i => $row) {
            $data = array_combine($headers, array_map('trim', $row));
            $nombreExcel = $data['ARBITRO'] ?? null;
            if (!$nombreExcel) {
                $skipped[] = ['row' => $i + 2, 'reason' => 'Nombre de árbitro vacío'];
                continue;
            }

            $key = $normalize($nombreExcel);
            $procesados[] = $key;

            if (!isset($mapArbitros[$key])) {
                $skipped[] = ['row' => $i + 2, 'arbitro' => $nombreExcel, 'key' => $key, 'reason' => 'Árbitro no encontrado'];
                continue;
            }

            $arb = $mapArbitros[$key];
            $cat = $arb->getCategoria();
            $ent = $this->entRepo->findOneByArbitroAndCategoria($arb->getId(), $cat->getId()) ?? new Entrenamientos();
            $ent->setArbitro($arb)->setCategoria($cat);

            $wasNew = !$ent->getId();

            foreach ($mesesEnExcel as $mes) {
                $ent->{'set' . ucfirst(strtolower($mes))}((int) ($data[$mes] ?? 0));
            }

            $this->em->persist($ent);

            if ($wasNew) {
                $created[] = ['arbitro' => $nombreExcel];
            } else {
                $updated[] = ['arbitro' => $nombreExcel];
            }
        }

        foreach ($todosArbitros as $arb) {
            $key = $normalize($arb->getFirstSurname().' '.$arb->getSecondSurname().' '.$arb->getName());
            if (in_array($key, $procesados)) continue;

            $cat = $arb->getCategoria();
            if (!in_array(strtolower($cat->getName()), ['provincial', 'oficial'])) continue;

            $ent = $this->entRepo->findOneByArbitroAndCategoria($arb->getId(), $cat->getId()) ?? new Entrenamientos();
            $ent->setArbitro($arb)->setCategoria($cat);

            foreach ($mesesEnExcel as $mes) {
                $ent->{'set' . ucfirst(strtolower($mes))}(0);
            }

            $this->em->persist($ent);
            $missing[] = [
                'arbitro' => $arb->getFirstSurname().' '.$arb->getSecondSurname().' '.$arb->getName(),
                'categoria' => $cat->getName(),
                'meses_0' => $mesesEnExcel
            ];
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'created' => $created,
            'updated' => $updated,
            'missing' => $missing,
            'skipped' => $skipped,
            'created_count' => count($created),
            'updated_count' => count($updated),
            'missing_count' => count($missing),
            'skipped_count' => count($skipped),
        ], 201);
    }

    #[Route('/truncate', name: 'entrenamientos_truncate', methods: ['POST'])]
    public function truncate(): JsonResponse
    {
        if (! $this->isGranted('ROLE_ADMIN')) return $this->forbidden();

        $this->em->getConnection()->executeStatement(
            $this->em->getConnection()->getDatabasePlatform()->getTruncateTableSQL('entrenamientos', true)
        );

        return $this->json(['status' => 'success', 'data' => ['message' => 'Tabla truncada correctamente']]);
    }

    #[Route('/total-por-arbitro', name: 'entrenamientos_totales', methods: ['GET'])]
    public function totalPorArbitro(): JsonResponse
    {
        if (! $this->allowed()) return $this->forbidden();

        $out = [];
        foreach ($this->entRepo->findAll() as $ent) {
            $arb = $ent->getArbitro();
            $key = $arb->getId();

            if (!isset($out[$key])) {
                $out[$key] = [
                    'arbitro_id' => $arb->getId(),
                    'nif' => $arb->getNif(),
                    'first_surname' => $arb->getFirstSurname(),
                    'second_surname' => $arb->getSecondSurname(),
                    'name' => $arb->getName(),
                    'total' => 0,
                ];
            }

            $out[$key]['total'] += array_sum([
                $ent->getSeptiembre(), $ent->getOctubre(), $ent->getNoviembre(),
                $ent->getDiciembre(), $ent->getEnero(), $ent->getFebrero(),
                $ent->getMarzo(), $ent->getAbril()
            ]);
        }

        return $this->json(['status' => 'success', 'data' => array_values($out)]);
    }
}
