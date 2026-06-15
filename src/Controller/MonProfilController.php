<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileFormType;
use App\Security\EmailVerifier;
use App\Service\MailerConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MonProfilController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly MailerConfig $mailerConfig,
    ) {
    }

    #[Route('/profil', name: 'app_mon_profil', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $previousEmail = $user->getEmail();
        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailChanged = $previousEmail !== $user->getEmail();
            $user->setIsVerified(true);

            $entityManager->flush();

            if ($emailChanged) {
                $security->login($user, 'form_login', 'main');

                if ($this->mailerConfig->isConfigured()) {
                    $this->emailVerifier->sendEmailConfirmation(
                        'app_verify_email',
                        $user,
                        (new TemplatedEmail())
                            ->from(new Address($this->mailerConfig->getFromEmail(), $this->mailerConfig->getFromName()))
                            ->to((string) $user->getEmail())
                            ->subject('Confirmez votre nouvelle adresse email — Lyceo Campus')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                            ->textTemplate('registration/confirmation_email.txt.twig')
                    );
                    $this->addFlash(
                        'success',
                        'Vos informations ont été mises à jour. Un email de confirmation a été envoyé à votre nouvelle adresse.'
                    );
                } else {
                    $this->addFlash(
                        'warning',
                        'Vos informations ont été mises à jour. Veuillez confirmer votre nouvelle adresse email lorsque l\'envoi sera disponible.'
                    );
                }
            } else {
                $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');
            }

            return $this->redirectToRoute('app_mon_profil');
        }

        return $this->render('mon_profil/index.html.twig', [
            'user' => $user,
            'profileForm' => $form,
        ]);
    }
}
