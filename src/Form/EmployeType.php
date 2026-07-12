<?php

namespace App\Form;

use App\Entity\Employe;
use App\Entity\Projet;
use App\Enum\ContratEmploye;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', TextType::class)
            ->add('statut', EnumType::class, ['class' => ContratEmploye::class, 'choice_label' => function (ContratEmploye $choice) {
                return $choice->getLabel();
            },])
            ->add('dateEntree', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',

                // On définit les choix : 'Ce qui est affiché' => 'La valeur en BDD (le rôle Symfony)'
                'choices' => [
                    'Collaborateur' => 'ROLE_USER',
                    'Chef de projet' => 'ROLE_ADMIN',
                ],

                'expanded' => false, // C'est maintenant une liste déroulante classique !
                'multiple' => false,

            ])
        ;
        // LE DATA TRANSFORMER (Le traducteur magique entre le formulaire et la BDD)
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                // PHP vers Formulaire : On extrait le rôle du tableau pour l'afficher dans le select
                function (array $rolesAsArray): string {
                    return count($rolesAsArray) ? $rolesAsArray[0] : 'ROLE_USER';
                },
                // Formulaire vers PHP : On englobe la chaîne sélectionnée dans un tableau pour l'entité
                function (string $roleAsString): array {
                    return [$roleAsString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}
