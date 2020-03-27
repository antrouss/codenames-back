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
    const STATUS_CREATED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_FINISHED = 2;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $host;

    /**
     * @ORM\Column(type="integer")
     */
    private $number_of_rounds = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Team", cascade={"persist", "remove"})
     */
    private $winner;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_CREATED;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Round", mappedBy="game", orphanRemoval=true)
     */
    private $rounds;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="game")
     */
    private $teams;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $team->setGame($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            // set the owning side to null (unless already changed)
            if ($team->getGame() === $this) {
                $team->setGame(null);
            }
        }

        return $this;
    }
}
