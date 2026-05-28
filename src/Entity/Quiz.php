<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_quiz_chapitre', columns: ['chapitre_id'])]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\OneToOne(inversedBy: 'quiz')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Chapitre $chapitre;

    #[ORM\Column(options: ['default' => 70])]
    private int $seuilReussite = 70;

    /**
     * @var Collection<int, QuestionQuiz>
     */
    #[ORM\OneToMany(targetEntity: QuestionQuiz::class, mappedBy: 'quiz', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $questions;

    public function __construct(Chapitre $chapitre)
    {
        $this->chapitre = $chapitre;
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getChapitre(): Chapitre
    {
        return $this->chapitre;
    }

    public function setChapitre(Chapitre $chapitre): static
    {
        $this->chapitre = $chapitre;

        return $this;
    }

    public function getSeuilReussite(): int
    {
        return $this->seuilReussite;
    }

    public function setSeuilReussite(int $seuilReussite): static
    {
        $this->seuilReussite = max(1, min(100, $seuilReussite));

        return $this;
    }

    /**
     * @return Collection<int, QuestionQuiz>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuestionQuiz $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(QuestionQuiz $question): static
    {
        if ($this->questions->removeElement($question) && $question->getQuiz() === $this) {
            $question->setQuiz(null);
        }

        return $this;
    }

    public function getFormation(): Formation
    {
        return $this->chapitre->getFormation();
    }
}
