<?php
namespace Law\RecaptchaBundle\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RecaptchaSubmitType extends AbstractType
{
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key) {
        $this->key = $key;
    }

    public function ConfigureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }

    public function buildview(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = false;
        $view->vars['key'] = $this->key;
        $view->vars['button'] = $options['label'];
    }

    public function getBlockPrefixe()
    {
        return 'recaptcha_submit';
    }

    public function getParent()
    {
        return TextType::class;
    }
}
