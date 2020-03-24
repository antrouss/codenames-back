<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team")
     */
    private $teams;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $host;

    /**
     * @ORM\Column(type="integer")
     */
    private $number_of_rounds;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Team", cascade={"persist", "remove"})
     */
    private $winner;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $map = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $words = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $progress = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Round", mappedBy="game", orphanRemoval=true)
     */
    private $rounds;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    public function getHost(): ?User
    {
        return $this->host;
    }

    public function setHost(?User $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getNumberOfRounds(): ?int
    {
        return $this->number_of_rounds;
    }

    public function setNumberOfRounds(int $number_of_rounds): self
    {
        $this->number_of_rounds = $number_of_rounds;

        return $this;
    }

    public function getWinner(): ?Team
    {
        return $this->winner;
    }

    public function setWinner(?Team $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getMap(): ?array
    {
        return $this->map;
    }

    public function setMap(?array $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function getWords(): ?array
    {
        return $this->words;
    }

    public function setWords(?array $words): self
    {
        $this->words = $words;

        return $this;
    }

    public function getProgress(): ?array
    {
        return $this->progress;
    }

    public function setProgress(?array $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Rounds[]
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): self
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds[] = $round;
            $round->setGame($this);
        }

        return $this;
    }

    public function removeRound(Round $round): self
    {
        if ($this->rounds->contains($round)) {
            $this->rounds->removeElement($round);
            // set the owning side to null (unless already changed)
            if ($round->getGame() === $this) {
                $round->setGame(null);
            }
        }

        return $this;
    }
}
