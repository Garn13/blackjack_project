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

    public function getValue(): ?int
    {
        return $this->value;
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
        $nbAs = 0;
        foreach ($cards as &$card) {
            $cardFigure = substr($card, 1);
            if (!in_array($cardFigure, ["j", "q", "k", "1"])) {
                $value += intval($cardFigure);
            } elseif ($cardFigure == "1") {
                $nbAs++;
            } else {
                $value += 10;
            }
        }
        if ($nbAs != 0 && $value == 10) {
            $value = 21;
            $nbAs = "blackjack";
        }

        $returnedArray = [$value, $nbAs];
        return $returnedArray;
    }

    public function calculateDealerValue(): array
    {
        $cards = $this->getCards();
        $value = 0;
        $nbAs = 0;
        foreach ($cards as &$card) {
            $cardFigure = substr($card, 1);
            if (!in_array($cardFigure, ["j", "q", "k", "1"])) {
                $value += intval($cardFigure);
            } elseif ($cardFigure == "1") {
                if ($nbAs == 0) {
                    $value += 11;
                } elseif ($nbAs != 0) {
                    if ($value + 11 > 21) {
                        $value++;
                    } else {
                        $value += 11;
                    }
                }
                $nbAs++;
            } else {
                $value += 10;
            }
        }
        if ($nbAs != 0 && $value == 10) {
            $value = 21;
            $nbAs = "blackjack";
        }

        $returnedArray = [$value, $nbAs];
        return $returnedArray;
    }

    public function calculateHitValue(): array
    {
        $cards = $this->getCards();
        $newCard = end($cards);
        $value = $this->getValue();
        $nbAs = 0;
        $status = $this->getStatus();

        $cardFigure = substr($newCard, 1);
        if (!in_array($cardFigure, ["j", "q", "k", "1"])) {
            $value += intval($cardFigure);
        } elseif ($cardFigure == "1") {
            $nbAs++;
        } else {
            $value += 10;
        }

        if ($nbAs == 1) {
            if ($value > 10) {
                $value++;
            } else {
                $status = "choosing";
            }
        }

        $returnedArray = [$value, $status];
        return $returnedArray;
    }

    public function calculateDealerNewValue($newCard): ?int
    {
        $value = $this->getValue();
        $cards = $this->getCards();
        $nbAs = 0;

        $cardFigure = substr($newCard, 1);
        if (!in_array($cardFigure, ["j", "q", "k", "1"])) {
            $value += intval($cardFigure);
        } elseif ($cardFigure == "1") {
            foreach ($cards as &$card) {
                $oldCardFigure = substr($card, 1);
                if ($oldCardFigure == "1") {
                    $nbAs++;
                }
            }

            if ($nbAs == 0) {
                $value += 11;
            } elseif ($nbAs > 0) {
                if ($value + 11 > 21) {
                    $value++;
                } else {
                    $value += 11;
                }
            }
        } else {
            $value += 10;
        }

        return $value;
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
