<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Hand;
use App\Entity\Table;
use DateTimeImmutable;
use App\Repository\GameRepository;
use App\Repository\HandRepository;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameController extends AbstractController
{
    #[Route('/game', name: 'game')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
        ]);
    }

    #[Route('/game/create', name: 'create_game')]
    public function create(Request $request, EntityManagerInterface $em, TableRepository $tableRepository): Response
    {
        $game = new Game();
        $table = $tableRepository->find($request->query->get('id'));
        $game->setGameTable($table);
        $game->setStatus("pending");
        $game->setCardDeck($table->getNbDecks());
        $game->setCreatedAt(new DateTimeImmutable());
        $game->setUpdatedAt(new DateTimeImmutable());
        $game->addUser($this->getUser());
        $em->persist($game);
        $em->flush();

        return $this->redirectToRoute('play_game', [
            'game' => $game->getId(), 'table' => $table->getId(),
        ]);
    }

    #[Route('/game/play', name: 'play_game')]
    public function play(Request $request, TableRepository $tableRepository, GameRepository $gameRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($this->getUser());
        $table = $tableRepository->find($request->query->get('table'));
        $game = $gameRepository->find($request->query->get('game'));

        return $this->render('game/play.html.twig', [
            'game' => $game, 'table' => $table, 'balance' => $user->getBalance()
        ]);
    }

    #[Route('/game/deal', name: 'deal_game')]
    public function deal(Request $request, HandRepository $handRepository, GameRepository $gameRepository, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $game = $gameRepository->find($request->query->get('game'));
        $playersHands = $handRepository->getPlayersHands($game->getId());
        $data = [];
        foreach ($playersHands as &$hand) {
            $newHand = ["h1", "h7"];
            $deck = $game->getCardDeck();
            // array_push($newHand, $deck[0], $deck[1]);
            // array_splice($deck, 0, 2);
            $game->replaceCardDeck($deck);
            $em->persist($game);
            $hand->setCards($newHand);
            $calculatedValue = $hand->calculateValue();
            if ($calculatedValue[1] == "blackjack") {
                $hand->setValue($calculatedValue[0]);
                $hand->setStatus("blackjack");
            } elseif (intval($calculatedValue[1]) > 0) {
                $hand->setValue($calculatedValue[0]);
                $hand->setStatus(strval($calculatedValue[1]));
            } elseif (intval($calculatedValue[1]) == 0) {
                $hand->setValue($calculatedValue[0]);
                $hand->setStatus("playing");
            }

            $em->persist($hand);
            $em->flush();
            $id = $hand->getId();
            $data["$id"] = [$newHand, $hand->getStatus(), $hand->getValue()];
        }

        $dealerHand = new Hand();
        $dealerHand->setUser($userRepository->find(2));
        $dealerHand->setGame($game);
        $dealerHand->setStatus("playing");
        $dealerHand->setCreatedAt(new DateTimeImmutable());
        $dealerHand->setUpdatedAt(new DateTimeImmutable());
        $em->persist($dealerHand);
        $em->flush();
        $deck = $game->getCardDeck();
        $newDealerHand = [];
        array_push($newDealerHand, $deck[0]);
        $dealerHand->setCards($newDealerHand);
        $dealerHand->setValue($dealerHand->calculateDealerValue()[0]);
        $em->persist($dealerHand);
        $data["dealer"] = [$deck[0], $dealerHand->getStatus(), $dealerHand->getValue()];
        array_splice($deck, 0, 1);
        $game->replaceCardDeck($deck);
        $em->persist($game);
        $em->flush();

        return $this->json($data);
    }

    #[Route('/game/{id}/dealerturn', name: 'dealer_turn_game')]
    public function dealerTurn(Game $game, HandRepository $handRepository, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($this->getUser());
        $playerBet = $game->getBets()[0];
        $dealerHand = $handRepository->getDealerHand($game->getId())[0];
        $playersHands = $handRepository->getPlayersHands($game->getId());
        $playerHand = end($playersHands);
        $playerValue = $playerHand->getValue();
        $lastCard = false;
        while ($dealerHand->getValue() <= 16 && !$lastCard) {
            $deck = $game->getCardDeck();
            $oldHand = $dealerHand->getCards();
            $newDealerCard = $deck[0];
            array_splice($deck, 0, 1);
            $game->replaceCardDeck($deck);
            $em->persist($game);
            $em->flush();
            $newValue = $dealerHand->calculateDealerNewValue($newDealerCard);
            array_push($oldHand, $newDealerCard);
            $dealerHand->setCards($oldHand);
            $dealerHand->setValue($newValue);
            $em->persist($dealerHand);
            $em->flush();
            if ($dealerHand->getValue() > 16) {
                $lastCard = true;
            }
        }

        $data = [];
        if ($dealerHand->getValue() > 21) {
            if ($playerValue > 21) {
                $dealerHand->setStatus("draw");
                $playerHand->setStatus("draw");
                $data["result"] = "draw";
                $playerBet->setWinnings(2 * $playerBet->getBetAmmount());
            } else {
                if ($playerHand->getStatus() == "blackjack") {
                    $playerBet->setWinnings(2.5 * $playerBet->getBetAmmount());
                }
                $dealerHand->setStatus("lose");
                $playerHand->setStatus("win");
                $data["result"] = "win";
            }
        } elseif ($dealerHand->getValue() < 21) {
            if ($playerValue > 21) {
                $dealerHand->setStatus("win");
                $playerHand->setStatus("lose");
                $data["result"] = "lose";
                $playerBet->setWinnings(0);
            } elseif ($playerValue > $dealerHand->getValue()) {
                $dealerHand->setStatus("lose");
                $playerHand->setStatus("win");
                $data["result"] = "win";
                $playerBet->setWinnings(2 * $playerBet->getBetAmmount());
            } else {
                $dealerHand->setStatus("win");
                $playerHand->setStatus("lose");
                $data["result"] = "lose";
                $playerBet->setWinnings(0);
            }
        } elseif ($dealerHand->getValue() == 21 && $playerValue != 21) {
            $dealerHand->setStatus("win");
            $playerHand->setStatus("lose");
            $data["result"] = "lose";
            $playerBet->setWinnings(0);
        }

        $em->persist($dealerHand);
        $em->persist($playerHand);
        $em->flush();
        $em->persist($playerBet);
        $em->flush();
        $oldBalance = $user->getBalance();
        $user->setBalance($oldBalance + $playerBet->getWinnings());
        if ($oldBalance + $playerBet->getWinnings() == 0) {
            $user->setBalance(1);
        }
        $em->persist($user);
        $em->flush();
        $data["cards"] = $dealerHand->getCards();

        return $this->json($data);
    }
}
