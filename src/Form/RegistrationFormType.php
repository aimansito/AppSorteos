<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $builder
    ->add('username', null, [
        'label' => 'Nombre de usuario',
    ])
    ->add('agreeTerms', CheckboxType::class, [
        'mapped' => false,
        'label' => 'Acepto los términos y condiciones',
        'constraints' => [
            new IsTrue([
                'message' => 'Debes aceptar los términos.',
            ]),
        ],
    ])
    ->add('plainPassword', PasswordType::class, [
        'mapped' => false,
        'label' => 'Contraseña',
        'attr' => ['autocomplete' => 'new-password'],
        'constraints' => [
            new NotBlank([
                'message' => 'Por favor, introduce una contraseña',
            ]),
            new Length([
                'min' => 6,
                'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres',
                'max' => 4096,
            ]),
        ],
    ]);
   

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
