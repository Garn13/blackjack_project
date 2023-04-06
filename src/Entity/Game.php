<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use function PHPUnit\Framework\isNull;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $card_deck = [];

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'games')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Hand::class, orphanRemoval: true)]
    private Collection $hands;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Bet::class)]
    private Collection $bets;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Table $game_table = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->hands = new ArrayCollection();
        $this->bets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCardDeck(): array
    {
        return $this->card_deck;
    }

    public function setCardDeck(int $nbDeck, array $deck = null): self
    {
        $this->card_deck = $this->shuffleDeck($nbDeck);

        return $this;
    }

    public function replaceCardDeck(array $deck): self
    {
        $this->card_deck = $deck;

        return $this;
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addGame($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeGame($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Hand>
     */
    public function getHands(): Collection
    {
        return $this->hands;
    }

    public function addHand(Hand $hand): self
    {
        if (!$this->hands->contains($hand)) {
            $this->hands->add($hand);
            $hand->setGame($this);
        }

        return $this;
    }

    public function removeHand(Hand $hand): self
    {
        if ($this->hands->removeElement($hand)) {
            // set the owning side to null (unless already changed)
            if ($hand->getGame() === $this) {
                $hand->setGame(null);
            }
        }

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
            $bet->setGame($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->removeElement($bet)) {
            // set the owning side to null (unless already changed)
            if ($bet->getGame() === $this) {
                $bet->setGame(null);
            }
        }

        return $this;
    }


    public function getGameTable(): ?Table
    {
        return $this->game_table;
    }

    public function setGameTable(?Table $game_table): self
    {
        $this->game_table = $game_table;

        return $this;
    }
    public function shuffleDeck(int $nbDeck): array
    {
        $card_deck =
            [
                "h1", "h2", "h3", "h4", "h5", "h6", "h7", "h8", "h9", "h10", "hj", "hq", "hk",
                "d1", "d2", "d3", "d4", "d5", "d6", "d7", "d8", "d9", "d10", "dj", "dq", "dk",
                "s1", "s2", "s3", "s4", "s5", "s6", "s7", "s8", "s9", "s10", "sj", "sq", "sk",
                "c1", "c2", "c3", "c4", "c5", "c6", "c7", "c8", "c9", "c10", "cj", "cq", "ck"
            ];

        $initial_deck = [];

        for ($i = 0; $i < $nbDeck; $i++) {
            $initial_deck = array_merge($initial_deck, $card_deck);
        }
        $shuffled_deck = [];
        while (!empty($initial_deck)) {
            $card_index = random_int(0, sizeof($initial_deck) - 1);
            array_push($shuffled_deck, $initial_deck[$card_index]);
            array_splice($initial_deck, $card_index, 1);
        }

        return $shuffled_deck;
    }
}
