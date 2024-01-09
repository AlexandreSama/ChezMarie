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
     * The function takes an uploaded file, generates a safe filename, moves the file to a target
     * directory, and returns the filename.
     * 
     * @param UploadedFile file The "file" parameter is an instance of the UploadedFile class, which
     * represents a file that has been uploaded through a form. It contains information about the file,
     * such as its original name, size, and temporary location on the server.
     * 
     * @return string the file name of the uploaded file.
     */
    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new \Exception($e->getMessage());
        }

        return $fileName;
    }

    /**
     * The function "uploadMultiple" takes an array of files and uploads each file, returning an array
     * of the uploaded file names.
     * 
     * @param array files An array of files to be uploaded. Each file should be an instance of the
     * `SplFileInfo` class.
     * 
     * @return array an array of file names.
     */
    public function uploadMultiple(array $files): array
    {
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = $this->upload($file);
        }
        return $fileNames;
    }

    /**
     * The function "getTargetDirectory" returns the target directory as a string.
     * 
     * @return string a string value.
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
