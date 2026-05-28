<?php

namespace App\Entity;

use App\Repository\ReponseQuizRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseQuizRepository::class)]
class ReponseQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?QuestionQuiz $question = null;

    #[ORM\Column(length: 500)]
    private ?string $libelle = null;

    #[ORM\Column]
    private bool $estCorrecte = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?QuestionQuiz
    {
        return $this->question;
    }

    public function setQuestion(?QuestionQuiz $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function isEstCorrecte(): bool
    {
        return $this->estCorrecte;
    }

    public function setEstCorrecte(bool $estCorrecte): static
    {
        $this->estCorrecte = $estCorrecte;

        return $this;
    }
}
