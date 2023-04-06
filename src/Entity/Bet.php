<?php

namespace App\Entity;

use App\Repository\BetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BetRepository::class)]
class Bet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $bet_ammount = null;

    #[ORM\Column(nullable: true)]
    private ?int $winnings = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'bets')]
    private ?Hand $hands = null;

    #[ORM\ManyToOne(inversedBy: 'bets')]
    private ?Game $game = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBetAmmount(): ?int
    {
        return $this->bet_ammount;
    }

    public function setBetAmmount(int $bet_ammount): self
    {
        $this->bet_ammount = $bet_ammount;

        return $this;
    }

    public function getWinnings(): ?int
    {
        return $this->winnings;
    }

    public function setWinnings(?int $winnings): self
    {
        $this->winnings = $winnings;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getHands(): ?Hand
    {
        return $this->hands;
    }

    public function setHands(?Hand $hands): self
    {
        $this->hands = $hands;

        return $this;
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
}
