<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,
                [
                    'constraints' =>[ new NotBlank(message: 'form.RegistrationFormType.login_not_blank', ), ],
                ]
            )

            ->add('email', EmailType::class,
                [
                    'attr' => ['autocomplete' => 'email'],
                    'constraints' =>
                    [
                        new NotBlank(message: 'form.RegistrationFormType.email_not_blank', ),
                        new Email(message: 'form.RegistrationFormType.email_invalid'),
                    ],
                ]
            )

            ->add('password', PasswordType::class, 
                [
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => 
                    [
                        new NotBlank(message: 'form.RegistrationFormType.password_not_blank', ),
                        new Length(
                            min: 4,
                            minMessage: 'form.RegistrationFormType.password_min_length',
                            max: 4096,
                        ),
                        //new PasswordStrength(),
                        //new NotCompromisedPassword(),
                    ],
                ]
            )
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
