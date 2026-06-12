<?php

namespace App\Form;

use App\Entity\Employe;
use App\Entity\Projet;
use App\Entity\Tache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Enum\StatutTache;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 1. On récupère le projet passé en option depuis le contrôleur
        $projet = $options['projet'];

        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['min' => '2020-01-01', 'max' => '2030-12-31'] // Ajoute des contraintes HTML5 pour la date mais pas côté serveur
            ])
            ->add('statut', EnumType::class, ['class' => StatutTache::class, 'choice_label' => function (StatutTache $choice) {
                return $choice->getLabel();
            },])
            // ->add('Projet', EntityType::class, [
            //     'class' => Projet::class,
            //     'choice_label' => 'id',
            // ])
            ->add('employe', EntityType::class, [
                'class' => Employe::class,
                // On utilise une fonction anonyme qui reçoit chaque objet Employe
                'choice_label' => function (Employe $employe) {
                    return $employe->getPrenom() . ' ' . $employe->getNom();
                },
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Choisir un employé (optionnel)',
                'query_builder' => function (EntityRepository $er) use ($projet) {
                    return $er->createQueryBuilder('e')
                        ->join('e.projets', 'p') // On fait une jointure avec la table des projets pour filtrer les employés associés au projet en cours
                        ->where('p.id = :projetId') // On ajoute une condition pour ne récupérer que les employés liés au projet passé en option
                        ->setParameter('projetId', $projet ? $projet->getId() : null) //
                        ->orderBy('e.prenom', 'ASC'); // Tri par nom d'employé
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'projet' => null,
        ]);

        // Optionnel : on peut restreindre le type attendu pour cette option
        $resolver->setAllowedTypes('projet', [Projet::class, 'null']);
    }
}
