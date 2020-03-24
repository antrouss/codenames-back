<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoundsRepository")
 */
class Round
{
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
}
