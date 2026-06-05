<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tache;
use App\Enum\StatutTache;
use App\Entity\Projet;
use App\Form\TacheType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function edit(?Tache $tache, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si la tâche n'existe pas en base de données
        if (!$tache) {
            throw new NotFoundHttpException("La tâche demandée n'existe pas.");
        }

        return $this->handleForm($tache, $request, $entityManager);
    }

    #[Route('/projet/{id}/tache/ajout/{default_status}', name: 'app_tache_add', requirements: ['id' => '\d+'], defaults: ['default_status' => null], methods: ['GET', 'POST'])]
    public function add(?Projet $projet, ?string $default_status, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si le projet n'existe pas en base de données
        if (!$projet) {
            throw new NotFoundHttpException("Le projet demandé n'existe pas.");
        }

        $tache = new Tache();
        $tache->setProjet($projet);

        // Si un statut par défaut est passé dans l'URL
        if ($default_status) {

            // On essaie de trouver le cas de l'Enum qui correspond à la chaîne (sensible à la casse)
            // Par exemple StatutTache::tryFrom('DOING')
            $enumStatus = StatutTache::tryFrom(strtolower($default_status));

            if ($enumStatus) {
                $tache->setStatut($enumStatus); // On injecte l'Enum directement dans l'objet !
            }
        }

        return $this->handleForm($tache, $request, $entityManager);
    }

    #[Route('/tache/{id}/supprimer', name: 'app_tache_remove', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function remove(?Tache $tache, EntityManagerInterface $manager): Response
    {

        // Si la tache n'existe pas en base de données on redirige vers la HP
        if (!$tache) {
            return $this->redirectToRoute('app_projet_index');
        }

        // On récupère l'ID du projet auquel la tâche est associée avant de supprimer la tâche pour pouvoir rediriger vers la page du projet après suppression
        $projetId = $tache->getProjet()->getId();

        // Suppression de la BD
        $manager->remove($tache);
        $manager->flush();

        // redirection vers la HP
        return $this->redirectToRoute('app_projet_show', ['id' => $projetId]);
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
            'tacheId' => $tache->getId(),
            'isEdit' => $tache->getId() !== null // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }

}
