<?php

namespace App\Entity;

use App\Repository\QuizFinalTentativeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizFinalTentativeRepository::class)]
class QuizFinalTentative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Formation $formation;

    #[ORM\Column]
    private int $score = 0;

    #[ORM\Column]
    private \DateTimeImmutable $dateTentative;

    public function __construct(User $user, Formation $formation, int $score)
    {
        $this->user = $user;
        $this->formation = $formation;
        $this->score = $score;
        $this->dateTentative = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getDateTentative(): \DateTimeImmutable
    {
        return $this->dateTentative;
    }
}
