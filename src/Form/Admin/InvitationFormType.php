<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Form\Model\InvitationFormModel;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationFormType extends AbstractType
{
    public function __construct(
        private readonly PartyMemberRoleTranslatorInterface $roleTranslator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $party = $options['party'] ?? null;

        $defaultExpiration = new \DateTime('+7 days');

        if ($party instanceof Party)
        {
            $defaultExpiration = $party->getPartyDate();
        }

        $builder
            ->add('role', ChoiceType::class, [
                'choices' => [
                    $this->roleTranslator->translate(PartyMember::ROLE_GUEST) => PartyMember::ROLE_GUEST,
                    $this->roleTranslator->translate(PartyMember::ROLE_HOST) => PartyMember::ROLE_HOST,
                ],
                'label' => 'Rolle'
            ])
            ->add('maxUses', IntegerType::class, [
                'label' => 'Maximale Nutzungen',
                'data' => 1
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'Ablaufdatum der Einladung',
                'widget' => 'single_text',
                'data' => $defaultExpiration,
                'help' => 'Standard: Ablaufdatum der Party'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Einladung erstellen',
                'attr' => ['class' => 'btn btn-primary mt-4']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'party' => null,
            'data_class' => InvitationFormModel::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'invitation_form',
        ]);
    }
}
