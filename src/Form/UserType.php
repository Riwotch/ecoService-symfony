<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name',null, [
                'label' => 'Prénom'
            ])
            ->add('last_name',null, [
                'label' => 'Nom'
            ])
            ->add('mail',null, [
                'label' => 'Email'
            ])
            ->add('address1',null, [
                'label' => 'Adresse 1'
            ])
            ->add('address2',null, [
                'label' => 'Adresse 2'
            ])
            ->add('city',null, [
                'label' => 'Ville'
            ])
            ->add('zip_code',null, [
                'label' => 'Code postal'
            ])
            ->add('country',null, [
                'label' => 'Pays'
            ])
            ->add('phone',null, [
                'label' => 'Téléphone'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
