<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\DifficultyRepository")
 */
class Difficulty
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="difficulty")
     */
    private $questions;
    /**
     * @ORM\Column(type="integer")
     */
    private $weight;
    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getLabel(): ?string
    {
        return $this->label;
    }
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }
    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }
    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setDifficulty($this);
        }
        return $this;
    }
    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getDifficulty() === $this) {
                $question->setDifficulty(null);
            }
        }
        return $this;
    }
    public function getWeight(): ?int
    {
        return $this->weight;
    }
    public function setWeight(int $weight): self
    {
        $this->weight = $weight;
        return $this;
    }
}
