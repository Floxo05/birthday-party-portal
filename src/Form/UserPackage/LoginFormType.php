<?php
declare(strict_types=1);

namespace App\Form\UserPackage;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Benutzername',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Benutzername']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Passwort',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Passwort']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Login',
                'attr' => ['class' => 'btn btn-primary w-100 mt-3']
            ]);
    }
}