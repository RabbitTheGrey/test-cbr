<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Required;

class CbrRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_start', DateType::class, [
                'constraints' => [
                    new Required(),
                ],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false
            ])
            ->add('date_end', DateType::class, [
                'constraints' => [
                    new Required(),
                ],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'type' => null,
        ]);
    }
}
