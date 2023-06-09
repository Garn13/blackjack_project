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
    #[Route('/', name: 'home')]
    public function home(UserRepository $userRepository): Response
    {
        return $this->render('navigation/home.html.twig', []);
    }

    #[Route('/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('navigation/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/ranking', name: 'ranking')]
    public function ranking(UserRepository $userRepository): Response
    {
        $ranking = $userRepository->orderByBalance();
        return $this->render('navigation/ranking.html.twig', [
            'users' => $ranking,
        ]);
    }
}
