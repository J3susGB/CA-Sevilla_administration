<?php

namespace App\Controller;

use App\Repository\CategoriasRepository;
use App\Service\ClasificacionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/clasificacion')]
class ClasificacionController extends AbstractController
{
    public function __construct(
        private readonly ClasificacionService $clasificacionService,
        private readonly CategoriasRepository $catRepo
    ) {}

    #[Route('/{categoria}', name: 'clasificacion_por_categoria', methods: ['GET'])]
    public function generar(string $categoria): BinaryFileResponse|JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_CLASIFICACION')) {
            return $this->json([
                'status' => 'error',
                'error' => ['code' => 403, 'message' => 'No autorizado']
            ], 403);
        }

        $cat = $this->catRepo->findOneBy(['name' => ucfirst(strtolower($categoria))]);
        if (!$cat) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => "Categoría '$categoria' no encontrada"]
            ], 404);
        }

        // Lógica según categoría
        $filePath = match (strtoupper($categoria)) {
            'PROVINCIAL' => $this->clasificacionService->generarExcelProvincial($cat),
            'OFICIAL'    => $this->clasificacionService->generarExcelOficial($cat),
            'AUXILIAR'   => $this->clasificacionService->generarExcelAuxiliar($cat),
            default      => null
        };

        if (!$filePath) {
            return $this->json([
                'status' => 'error',
                'error'  => ['message' => "No se pudo generar el Excel para la categoría '$categoria'"]
            ], 500);
        }

        return new BinaryFileResponse($filePath, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => (new ResponseHeaderBag())->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($filePath)
            ),
        ]);
    }
}
