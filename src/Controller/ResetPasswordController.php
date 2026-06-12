<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ResetPasswordRequestRepository;
use App\Service\MailerConfig;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private MailerConfig $mailerConfig,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ?string $token = null,
    ): Response {
        if ($token) {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('Aucun jeton de réinitialisation trouvé.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            $this->addFlash('reset_password_error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();

            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $email): RedirectResponse
    {
        if (!$this->mailerConfig->isConfigured()) {
            $this->addFlash('reset_password_error', 'Email non configuré : vérifiez MAILER_DSN dans le fichier .env.local.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException) {
            // Demande précédente (ex. email resté en file d'attente) : on autorise un nouvel envoi
            $this->resetPasswordRequestRepository->removeRequests($user);
            try {
                $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            } catch (ResetPasswordExceptionInterface) {
                return $this->redirectToRoute('app_check_email');
            }
        } catch (ResetPasswordExceptionInterface) {
            return $this->redirectToRoute('app_check_email');
        }

        $emailMessage = $this->buildResetPasswordEmail($user, $resetToken);

        try {
            $this->mailer->send($emailMessage);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Échec envoi email réinitialisation MDP', ['exception' => $e]);
            $this->addFlash('reset_password_error', 'Connexion SMTP refusée : vérifiez dans AlwaysData (E-mails) que l\'adresse et le mot de passe dans .env.local correspondent exactement au compte email.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }

    private function buildResetPasswordEmail(User $user, object $resetToken): TemplatedEmail
    {
        $emailMessage = (new TemplatedEmail())
            ->from(new Address($this->mailerConfig->getFromEmail(), $this->mailerConfig->getFromName()))
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de votre mot de passe — Lyceo Campus')
            ->locale('fr')
            ->htmlTemplate('reset_password/email.html.twig')
            ->textTemplate('reset_password/email.txt.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $logoPath = $this->getParameter('kernel.project_dir').'/public/image/logo2-transparent.png';
        if (is_readable($logoPath)) {
            $emailMessage->embedFromPath($logoPath, 'lyceo_logo', 'image/png');
        }

        return $emailMessage;
    }
}
