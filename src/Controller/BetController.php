<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Entity\Hand;
use DateTimeImmutable;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BetController extends AbstractController
{
    #[Route('/bet/create', name: 'create_bet')]
    public function create(Request $request, EntityManagerInterface $em, GameRepository $gameRepository): JsonResponse
    {
        $game = $gameRepository->find($request->query->get('game'));
        if ($game->getStatus() == "ongoing") {
            return $this->json("error");
        }
        $hand = new Hand();
        $hand->setGame($game);
        $hand->setUser($this->getUser());
        $hand->setCreatedAt(new DateTimeImmutable());
        $hand->setUpdatedAt(new DateTimeImmutable());
        $hand->setStatus("playing");
        $em->persist($hand);
        $em->flush();
        $bet = new Bet();
        $maxBet = $game->getGameTable()->getMaxBet();
        $bet->setBetAmmount($request->query->get('bet') > $maxBet ? $maxBet : $request->query->get('bet'));
        $bet->setHands($hand);
        $bet->setGame($game);
        $bet->setType("bet");
        $bet->setCreatedAt(new DateTimeImmutable());
        $bet->setUpdatedAt(new DateTimeImmutable());
        $em->persist($bet);
        $em->flush();
        $data = ['bet' => $bet->getId(), 'hand' => $hand->getId()];
        return $this->json($data);
    }
}
