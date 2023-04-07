<?php

namespace App\Entity;

use App\Repository\HandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HandRepository::class)]
class Hand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $cards = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'hands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\OneToMany(mappedBy: 'hands', targetEntity: Bet::class)]
    private Collection $bets;

    #[ORM\ManyToOne(inversedBy: 'hands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function __construct()
    {
        $this->bets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function setCards(array $cards): self
    {
        $this->cards = $cards;

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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Collection<int, Bet>
     */
    public function getBets(): Collection
    {
        return $this->bets;
    }

    public function addBet(Bet $bet): self
    {
        if (!$this->bets->contains($bet)) {
            $this->bets->add($bet);
            $bet->setHands($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getHands() === $this) {
                $bet->setHands(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getValue(?int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setValue(?int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function calculateValue(): array
    {
        $cards = $this->getCards();
        $value = 0;
        $isAs = false;
        foreach ($cards as &$card) {
            $substring = substr($card, 1);
            if (!in_array($substring, ["j", "q", "k"])) {
                $value += intval($substring);
            } elseif ($substring == "1") {
                $isAs = true;
            } else {
                $value += 10;
            }
        }
        if ($isAs && $value == 10) {
            $value = 21;
            $isAs = "blackjack";
        }

        $returnedArray = [$value, $isAs];
        return $returnedArray;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
