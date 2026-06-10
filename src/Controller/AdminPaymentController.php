<?php

namespace App\Controller;

use App\Repository\FormationUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/paiements')]
<<<<<<<<< Temporary merge branch 1
#[IsGranted('ROLE_ADMIN')]
=========
#[IsGranted('ROLE_SUPER_ADMIN')]
>>>>>>>>> Temporary merge branch 2
final class AdminPaymentController extends AbstractController
{
    #[Route('', name: 'app_admin_payments', methods: ['GET'])]
    public function index(FormationUserRepository $formationUserRepository): Response
    {
        return $this->render('admin_payments/index.html.twig', [
            'stats' => $formationUserRepository->getPaymentOverview(),
            'payments' => $formationUserRepository->findAllForAdmin(),
        ]);
    }
}
