<?php

namespace Core\MediaBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileUploadHandlerController extends Controller
{

    const
        STATUS_IN_PROGRESS  = 0,
        STATUS_DONE         = 1;

    public function uploadAction(Request $request){

        $cacheChunksFolder = $this->container->getParameter('kernel.cache_dir').'/uploads/chunks/';
        $cacheUploadFolder = $this->container->getParameter('kernel.cache_dir').'/uploads/';

        $targetPath = $cacheChunksFolder;
        $sessionID = $request->getSession()->getId();

        $responseStatus = -1;
        $completedFileName = null;

        $this->ensureDirectory($cacheChunksFolder);

        //remove everything that is older than 1 hour
        $this->removeOldFiles($cacheUploadFolder.'*');

        //get the file that should be uploaded
        $file = $this->getFile($request->files->all());

        if (empty($file)) {
            throw new \RuntimeException('No file found in request');
        }

        $fileName = $this->getFileName($file, $cacheUploadFolder, $sessionID);

//            $extension = substr($fileName, strrpos($fileName, '.'));
//            $fileNameWoExtension = str_replace($extension, '', $fileName);

        //todo validation

        //if content range is supported chunk upload, anyway normal upload
        if(isset($_SERVER['HTTP_CONTENT_RANGE'])){
            //chunk
            list($start, $end, $size) = $this->getChunkData();

            $targetFileName = $targetPath . $sessionID . '_' . $fileName . '.part';
            $tmpFileName = $file->getPathname();
            $mode = $start == 0 ? "wb" : "ab";
            $this->executeUpload($targetFileName, $mode, $tmpFileName);

            if ($end == $size - 1) {
                //move file out from chunks folder
                $completedFileName = $sessionID . '_' . $fileName; //used in the response also
                $completedFilePath = $cacheUploadFolder . $completedFileName;
                rename($targetFileName, $completedFilePath);

                $responseStatus = self::STATUS_DONE;
            } else {
                $responseStatus = self::STATUS_IN_PROGRESS;
            }
        }else{
            //normal
            $completedFileName = $sessionID . '_' . $fileName; //used in the response also
            $targetFileName = $cacheUploadFolder . $completedFileName;
            $tmpFileName = $file->getPathname();
            $mode = "wb";
            $this->executeUpload($targetFileName, $mode, $tmpFileName);

            $responseStatus = self::STATUS_DONE;
        }

        $response = $this->getResponse($responseStatus, $completedFileName, $file->getClientOriginalName());

        $response->headers->set('Content-Type', 'text/plain');
        return $response;
    }

    /**
     * @param $path
     */
    private function removeOldFiles($path)
    {
        $finder = new Finder();
        $finder->in($path);
        $finder->date('< now - 1 hour');
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->isWritable()) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * @param $targetFileName
     * @param string $targetFileMode
     * @param $tmpFileName
     * @param string $tmpFileMode
     */
    private function executeUpload($targetFileName, $targetFileMode = 'wb', $tmpFileName, $tmpFileMode = 'r')
    {
        $out = @fopen($targetFileName, $targetFileMode);
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = @fopen($tmpFileName, $tmpFileMode);

            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
                //Failed to open input stream.
                throw new RuntimeException('Failed to open input stream.');
            }
            @fclose($in);
            @fclose($out);
        } else {
            //Failed to open output stream.
            throw new RuntimeException('Failed to open output stream.');
        }
    }

    /**
     * @return array
     */
    private function getChunkData()
    {
        $content_range_header = $_SERVER['HTTP_CONTENT_RANGE'];
        $content_range = preg_split('/[^0-9]+/', $content_range_header);

        $start = $content_range[1];
        $end = $content_range[2];
        $size = $content_range[3];
        return array($start, $end, $size);
    }

    /**
     * @param $responseStatus
     * @param null $completedFilePath
     * @param null $fileName
     * @return JsonResponse
     * @throws \Exception
     */
    private function getResponse($responseStatus, $completedFilePath = null, $fileName = null)
    {
        $response = new JsonResponse();

        $responseContent = array();
        switch ($responseStatus):
            case self::STATUS_IN_PROGRESS:
                $responseContent = array(
                    "responseCode" => self::STATUS_IN_PROGRESS,
                    "completedFileName" => null,
                    "canonicalFileName" => null,
                );
                break;
            case self::STATUS_DONE:
                $responseContent = array(
                    "responseCode" => self::STATUS_DONE,
                    "completedFileName" => $completedFilePath,
                    "canonicalFileName" => $fileName,
                );
                break;
        endswitch;

        $response->setData($responseContent);
        return $response;
    }

    /**
     * @param $file
     * @param $cacheUploadFolder
     * @param $sessionID
     * @return mixed|string
     */
    private function getFileName($file, $cacheUploadFolder, $sessionID)
    {
        $fileName = preg_replace('/[^\w\._]+/', '_', $file->getClientOriginalName());

        //unique filename - to avoid to have the smae file overwritten for the same user
        $count = 1;
        while (file_exists($cacheUploadFolder . $sessionID . '_' . $count . '_' . $fileName)) {
            $count++;
        }
        $fileName = $count . '_' . $fileName;
        return $fileName;
    }

    /**
     * @param $files
     * @return UploadedFile
     */
    private function getFile($files){
        while(!($files instanceof UploadedFile) && (is_array($files) || $files instanceof \IteratorAggregate)){
            $files = current($files);
        }

        return $files;
    }

    protected function ensureDirectory($targetPath)
    {
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
    }

}