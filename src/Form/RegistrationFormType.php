<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,
            [
                'constraints' => 
                [
                    new NotBlank(message: 'Пожалуйста, введите логин',),
                ],

            ])
            
            ->add('email', EmailType::class,
            [
                'attr' => ['autocomplete' => 'email'],
                'constraints' => 
                [
                    new NotBlank(message: 'Пожалуйста, введите email',),
                ],

            ])
            
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите пароль',),
                    new Length(
                        min: 4,
                        minMessage: 'Минимальная длина пароля: 4 символа',
                        max: 4096,
                    ),
                    //new PasswordStrength(),
                    //new NotCompromisedPassword(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true, 
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'registration_form',
        ]);
    }
}
