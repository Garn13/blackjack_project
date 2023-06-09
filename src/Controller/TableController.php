<?php

namespace App\Controller;

use App\Entity\Table;
use DateTimeImmutable;
use App\Form\TableType;
use App\Repository\UserRepository;
use App\Repository\TableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TableController extends AbstractController
{
    #[Route('/table', name: 'app_table')]
    public function index(TableRepository $tableRepo, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($this->getUser());
        $tables = $tableRepo->findAll();
        return $this->render('table/index.html.twig', [
            'tables' => $tables, 'balance' => $user->getBalance(),
        ]);
    }

    #[Route('/table/create', name: 'create_table')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $table = new Table();
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $table->setCreatedAt(new DateTimeImmutable());
            $table->setUpdatedAt(new DateTimeImmutable());
            $em->persist($table);
            $em->flush();
            return $this->render('navigation/admin.html.twig');
        }
        return $this->render('table/create.html.twig', [
            'tableForm' => $form,
        ]);
    }

    #[Route('/table/edit/{id}', name: 'edit_table')]
    public function edit(Table $table, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $table->setName($table->getName());
            // $table->setMinBet($table->getMinBet());
            // $table->setMaxBet($table->getMaxBet());
            // $table->setNbDecks($table->getNbDecks());
            $table->setUpdatedAt(new DateTimeImmutable());
            $em->flush();
            return $this->redirectToRoute('app_table');
        }
        return $this->render('table/create.html.twig', [
            'tableForm' => $form,
        ]);
    }

    #[Route('/table/delete/{id}', name: 'delete_table')]
    public function delete(Table $table, TableRepository $tableRepo, EntityManagerInterface $em): Response
    {

        $em->remove($table);
        $em->flush();
        $tables = $tableRepo->findAll();
        return $this->render('table/index.html.twig', [
            'tables' => $tables,
        ]);
    }
}
