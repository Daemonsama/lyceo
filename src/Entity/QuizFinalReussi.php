<?php

namespace App\Entity;

use App\Repository\QuizFinalReussiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizFinalReussiRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_user_formation_final', columns: ['user_id', 'formation_id'])]
class QuizFinalReussi
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
    private \DateTimeImmutable $dateReussite;

    public function __construct(User $user, Formation $formation, int $score)
    {
        $this->user = $user;
        $this->formation = $formation;
        $this->score = $score;
        $this->dateReussite = new \DateTimeImmutable();
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

    public function getDateReussite(): \DateTimeImmutable
    {
        return $this->dateReussite;
    }
}
