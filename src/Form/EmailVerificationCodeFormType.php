<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EmailVerificationCodeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Код подтверждения',
                'attr' => [
                    'autocomplete' => 'one-time-code',
                    'inputmode' => 'numeric',
                    'maxlength' => 6,
                    'placeholder' => 'Введите 6 цифр',
                ],
                'constraints' => [
                    new NotBlank(message: 'Пожалуйста, введите код подтверждения'),
                    new Length(
                        min: 6,
                        max: 6,
                        exactMessage: 'Код должен состоять из {{ limit }} цифр',
                    ),
                    new Regex(
                        pattern: '/^\d{6}$/',
                        message: 'Код должен содержать только цифры',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'email_verification_code_form',
        ]);
    }
}
