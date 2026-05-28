<?php

namespace App\Services;

use App\Entity\Chapitre;
use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\User;
use App\Repository\ChapitreRepository;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;

class NextChap
{
    public function __construct(
        private ChapitreRepository $chapitreRepository,
        private FormationUserRepository $formationUserRepository
    ) {}

    public function chapitre_suivant(User $user, Formation $formation): ?Chapitre
    {
        if (!$user->hasFormation($formation)) {
            return null;
        }

        $formationUser = $this->formationUserRepository->findOneBy([
            'user' => $user,
            'formation' => $formation
        ]);

        if ($formationUser->getChapitreEncours() === null) {
            $nextChap = $formation->getChapitres()->first();
        } else {
            $nextOrder = $formationUser->getChapitreEncours()->getOrdre() + 1;
            $nextChap = $this->chapitreRepository->findOneBy([
                'formation' => $formation,
                'ordre' => $nextOrder
            ]);
        }

        if ($nextChap !== null) {
            $formationUser->setChapitreEncours($nextChap);
            $this->formationUserRepository->save($formationUser);
        }

        return $nextChap;
    }
}
