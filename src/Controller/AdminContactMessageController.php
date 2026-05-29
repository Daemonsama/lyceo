<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/contact')]
final class AdminContactMessageController extends AbstractController
{
    #[Route('', name: 'app_admin_contact_index', methods: ['GET'])]
    public function index(ContactMessageRepository $contactMessageRepository): Response
    {
        return $this->render('admin_contact_message/index.html.twig', [
            'messages' => $contactMessageRepository->findAllNewestFirst(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_contact_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(ContactMessage $message): Response
    {
        return $this->render('admin_contact_message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_contact_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, ContactMessage $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_contact'.$message->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
            $this->addFlash('success', 'Le message a été supprimé.');
        }

        return $this->redirectToRoute('app_admin_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
