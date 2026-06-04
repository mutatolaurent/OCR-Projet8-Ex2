<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tache;
use App\Entity\Projet;
use App\Form\TacheType;

final class TacheController extends AbstractController
{
    #[Route('/tache', name: 'app_tache')]
    public function index(): Response
    {
        return $this->render('tache/index.html.twig', [
            'controller_name' => 'TacheController',
        ]);
    }

    #[Route('/tache/{id}/edit', name: 'app_tache_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Tache $tache, Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->handleForm($tache, $request, $entityManager);
    }

    #[Route('/projet/{id}/tache/ajout', name: 'app_tache_add', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function add(Projet $projet, Request $request, EntityManagerInterface $entityManager): Response
    {
        $tache = new Tache();
        $tache->setProjet($projet);

        return $this->handleForm($tache, $request, $entityManager);
    }

    // LA MÉTHODE MUTUALISÉE POUR LE FORMULAIRE
    private function handleForm(Tache $tache, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $tache->getProjet() // On passe l'objet Projet récupéré par la route
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_show', ['id' => $tache->getProjet()->getId()]);
        }

        return $this->render('tache/new.html.twig', [
            'form' => $form,
            'projet' => $tache->getProjet(),
            'isEdit' => $tache->getId() !== null // Pratique pour le titre dans Twig
        ]);
    }

}
