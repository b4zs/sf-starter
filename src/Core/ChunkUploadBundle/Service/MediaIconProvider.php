<?php


namespace Core\ChunkUploadBundle\Service;


class MediaIconProvider
{

    /** @var array */
    private $iconClasses;

    /** @var string */
    private $defaultClass;

    public function __construct(array $iconClasses, $defaultClass)
    {
        $this->iconClasses = $iconClasses;
        $this->defaultClass = $defaultClass;
    }

    public function getDefaultClass()
    {
        return $this->defaultClass;
    }

    public function getIcons()
    {
        return $this->getIcons();
    }

    public function getClassByExtension($extension = null)
    {
        return null !== $extension && isset($this->iconClasses[$extension]) ? $this->iconClasses[$extension] : $this->defaultClass;
    }

}
