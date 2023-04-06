<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\HandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NavigationController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(GameRepository $gameRepository, HandRepository $handRepository, EntityManagerInterface $em): Response
    {
        return $this->render('navigation/home.html.twig', [
            'controller_name' => 'NavigationController',
        ]);
    }

    #[Route('/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('navigation/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
