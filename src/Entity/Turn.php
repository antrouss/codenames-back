<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TurnRepository")
 */
class Turn
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $spy_master;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $word;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="json")
     */
    private $pointed = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Round", inversedBy="turns")
     * @ORM\JoinColumn(nullable=false)
     */
    private $round;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_IN_PROGRESS;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpyMaster(): ?User
    {
        return $this->spy_master;
    }

    public function setSpyMaster(?User $spy_master): self
    {
        $this->spy_master = $spy_master;

        return $this;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPointed(): ?array
    {
        return $this->pointed;
    }

    public function setPointed(array $pointed): self
    {
        $this->pointed = $pointed;

        return $this;
    }

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function setRound(?Round $round): self
    {
        $this->round = $round;

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
}
