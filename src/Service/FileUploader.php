<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileUploader
{

    public function __construct(private readonly string $targetDirectory)
    {

    }

    public function upload(UploadedFile $file): string
    {
        $fileName = uniqid() . 'Services' .$file->guessExtension();
        try {
            $file->move($this->getTargetDirectory(),$fileName);
        } catch (FileException $e) {
            die($e->getCode().'-'.$e->getMessage());
        }
        return  $fileName;
    }

    public function getTargetDirectory():string {
//        return '/public/uploads/images/wish';
        return $this->targetDirectory;
    }

    public function delete (?string $fileName, string $rep):void {
        if(null!=$fileName) {
            if (file_exists($rep.'/'.$fileName)) {
                unlink($rep.'/'.$fileName);
            }
        }
    }

}