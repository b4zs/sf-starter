<?php

namespace Application\MediaBundle\Entity;

use Core\ChunkUploadBundle\Model\MediaInterface;

class Media extends \Core\MediaBundle\Entity\BaseMedia implements MediaInterface
{
    /** @var int $id */
    protected $id;

    /** @var bool */
    private $isTmp;

    public function getId()
    {
        return $this->id;
    }

    public function getIsTmp()
    {
        return $this->isTmp;
    }

    public function setIsTmp($isTmp)
    {
        $this->isTmp = $isTmp;
    }
}
