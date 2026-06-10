<?php

namespace App\Controller;

use App\Repository\FormationUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/paiements')]
<<<<<<< HEAD
#[IsGranted('ROLE_SUPER_ADMIN')]
=======
#[IsGranted('ROLE_ADMIN')]
>>>>>>> 4755d1d843c648a2ebbb0d5b87978621e16dc484
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
