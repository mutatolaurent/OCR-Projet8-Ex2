<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Employe;
use App\Repository\EmployeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\InscriptionType;
use App\Enum\ContratEmploye;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AccesController extends AbstractController
{
    // Injection du service UserPasswordHasherInterface pour le hashage des mots de passe
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    // Route pour l'inscription des employés
    #[Route('/inscription', name: 'app_inscription', methods: ['GET','POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {

        $employe = new Employe();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(InscriptionType::class, $employe);
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Hashage du mot de passe et persistance de l'employé
            $employe->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $employe,
                    $form->get('password')->getData()
                )
            );
            // Définir la date d'entrée et le statut de l'employé
            $employe->setDateEntree(new \DateTimeImmutable());

            // Définir le statut de l'employé sur CDI
            $employe->setStatut(ContratEmploye::CDI);

            // Persister l'employé dans la base de données
            $entityManager->persist($employe);

            // Flush des changements dans la base de données
            $entityManager->flush();

            // Redirection vers la page d'accueil après l'inscription
            return $this->redirectToRoute('app_projet_index');
        }

        // Rendu du formulaire d'inscription
        return $this->render('acces/register.html.twig', [
            'form' => $form,
        ]);
    }

    // Route pour la page d'accueil
    #[Route('/accueil', name: 'app_accueil')]
    public function accueil(): Response
    {

        return $this->render('acces/home.html.twig', [
            'title' => 'Accueil',
        ]);
    }

}
