<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projet;
use App\Repository\ProjetRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

}
