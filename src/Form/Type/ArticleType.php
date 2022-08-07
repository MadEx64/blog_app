<?php

namespace App\Form\Type;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ArticleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', TextType::class)
      ->add('content', CKEditorType::class)
      ->add('cover', FileType::class, [
        // make it optional so you don't have to re-upload the cover file
        // every time you edit the article details
        'mapped' => false,
        'required' => false,
        'constraints' => [
          new Image([
            'maxSize' => '5M',
            'mimeTypes' => [
              'image/jpeg',
              'image/png',
              'image/webp'
            ],
            'mimeTypesMessage' => 'Veuillez sélectionner un fichier valide(jpg, png, webp)',
          ])
        ],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Article::class,
    ]);
  }
}
