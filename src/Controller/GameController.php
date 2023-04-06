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
    public function play(Request $request, TableRepository $tableRepository, GameRepository $gameRepository): Response
    {
        $table = $tableRepository->find($request->query->get('table'));
        $game = $gameRepository->find($request->query->get('game'));

        return $this->render('game/play.html.twig', [
            'game' => $game, 'table' => $table,
        ]);
    }

    #[Route('/game/deal', name: 'deal_game')]
    public function deal(Request $request, HandRepository $handRepository, GameRepository $gameRepository, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $game = $gameRepository->find($request->query->get('game'));
        $playersHands = $handRepository->getPlayersHands($game->getId());
        $data = [];
        foreach ($playersHands as &$hand) {
            $newHand = [];
            $deck = $game->getCardDeck();
            array_push($newHand, $deck[0], $deck[1]);
            array_splice($deck, 0, 2);
            $game->replaceCardDeck($deck);
            $em->persist($game);
            $hand->setCards($newHand);
            $em->persist($hand);
            $em->flush();
            $id = $hand->getId();
            $data["$id"] = $newHand;
        }

        $dealerHand = new Hand();
        $dealerHand->setUser($userRepository->find(2));
        $dealerHand->setGame($game);
        $dealerHand->setCreatedAt(new DateTimeImmutable());
        $dealerHand->setUpdatedAt(new DateTimeImmutable());
        $em->persist($dealerHand);
        $em->flush();
        $deck = $game->getCardDeck();
        $newDealerHand = [];
        array_push($newDealerHand, $deck[0]);
        $dealerHand->setCards($newDealerHand);
        $em->persist($dealerHand);
        $data["dealer"] = $deck[0];
        array_splice($deck, 0, 1);
        $game->replaceCardDeck($deck);
        $em->persist($game);
        $em->flush();

        return $this->json($data);
    }
}
