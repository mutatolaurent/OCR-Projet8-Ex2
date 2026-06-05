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
    public function add(?Projet $projet, Request $request, EntityManagerInterface $entityManager): Response
    {

        $projet = new Projet();
        $projet->setArchive(0); // Par défaut, un projet n'est pas archivé à sa création

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        // VALIDATION ET ENREGISTREMENT
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        // Affichage de la vue du formulaire
        return $this->render('projet/new.html.twig', [
            'form' => $form,
            'isEdit' => false, // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }

    #[Route('/projet/{id}/edit', name: 'app_projet_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(?Projet $projet, Request $request, EntityManagerInterface $entityManager): Response
    {

        // Si on est sur la route /edit mais que l'ID ne correspond à rien
        if (null === $projet) {
            throw new NotFoundHttpException("Le projet demandé n'existe pas.");
        }

        // 1. CLONAGE DE SÉCURITÉ (Uniquement en mode édition)
        // On garde en mémoire la liste des employés AVANT la soumission du formulaire
        $employesAvantSoumission = [];

        // On crée un tableau figé des employés actuellement dans le projet
        $employesAvantSoumission = $projet->getEmployes()->toArray();

        // Création du formulaire et traitement de la requête
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        // 2. VÉRIFICATION DES RÈGLES MÉTIER APRÈS SOUMISSION
        if ($form->isSubmitted()) {

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
            'isEdit' => true, // Permet au template de savoir si on est en création ou en édition pour adapter le titre et le bouton du formulaire
        ]);
    }

    #[Route('/projet/{id}/archiver', name: 'app_projet_archive', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function archive(?Projet $projet, Request $request, EntityManagerInterface $manager): Response
    {

        // ---- ZONE DE TRAÇAGE ----
        // $logFile = __DIR__ . '/../../var/log/trace_archive.log'; // Chemin vers le dossier log de Symfony
        // $date = (new \DateTime())->format('Y-m-d H:i:s.u'); // Date avec microsecondes pour la précision

        // On récupère l'ID brut depuis l'URL (même si le projet n'existe pas)
        // $idRequis = $request->attributes->get('id');
        // $uriComplete = $request->getUri();
        // $userAgent = $request->headers->get('User-Agent');

        // $logMessage = sprintf(
        //     "[%s] Appel de la route pour l'ID: %s | URL: %s | Navigateur: %s\n",
        //     $date,
        //     $idRequis,
        //     $uriComplete,
        //     substr($userAgent, 0, 50) // On coupe pour que ce soit lisible
        // );

        // On écrit à la fin du fichier (FILE_APPEND)
        // file_put_contents($logFile, $logMessage, FILE_APPEND);
        // -------------------------

        // Si le projet n'existe pas en base de données on redirige vers la HP
        if (!$projet) {
            return $this->redirectToRoute('app_projet_index');
        }

        // Au lieu de supprimer, on modifie la propriété pour archiver
        $projet->setArchive(1);
        $manager->flush();

        // Petit message flash pour avertir l'utilisateur (optionnel mais recommandé !)
        // $this->addFlash('success', 'Le projet a bien été archivé.');

        // redirection vers la HP
        return $this->redirectToRoute('app_projet_index');
    }

}
