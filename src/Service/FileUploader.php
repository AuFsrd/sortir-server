<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileUploader
{

    public function __construct(private readonly string $targetDirectory,
                                private readonly string $csvTargetDirectory)
    {

    }

    public function upload(UploadedFile $file, string $type): string
    {
        $fileName = ($type==='img'?uniqid().'-':'') . $file->getClientOriginalName();
        try {
            $file->move($this->getTargetDirectory($type),$fileName);
        } catch (FileException $e) {
            die($e->getCode().'-'.$e->getMessage());
        }
        return  $fileName;
    }


    public function getTargetDirectory(string $type):string {
//        return '/public/uploads/images/wish';
        if($type==='img') {
            return $this->targetDirectory;
        } else {
            return $this->csvTargetDirectory;
        }

    }

    public function delete (?string $fileName, string $rep):void {
        if(null!=$fileName) {
            if (file_exists($rep.'/'.$fileName)) {
                unlink($rep.'/'.$fileName);
            }
        }
    }

}