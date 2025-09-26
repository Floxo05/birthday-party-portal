<?php

declare(strict_types=1);

namespace App\Form\Party;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyResponseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accept', SubmitType::class, [
                'label' => 'Zusage',
                'attr' => ['class' => 'btn btn-secondary w-100 mb-2']
            ])
            ->add('accept_with_guests', SubmitType::class, [
                'label' => 'Zusage + 1',
                'attr' => ['class' => 'btn btn-secondary w-100 mb-2']
            ])
            ->add('decline', SubmitType::class, [
                'label' => 'Absage',
                'attr' => ['class' => 'btn btn-secondary w-100']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PartyResponseFormModel::class,
        ]);
    }
}


