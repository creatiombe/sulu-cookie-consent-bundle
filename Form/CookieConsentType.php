<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\SuluCookieConsentBundle\Form;

use Creatiom\SuluCookieConsentBundle\Cookie\CookieChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CookieConsentType extends AbstractType
{
    /**
     * @var CookieChecker
     */
    protected $cookieChecker;

    /**
     * @var array
     */
    protected $cookieCategories;

    /**
     * @var bool
     */
    protected $cookieConsentSimplified;

    public function __construct(
        CookieChecker $cookieChecker,
        array $cookieCategories,
        bool $cookieConsentSimplified = false
    ) {
        $this->cookieChecker           = $cookieChecker;
        $this->cookieCategories        = $cookieCategories;
        $this->cookieConsentSimplified = $cookieConsentSimplified;
    }

    /**
     * Build the cookie consent form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        if ($this->cookieConsentSimplified === false) {
            $builder->add('categories', CookieCategoriesType::class, ['label' => false]);
        }
        $builder->add('accept', SubmitType::class, ['label' => 'cookie_consent.accept', 'attr' => ['class' => 'cookie-consent__btn cookie-consent__btn_main']]);
        $builder->add('only_necessary', SubmitType::class, ['label' => 'cookie_consent.only_necessary', 'attr' => ['data-role' => 'only_necessary', 'class' => 'cookie-consent__btn cookie-consent__btn_necessary']]);
    }

    /**
     * Default options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'SuluCookieConsentBundle',
            'csrf_protection' => false,
        ]);
    }
}
