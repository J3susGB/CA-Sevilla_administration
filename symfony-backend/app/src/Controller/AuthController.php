<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/reset-request', name: 'reset_request', methods: ['POST'])]
    public function requestReset(
        Request $request,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $username = $data['email'] ?? null; // Recibes el campo del formulario que pone "Usuario"

        if (!$username) {
            return $this->json(['status' => 'error', 'message' => 'Nombre de usuario requerido'], 400);
        }

        // Buscar por username, no por email
        $user = $userRepo->findOneBy(['username' => $username]);
        if (!$user) {
            return $this->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
        }

        $email = $user->getEmail();
        if (!$email) {
            return $this->json(['status' => 'error', 'message' => 'Este usuario no tiene correo electrónico asociado'], 400);
        }

        // Generar token de reseteo
        $token = Uuid::v4()->toRfc4122();
        $user->setResetToken($token);
        $user->setResetTokenExpiresAt((new \DateTime())->modify('+1 hour'));

        $em->flush();

        $resetUrl = "http://localhost:4200/reset-password?token=$token"; 

        $emailMessage = (new Email())
            ->from('jgomezbeltran88@gmail.com')
            ->to($email)
            ->subject('🔐 Recuperación de contraseña')
            ->html("
                <div style=\"font-family: Arial, sans-serif; font-size: 15px; color: #333;\">
                    <p>Hola <strong>{$user->getUsername()}</strong>,</p>

                    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>

                    <p style=\"text-align: center; margin: 20px 0;\">
                        <a href=\"$resetUrl\" style=\"
                            background-color: #4F46E5;
                            color: white;
                            padding: 10px 20px;
                            text-decoration: none;
                            border-radius: 5px;
                            display: inline-block;
                            font-weight: bold;
                        \">
                            🔒 Haz clic aquí para restablecer tu contraseña
                        </a>
                    </p>

                    <p>Este enlace es válido durante 1 hora. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>

                    <p style=\"font-size: 12px; color: #999;\">CA-Sevilla Administración</p>
                </div>
            ");

        $mailer->send($emailMessage);

        return $this->json(['status' => 'success', 'message' => 'Correo de recuperación enviado']);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['status' => 'error', 'message' => 'Token o nueva contraseña requerida'], 400);
        }

        $user = $userRepo->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            return $this->json(['status' => 'error', 'message' => 'Token inválido o expirado'], 400);
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $em->flush();

        return $this->json(['status' => 'success', 'message' => 'Contraseña actualizada correctamente']);
    }
}
