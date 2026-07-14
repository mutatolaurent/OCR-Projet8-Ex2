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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use App\Security\Voter\ProjetVoter;

#[isGranted('IS_AUTHENTICATED')]
final class TacheController extends AbstractController
{
    // Injection du service Security pour gérer les autorisations et récupérer l'utilisateur connecté
    public function __construct(private Security $security)
    {
    }

    // Route pour éditer une tâche spécifique
    #[Route('/tache/{id}/edit', name: 'app_tache_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(?Tache $tache, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si la tâche n'existe pas en base de données
        if (!$tache) {
            throw new NotFoundHttpException("La tâche demandée n'existe pas.");
        }

        // $this->denyAccessUnlessGrantedProjectEmploye($tache->getProjet());
        // On utilise le Voter de manière native
        $this->denyAccessUnlessGranted(ProjetVoter::ACCESS, $tache->getProjet(), "Vous n'êtes pas autorisé à accéder à ce projet.");

        return $this->handleForm($tache, $request, $entityManager);
    }

    // Route pour ajouter une tâche à un projet spécifique, avec un statut par défaut optionnel
    #[Route('/projet/{id}/tache/ajout/{default_status}', name: 'app_tache_add', requirements: ['id' => '\d+'], defaults: ['default_status' => null], methods: ['GET', 'POST'])]
    public function add(?Projet $projet, ?string $default_status, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si le projet n'existe pas en base de données
        if (!$projet) {
            throw new NotFoundHttpException("Le projet demandé n'existe pas.");
        }

        // $this->denyAccessUnlessGrantedProjectEmploye($projet);
        $this->denyAccessUnlessGranted(ProjetVoter::ACCESS, $projet, "Vous n'êtes pas autorisé à accéder à ce projet.");

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

    // Route pour supprimer une tâche spécifique
    #[Route('/tache/{id}/supprimer', name: 'app_tache_remove', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function remove(?Tache $tache, EntityManagerInterface $manager): Response
    {

        // Si la tache n'existe pas en base de données on redirige vers la HP
        if (!$tache) {
            return $this->redirectToRoute('app_projet_index');
        }

        // $this->denyAccessUnlessGrantedProjectEmploye($tache->getProjet());
        $this->denyAccessUnlessGranted(ProjetVoter::ACCESS, $tache->getProjet(), "Vous n'êtes pas autorisé à accéder à ce projet.");

        // On récupère l'ID du projet auquel la tâche est associée avant de supprimer la tâche pour pouvoir rediriger vers la page du projet après suppression
        $projetId = $tache->getProjet()->getId();

        // Suppression de la BD
        $manager->remove($tache);
        $manager->flush();

        // Petit message flash pour avertir l'utilisateur
        $this->addFlash('success', 'La tâche a bien été supprimée.');

        // redirection vers la HP
        return $this->redirectToRoute('app_projet_show', ['id' => $projetId]);
    }

    // LA MÉTHODE MUTUALISÉE POUR LE FORMULAIRE
    // Cette méthode est utilisée à la fois pour l'ajout et l'édition d'une tâche
    private function handleForm(Tache $tache, Request $request, EntityManagerInterface $entityManager): Response
    {
        $isEdit = $tache->getId() !== null;

        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $tache->getProjet() // On passe l'objet Projet récupéré par la route
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            // Petit message flash pour avertir l'utilisateur (optionnel mais recommandé !)
            $this->addFlash('success', 'La tâche a bien été '.($isEdit ? ' modifiée' : 'enregistrée').'.');

            return $this->redirectToRoute('app_projet_show', ['id' => $tache->getProjet()->getId()]);
        }

        return $this->render('tache/new.html.twig', [
            'form' => $form,
            'tacheId' => $tache->getId(),
            'isEdit' => $isEdit // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }

    private function denyAccessUnlessGrantedProjectEmploye(Projet $projet): void
    {
        // Si l'utilisateur est ADMIN, il a accès à tout
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Si l'utilisateur n'est pas ADMIN, on vérifie s'il est affecté au projet
        $currentUser = $this->security->getUser();

        if (!$currentUser || !$projet->getEmployes()->contains($currentUser)) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à ce projet.');
        }
    }
}
