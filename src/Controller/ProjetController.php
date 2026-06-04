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
use Symfony\Component\Form\FormError;

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

        // Si on est sur la route /edit mais que l'ID ne correspond à rien
        if ($request->attributes->get('_route') === 'app_projet_edit' && null === $projet) {
            throw new NotFoundHttpException("Le projet demandé n'existe pas.");
        }

        // Permet de savoir si on est en édition ou en création
        $isEdit = (null !== $projet);

        // Création de l'objet Projet et du formulaire associé
        if (null === $projet) {
            $projet = new Projet();
            $projet->setArchive(0); // Par défaut, un projet n'est pas archivé à sa création
        }

        // 1. CLONAGE DE SÉCURITÉ (Uniquement en mode édition)
        // On garde en mémoire la liste des employés AVANT la soumission du formulaire
        $employesAvantSoumission = [];
        if ($isEdit) {
            // On crée un tableau figé des employés actuellement dans le projet
            $employesAvantSoumission = $projet->getEmployes()->toArray();
        }

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        // 2. VÉRIFICATION DES RÈGLES MÉTIER APRÈS SOUMISSION
        if ($form->isSubmitted() && $isEdit) {

            // On récupère la liste des employés qui vient d'être saisie dans le formulaire
            $employesApresSoumission = $projet->getEmployes()->toArray();

            // On cherche les employés qui ont été RETIRÉS (présents avant, mais plus après)
            foreach ($employesAvantSoumission as $employeId => $employe) {
                if (!in_array($employe, $employesApresSoumission, true)) {

                    // L'employé a été retiré, vérifions s'il a des tâches sur ce projet
                    foreach ($projet->getTaches() as $tache) {
                        if ($tache->getEmploye() === $employe) {
                            // L'employé est encore assigné à au moins une tâche de ce projet !
                            // On ajoute une erreur globale au formulaire
                            $form->addError(new FormError(sprintf(
                                'Impossible de retirer l’employé "%s %s" car il est encore affecté à la tâche "%s" de ce projet.',
                                $employe->getPrenom(),
                                $employe->getNom(),
                                $tache->getTitre()
                            )));

                            break; // Inutile de vérifier les autres tâches pour cet employé
                        }
                    }
                }
            }
        }

        // 3. VALIDATION ET ENREGISTREMENT
        // Si la boucle au-dessus a ajouté un "FormError", $form->isValid() renverra automatiquement FALSE !
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        // Affichage de la vue du formulaire
        return $this->render('projet/new.html.twig', [
            'form' => $form,
            'isEdit' => $isEdit, // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }


}
