<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

final class ProjetController extends AbstractController
{
    #[Route('/', name: 'app_projet_index')]
    public function index(ProjetRepository $repository): Response
    {
        // On ne récupère que les projets qui ne sont pas archivés et on tri dans l'ordre des plus récents
        $projets = $repository->findBy(['archive' => false], ['id' => 'DESC']);

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/projet/{id}', name: 'app_projet_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Projet $projet): Response
    {

        // Si le projet n'existe pas en base de données
        if (!$projet) {
            throw new NotFoundHttpException("Le projet demandé n'existe pas.");
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/projet/ajout', name: 'app_projet_add', methods: ['GET','POST'])]
    #[Route('/projet/{id}/edit', name: 'app_projet_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Projet $projet, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création de l'objet Projet et du formulaire associé
        if (null === $projet) {
            $projet = new Projet();
            $projet->setArchive(0); // Par défaut, un projet n'est pas archivé à sa création
        }

        $form = $this->createForm(ProjetType::class, $projet);

        // Gestion de la soumission du formulaire et redirection vers la HP si tout est OK
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        // Affichage de la vue du formulaire
        return $this->render('projet/new.html.twig', [
            'form' => $form,
        ]);
    }


}
