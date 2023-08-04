<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\HandRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NavigationController extends AbstractController
{
    #[Route('/blackjack-home', name: 'home')]
    public function home(): Response
    {
        return $this->render('navigation/home.html.twig', []);
    }

    #[Route('/blackjack-rules', name: 'rules')]
    public function rules(): Response
    {
        return $this->render('navigation/rules.html.twig', []);
    }

    #[Route('/blackjack-admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('navigation/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/blackjack-ranking', name: 'ranking')]
    public function ranking(UserRepository $userRepository): Response
    {
        $ranking = $userRepository->orderByBalance();
        return $this->render('navigation/ranking.html.twig', [
            'users' => $ranking,
        ]);
    }
}
