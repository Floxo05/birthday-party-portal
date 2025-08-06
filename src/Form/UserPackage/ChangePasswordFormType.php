<?php

declare(strict_types=1);

namespace App\Form\UserPackage;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Aktuelles Passwort',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Aktuelles Passwort'],
                'constraints' => [
                    new NotBlank(['message' => 'Bitte geben Sie Ihr aktuelles Passwort ein.']),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
                'first_options' => [
                    'label' => 'Neues Passwort',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Neues Passwort'],
                ],
                'second_options' => [
                    'label' => 'Neues Passwort wiederholen',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Wiederholen'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Bitte geben Sie ein Passwort ein.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Das Passwort muss mindestens {{ limit }} Zeichen lang sein.',
                    ]),
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_WEAK,
                        'message' => 'Passwort ist nicht stark genug.',
                    ]),
                ],
            ]);
    }
}