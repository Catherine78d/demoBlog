<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author')
            // 'article'correspond à la clé étrangère présente dans la table SQL 'comment'
            // Nous devons définir de quelle entité elle provient
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'title'
            ])
            ->add('content')
            ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
