<?php

namespace App\Form;

use App\Entity\Participante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Formulario interno para gestionar datos básicos del participante.
 * Incluye nombre, email y código de entrada.
 */
class ParticipanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
            ])
            ->add('codigoEntrada', TextType::class, [
                'label' => 'Código de Entrada',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participante::class,
        ]);
    }
}
