<?php


namespace Core\UtilityBundle\Rest;


use FOS\RestBundle\Inflector\InflectorInterface;

class NoopInflector implements InflectorInterface
{
    public function pluralize($word)
    {
        // Don't pluralize
        return $word;
    }
}
