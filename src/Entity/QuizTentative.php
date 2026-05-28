<?php

namespace App\Entity;

use App\Repository\QuizTentativeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizTentativeRepository::class)]
class QuizTentative
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
    private Quiz $quiz;

    #[ORM\Column]
    private int $score = 0;

    #[ORM\Column]
    private \DateTimeImmutable $dateTentative;

    public function __construct(User $user, Quiz $quiz, int $score)
    {
        $this->user = $user;
        $this->quiz = $quiz;
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

    public function getQuiz(): Quiz
    {
        return $this->quiz;
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
