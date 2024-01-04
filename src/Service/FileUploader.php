<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * Uploads a single file.
     */
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
            // Throw new Exception
        }

        return $fileName;
    }

    /**
     * Uploads multiple files.
     * 
     * @param UploadedFile[] $files
     * @return string[]
     */
    public function uploadMultiple(array $files): array
    {
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = $this->upload($file);
        }
        return $fileNames;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
