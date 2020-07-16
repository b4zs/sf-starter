<?php


namespace Core\ChunkUploadBundle\Model;


interface MediaInterface extends \Sonata\MediaBundle\Model\MediaInterface
{
    /**
     * @param bool $bool
     * @return MediaInterface
     */
    public function setIsTmp($bool);

    /**
     * @return boolean
     */
    public function getIsTmp();

}
