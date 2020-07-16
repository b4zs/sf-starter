<?php


namespace Core\ClassificationBundle\Twig;


class TextFiltersExtension extends \Twig_Extension
{

    public function getName()
    {
        return 'core_classification_text_filters';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('url_decode', 'urldecode'),
        );
    }
}