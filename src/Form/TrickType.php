<?php

namespace App\Form;

use App\Entity\Trick;
use App\Form\ImageLinkType;
use App\Form\VideoLinkType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', null, [
            'label' => 'trick_form.name',
            'attr' => [
                'class' => 'm-2',
            ],
        ])
            ->add('description')
            ->add('trickgroup', null, [
                'label' => 'trick_form.trick_group',
            ])
            ->add('imagelinks', CollectionType::class, [
                'entry_type' => ImageLinkType::class,
                // No label for the image links
                'entry_options' => ['label' => false],
                // allows the user to add as many image links as they want
                'allow_add' => true,
                // allows the user to delete as many image links as they want
                'allow_delete' => true,
                // delete empty
                'delete_empty' => true,
                // makes it so that there is no error message when the field is empty
                'required' => false,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'You must specify at least one element.',
                    ]),
                ],
                'label' => 'trick_form.image_link',
            ])
            ->add('videolinks', CollectionType::class, [
                'entry_type' => VideoLinkType::class,
                // No label for the image links
                'entry_options' => ['label' => false],
                // allows the user to add as many video links as they want
                'allow_add' => true,
                'allow_delete' => true,
                // delete empty
                'delete_empty' => true,
                'required' => false,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'You must specify at least one element.',
                    ]),
                ],
                'label' => 'trick_form.video_link',
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
