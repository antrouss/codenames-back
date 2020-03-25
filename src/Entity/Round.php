<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoundsRepository")
 */
class Round
{
    const STATUS_IN_PROGRESS = 1;
    const STATUS_FINISHED = 2;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="rounds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $spy_master_1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $spy_master_2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team")
     */
    private $starting_team;

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
     * @ORM\OneToOne(targetEntity="App\Entity\Team", cascade={"persist", "remove"})
     */
    private $winner;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_IN_PROGRESS;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Turn", mappedBy="round", orphanRemoval=true)
     */
    private $turns;

    public function __construct()
    {
        $this->turns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getSpyMaster1(): ?User
    {
        return $this->spy_master_1;
    }

    public function setSpyMaster1(?User $spy_master_1): self
    {
        $this->spy_master_1 = $spy_master_1;

        return $this;
    }

    public function getSpyMaster2(): ?User
    {
        return $this->spy_master_2;
    }

    public function setSpyMaster2(?User $spy_master_2): self
    {
        $this->spy_master_2 = $spy_master_2;

        return $this;
    }

    public function getStartingTeam(): ?Team
    {
        return $this->starting_team;
    }

    public function setStartingTeam(?Team $starting_team): self
    {
        $this->starting_team = $starting_team;

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
     * @return Collection|Turn[]
     */
    public function getTurns(): Collection
    {
        return $this->turns;
    }

    public function addTurn(Turn $turn): self
    {
        if (!$this->turns->contains($turn)) {
            $this->turns[] = $turn;
            $turn->setRound($this);
        }

        return $this;
    }

    public function removeTurn(Turn $turn): self
    {
        if ($this->turns->contains($turn)) {
            $this->turns->removeElement($turn);
            // set the owning side to null (unless already changed)
            if ($turn->getRound() === $this) {
                $turn->setRound(null);
            }
        }

        return $this;
    }
}
