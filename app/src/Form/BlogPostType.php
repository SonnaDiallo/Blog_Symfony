<?php

namespace App\Form;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Titre de l\'article']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-input',
                    'rows' => 10,
                    'placeholder' => 'Contenu de votre article...'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => 'Choisir une catégorie',
                'choices' => [
                    'Guide' => 'Guide',
                    'Conseils déco' => 'Conseils déco',
                    'Noël' => 'Noël',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-input']
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (URL)',
                'required' => false,
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'https://exemple.com/image.jpg'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}
