<?php

namespace App\Controller;

use App\Repository\InformesRepository;
use App\Repository\EntrenamientosRepository;
use App\Repository\SimulacrosRepository;
use App\Repository\FisicaRepository;
use App\Repository\SancionesRepository;
use App\Repository\TecnicosRepository;
use App\Repository\TecnicoSessionRepository;
use App\Repository\TestRepository;
use App\Repository\TestSessionRepository;
use App\Repository\AsistenciaRepository;
use App\Repository\ClaseSesionRepository;
use App\Repository\ArbitrosRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private InformesRepository $informesRepo,
        private EntrenamientosRepository $entrenamientosRepo,
        private SimulacrosRepository $simulacrosRepo,
        private FisicaRepository $fisicaRepo,
        private SancionesRepository $sancionesRepo,
        private TecnicosRepository $tecnicosRepo,
        private TecnicoSessionRepository $tecnicoSessionRepo,
        private TestRepository $testRepo,
        private TestSessionRepository $testSessionRepo,
        private AsistenciaRepository $asistenciaRepo,
        private ClaseSesionRepository $claseRepo,
        private ArbitrosRepository $arbitrosRepo
    ) {}

    #[Route('/reset', name: 'admin_reset', methods: ['POST'])]
    public function reset(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        $errores = [];

        try { $this->asistenciaRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'asistencia: ' . $e->getMessage(); }
        try { $this->claseRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'claseSession: ' . $e->getMessage(); }
        try { $this->testRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'test: ' . $e->getMessage(); }
        try { $this->testSessionRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'testSession: ' . $e->getMessage(); }
        try { $this->tecnicosRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'tecnicos: ' . $e->getMessage(); }
        try { $this->tecnicoSessionRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'tecnicoSession: ' . $e->getMessage(); }
        try { $this->informesRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'informes: ' . $e->getMessage(); }
        try { $this->sancionesRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'sanciones: ' . $e->getMessage(); }
        try { $this->entrenamientosRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'entrenamientos: ' . $e->getMessage(); }
        try { $this->simulacrosRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'simulacros: ' . $e->getMessage(); }
        try { $this->fisicaRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'fisica: ' . $e->getMessage(); }
        try { $this->arbitrosRepo->truncate(); } catch (\Throwable $e) { $errores[] = 'arbitros: ' . $e->getMessage(); } // SIEMPRE EL ÚLTIMO

        return empty($errores)
            ? $this->json(['status' => 'success', 'message' => 'Base de datos reseteada completamente.'])
            : $this->json(['status' => 'partial_success', 'errors' => $errores], 207);
    }
}
