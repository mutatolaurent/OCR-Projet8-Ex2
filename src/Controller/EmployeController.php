<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Employe;
use App\Repository\EmployeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\EmployeType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted('IS_AUTHENTICATED')]
final class EmployeController extends AbstractController
{
    // route pour la page d'accueil de la gestion des employés
    #[Route('/employe', name: 'app_employe')]
    public function index(EmployeRepository $repository): Response
    {

        // $employes = $repository->findAll();
        $employes = $repository->findBy([], ['prenom' => 'ASC']);

        return $this->render('employe/index.html.twig', [
            'employes' => $employes,
        ]);
    }

    // route pour la page d'édition d'un employé
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/employe/{id}/edit', name: 'app_employe_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(?Employe $employe, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si l'employé n'existe pas en base de données
        if (!$employe) {
            throw new NotFoundHttpException("L'employé demandé n'existe pas.");
        }

        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employe);
            $entityManager->flush();
            return $this->redirectToRoute('app_employe');
        }

        return $this->render('employe/edit.html.twig', [
            'form' => $form,
            'isEdit' => true, // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);

    }

    // route pour la page d'ajout d'un employé
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/employe/ajout', name: 'app_employe_add', methods: ['GET','POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {

        $employe = new Employe();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        // VALIDATION ET ENREGISTREMENT
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employe);
            $entityManager->flush();

            // Petit message flash pour avertir l'utilisateur
            $this->addFlash('success', 'L\'employé a bien été ajouté.');

            return $this->redirectToRoute('app_employe');
        }

        // Affichage de la vue du formulaire
        return $this->render('employe/edit.html.twig', [
            'form' => $form,
            'isEdit' => false, // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }

    // route pour la suppression d'un employé
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/employe/{id}/supprimer', name: 'app_employe_remove', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function remove(?Employe $employe, EntityManagerInterface $manager): Response
    {

        // Si l'employé n'existe pas en base de données on redirige vers la HP employé
        if (!$employe) {
            return $this->redirectToRoute('app_employe');
        }

        // Si l'employé est affecté à des tâches, il faut le retirer de ces tâches avant de pouvoir le supprimer
        foreach ($employe->getTaches() as $tache) {
            $tache->setEmploye(null);
        }

        // Si l'employé est affecté à des projets, il faut le retirer de ces projets avant de pouvoir le supprimer
        foreach ($employe->getProjets() as $projet) {
            $projet->removeEmploye($employe);
        }

        // Au lieu de supprimer, on modifie la propriété pour archiver
        $manager->remove($employe);
        $manager->flush();

        // Petit message flash pour avertir l'utilisateur
        $this->addFlash('success', 'L\'employé a bien été supprimé.');

        // redirection vers la HP
        return $this->redirectToRoute('app_employe');
    }

}
