<?php

namespace App\Controller;

use App\Entity\Hand;
use App\Repository\GameRepository;
use App\Repository\HandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HandController extends AbstractController
{
    #[Route('/hand/create', name: 'app_hand')]
    public function create(Request $request, EntityManagerInterface $em, GameRepository $gameRepository): Response
    {
        $hand = new Hand();

        return $this->render('hand/index.html.twig', [
            'controller_name' => 'HandController',
        ]);
    }
}
