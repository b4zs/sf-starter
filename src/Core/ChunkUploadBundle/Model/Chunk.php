<?php


namespace Core\ChunkUploadBundle\Model;


class Chunk
{
    /** @var integer */
    private $start;

    /** @var integer */
    private $end;

    /**
     * @var integer
     * The total size of the whole file, not just the size of this chunk!
     */
    private $totalSize;

    /**
     * @param string $contentRangeHeader
     */
    public function __construct($contentRangeHeader)
    {
        $contentRange = preg_split('/[^0-9]+/', $contentRangeHeader);

        $this->start     = isset($contentRange[1]) ? $contentRange[1] : 0;
        $this->end       = isset($contentRange[2]) ? $contentRange[2] : 0;
        $this->totalSize = isset($contentRange[3]) ? $contentRange[3] : 0;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function isLast()
    {
        return $this->end >= $this->totalSize - 1;
    }

}
