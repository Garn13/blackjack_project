<?php

namespace App\Entity;

use App\Entity\Game;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TableRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TableRepository::class)]
#[ORM\Table(name: '`table`')]
class Table
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $min_bet = null;

    #[ORM\Column]
    private ?int $max_bet = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'game_table', targetEntity: game::class)]
    private Collection $games;

    #[ORM\Column]
    private ?int $nb_decks = null;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMinBet(): ?int
    {
        return $this->min_bet;
    }

    public function setMinBet(int $min_bet): self
    {
        $this->min_bet = $min_bet;

        return $this;
    }

    public function getMaxBet(): ?int
    {
        return $this->max_bet;
    }

    public function setMaxBet(int $max_bet): self
    {
        $this->max_bet = $max_bet;

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
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setGameTable($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getGameTable() === $this) {
                $game->setGameTable(null);
            }
        }

        return $this;
    }

    public function getNbDecks(): ?int
    {
        return $this->nb_decks;
    }

    public function setNbDecks(int $nb_decks): self
    {
        $this->nb_decks = $nb_decks;

        return $this;
    }
}
