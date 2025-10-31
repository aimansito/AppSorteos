<?php

namespace App\Form;

use App\Entity\Sorteo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Formulario de administración de sorteos.
 * Define los campos principales del sorteo y reglas de validación.
 *
 * Notas:
 * - imagenFile: campo no mapeado, restringido a JPG/PNG y tamaño máximo.
 * - participantesIlimitados: controla el uso de maxParticipantes.
 */
class SorteoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreActividad')
            ->add('fecha', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha del sorteo',
                'model_timezone' => 'Europe/Madrid',
                'view_timezone' => 'Europe/Madrid',
                'html5' => true,
            ])
            ->add('lugar')
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('participantesIlimitados', CheckboxType::class, [
                'label' => 'Participantes Ilimitados',
                'required' => false,
            ])
            ->add('numeroGanadores', IntegerType::class, [
                'label' => 'Número de ganadores',
                'required' => true,
                'attr' => [
                    'min' => 1
                ]
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
