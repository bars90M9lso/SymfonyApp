<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailVerificationCodeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, 
            [
                'label' => 'form.EmailVerificationCodeFormType.label',
                'attr' => 
                [
                    'autocomplete' => 'one-time-code',
                    'inputmode' => 'numeric',
                ],
                'constraints' => 
                [
                    new NotBlank(message: 'form.EmailVerificationCodeFormType.not_blank', ),
                    new Length(
                        min: 1,
                        max: 99999,
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
        [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'email_verification_code_form',
        ]);
    }
}
