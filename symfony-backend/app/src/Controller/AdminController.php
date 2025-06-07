<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private RequestStack $requestStack
    ) {}

    #[Route('/reset', name: 'admin_reset', methods: ['POST'])]
    public function reset(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        $baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $routes = [
            '/api/informes/truncate',
            '/api/entrenamientos/truncate',
            '/api/simulacros/truncate',
            '/api/fisica/truncate',
            '/api/sanciones/truncate',
            '/api/tecnicos/truncate',
            '/api/test/truncate',
            '/api/asistencia/truncate',
            '/api/arbitros/truncate' // esta debe ir la última
        ];

        $errores = [];

        foreach ($routes as $ruta) {
            try {
                $this->client->request('POST', $baseUrl . $ruta, [
                    'headers' => [
                        'Authorization' => $this->requestStack->getCurrentRequest()->headers->get('Authorization')
                    ]
                ]);
            } catch (\Throwable $e) {
                $errores[] = $ruta . ' → ' . $e->getMessage();
            }
        }

        return empty($errores)
            ? $this->json(['status' => 'success', 'message' => 'Base de datos reseteada completamente.'])
            : $this->json(['status' => 'partial_success', 'errors' => $errores], 207);
    }
}