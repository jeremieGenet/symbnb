<?php

namespace App\Form;

use App\Entity\Ad;
use App\Entity\User;
use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text', // single_text permet un affichage d'un simple text pour un champ de type DateType
                'label' => 'Date de fin de la réservation'
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin de la réservation'
            ])
            ->add('amount')
            ->add('comment', TextType::class, [
                'label' => "Commentaire de la réservation"
            ])
            // UTILISATION DE "EntityType" (booker, ici est une relation de type tableau complexe, donc pour aller chercher le "réservateur" il va falloir aider symfony)
            // il faut lui passer un tableau dans lequel on lui donne l'entité sur laquelle se baser, et choice_label pour en définir le label du champ.
            ->add('booker', EntityType::class, [
                'class' => User::class,
                'choice_label' =>'getFullName',
                'label' => 'Possesseur de la réservation'
            ])
            ->add('ad', EntityType::class, [
                'class' => Ad::class,
                'choice_label' =>'title',
                'label' => "Titre de l'annonce"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
