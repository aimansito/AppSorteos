<?php

namespace App\Form;

use App\Entity\Participante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ParticipantePublicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Campos básicos para el alta pública
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
            ])
            ->add('codigoEntrada', TextType::class, [
                'label' => 'Código de Entrada',
            ])
            // Casilla de aceptación de términos (no mapeada, validación obligatoria)
            ->add('aceptarTerminos', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Acepto los <a href="/terminos" target="_blank" rel="noopener">términos y condiciones</a>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'row_attr' => [
                    'class' => 'form-check mb-3 terms-box'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Debes aceptar los términos y condiciones.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participante::class,
        ]);
    }
}