<?php
namespace Virton;

use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Manages upload response and file storage.
 *
 * Class Upload
 * @package Virton
 */
class Upload
{
    /**
     * File storage path.
     * @var string|null
     */
    protected $path;

    /**
     * @var array
     */
    protected $formats = [];

    /**
     * Upload constructor.
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    /**
     * Uploads and store a file.
     * @param UploadedFileInterface $file
     * @param string|null $oldFile
     * @param null|string $filename
     * @return string|null
     */
    public function upload(UploadedFileInterface $file, ?string $oldFile = null, ?string $filename = null): ?string
    {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $this->delete($oldFile);
            $targetPath = $this->addCopySuffix(
                $this->path . DIRECTORY_SEPARATOR . ($filename ?: $file->getClientFilename())
            );
            $dirname = pathinfo($targetPath, PATHINFO_DIRNAME);
            if (!file_exists($dirname)) {
                $old = umask(0);
                mkdir($dirname, 0777, true);
                umask($old);
            }
            $file->moveTo($targetPath);
            $this->generateFormats($targetPath);
            return pathinfo($targetPath)['basename'];
        }
        return null;
    }

    /**
     * Deletes the old file.
     * @param string|null $oldFile
     * @return void
     */
    public function delete(?string $oldFile): void
    {
        if ($oldFile) {
            $oldFile = $this->path . DIRECTORY_SEPARATOR . $oldFile;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            foreach ($this->formats as $format => $_) {
                $oldFileWithFormat = $this->getPathWithSuffix($oldFile, $format);
                if (file_exists($oldFileWithFormat)) {
                    unlink($oldFileWithFormat);
                }
            }
        }
    }

    /**
     * @param string $targetPath
     * @return string
     */
    private function addCopySuffix(string $targetPath): string
    {
        if (file_exists($targetPath)) {
            return $this->addCopySuffix($this->getPathWithSuffix($targetPath, 'copy'));
        }
        return $targetPath;
    }

    /**
     * Retrieves the path with the format suffix.
     * @param string $path
     * @param string $suffix
     * @return string
     */
    private function getPathWithSuffix(string $path, string $suffix): string
    {
        $info = pathinfo($path);
        return $info['dirname'] . DIRECTORY_SEPARATOR .
            $info['filename'] . '_' . $suffix . '.' . $info['extension'];
    }

    /**
     * Generates a file according to the format.
     * @param $targetFormat
     * @return void
     */
    private function generateFormats($targetFormat): void
    {
        foreach ($this->formats as $format => $size) {
            $manager = new ImageManager(['driver' => 'gd']);
            $destination = $this->getPathWithSuffix($targetFormat, $format);
            [$width, $height] = $size;
            $manager->make($targetFormat)->fit($width, $height)->save($destination);
        }
    }
}
