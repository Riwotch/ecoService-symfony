<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address1', null, ['label' => 'Adresse *'])
            ->add('address2', null, [
                'label' => 'Complément d\'adresse',
                'required' => false
            ])
            ->add('city', null, ['label' => 'Ville *'])
            ->add('zip_code', NumberType::class, ['label' => 'Code Postal *'])
            ->add('country', null, ['label' => 'Pays *'])
            ->add('phone', NumberType::class,['label' => 'Téléphone', 'required' => false])
            ->add('mail', null, ['label' => 'Email *'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
