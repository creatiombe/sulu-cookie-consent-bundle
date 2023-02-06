<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\Form;

use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CookieCategoriesType extends AbstractType
{

    private CookieChecker $cookieChecker;

    private array $cookieCategories;

    /**
     * @param CookieChecker $cookieChecker
     * @param array $cookieCategories
     */
    public function __construct(
        CookieChecker $cookieChecker,
        array $cookieCategories,
    ) {
        $this->cookieChecker           = $cookieChecker;
        $this->cookieCategories        = $cookieCategories;
    }

    /**
     * Build the cookie consent form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach($this->cookieCategories as $category) {
            $builder->add($category, CheckboxType::class, [
                'label' => 'cookie_consent.category.' . $category,
                'translation_domain' => false,
                'required' => false,
                'data' => $this->cookieChecker->hasCategoryCookieSet($category),
            ]);
        }
//        $builder->add('categories', ChoiceType::class, [
//            'choices' => $options['categories'],
//            'multiple' => true,
//            'expanded' => true,
//            'label' => 'cookie_consent.categories',
//            'choice_label' => function ($choiceValue, $key, $value) {
//                return 'cookie_consent.category.' . $key;
//            },
//            'choice_translation_domain' => false,
//        ]);

    }

    /**
     * Default options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'SuluCookieConsentBundle'
        ]);
    }
}
