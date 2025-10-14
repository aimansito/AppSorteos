<?php

namespace App\Form;

use App\Entity\Sorteo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SorteoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreActividad')
            ->add('fecha', null, [
                'widget' => 'single_text'
            ])
            ->add('lugar')
            ->add('participantesIlimitados', CheckboxType::class, [
                'label' => 'Participantes Ilimitados',
                'required' => false,
            ])
            ->add('maxParticipantes', IntegerType::class, [
                'label' => 'Máximo de participantes',
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'data-depends-on' => 'sorteo_participantesIlimitados',
                ]
            ])
            ->add('imagenFile', FileType::class, [
                'label' => 'Logo de la actividad (JPG o PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Por favor, sube un archivo de imagen válido (JPG o PNG)'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sorteo::class,
        ]);
    }
}
