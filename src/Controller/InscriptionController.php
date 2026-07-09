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

final class InscriptionController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    #[Route('/inscription', name: 'app_inscription', methods: ['GET','POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {

        $employe = new Employe();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(InscriptionType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $employe->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $employe,
                    $form->get('password')->getData()
                )
            );
            $employe->setDateEntree(new \DateTimeImmutable());
            $employe->setStatut(ContratEmploye::CDI);
            $entityManager->persist($employe);
            $entityManager->flush();
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('inscription/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/accueil', name: 'app_accueil')]
    public function accueil(): Response
    {

        return $this->render('inscription/home.html.twig', [
            'title' => 'Accueil',
        ]);
    }

}
