<?php

namespace App\Controller;

use App\Entity\Hand;
use App\Repository\GameRepository;
use App\Repository\HandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HandController extends AbstractController
{
    #[Route('/hand/create', name: 'app_hand')]
    public function create(Request $request, EntityManagerInterface $em, GameRepository $gameRepository): Response
    {

        return $this->render('hand/index.html.twig', [
            'controller_name' => 'HandController',
        ]);
    }

    #[Route('/hand/{id}/choose', name: 'choose_hand')]
    public function choose(Hand $hand, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $chosenValue = $request->query->get('choice');
        if ($chosenValue == "2") {
            $hand->setValue($hand->getValue() + 11);
        } else {
            $hand->setValue($hand->getValue() + 1);
        }

        $em->persist($hand);
        $em->flush();

        if ($hand->getValue() == 21) {
            $hand->setStatus("won");
        } elseif ($hand->getValue() > 21) {
            $hand->setStatus("bust");
        } else {
            $hand->setStatus("playing");
        }
        $em->persist($hand);
        $em->flush();
        $data["value"] = $hand->getValue();
        $data["aa"] = $chosenValue;
        return $this->json($data);
    }

    #[Route('/hand/{id}/hit', name: 'hit_hand')]
    public function hit(Hand $hand, EntityManagerInterface $em): JsonResponse
    {
        $game = $hand->getGame();
        $deck = $game->getCardDeck();
        $newHand = [$hand->getCards()];
        array_push($newHand, $deck[0]);
        array_splice($deck, 0, 1);
        $game->replaceCardDeck($deck);
        $em->persist($game);
        $em->flush();
        $hand->setStatus("playing");
        $hand->setCards($newHand);

        $em->persist($hand);
        $em->flush();

        $newValue = $hand->calculateHitValue();
        if ($newValue[0] == 21) {
            $hand->setValue($newValue[0]);
            $hand->setStatus("won");
        } elseif ($newValue[0] > 21) {
            $hand->setValue($newValue[0]);
            $hand->setStatus("bust");
        } elseif ($newValue[1] === "choosing") {
            $hand->setStatus("choosing");
        } else {
            $hand->setValue($newValue[0]);
            $hand->setStatus("playing");
        }

        $em->persist($hand);
        $em->flush();
        $data["card"] = end($newHand);
        $data["value"] = $hand->getValue();
        $data["status"] = $hand->getStatus();
        return $this->json($data);
    }
}
