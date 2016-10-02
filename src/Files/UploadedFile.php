<?php

namespace Mindy\Orm\Files;

use GuzzleHttp\Psr7\UploadedFile as GuzzleUploadedFile;

/**
 * Class UploadedFile
 * @package Mindy\Storage
 */
class UploadedFile extends GuzzleUploadedFile
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var bool
     */
    protected $test;

    public function __toString()
    {
        return (string)$this->path;
    }

    /**
     * @param \Psr\Http\Message\StreamInterface|string|resource $streamOrFile
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct($streamOrFile, $size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        // Fix for FileValidator (is_uploaded_file validation)
        if (is_string($streamOrFile)) {
            $this->path = $streamOrFile;
        }
        parent::__construct($streamOrFile, (int)$size, (int)$errorStatus, $clientFilename, $clientMediaType);
    }

    /**
     * @return string
     */
    public function getPathname()
    {
        return $this->path;
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return bool True if the file has been uploaded with HTTP and no error occurred
     */
    public function isValid()
    {
        $isOk = $this->getError() === UPLOAD_ERR_OK;
        return $this->test ? $isOk : $isOk && is_string($this->path) && is_uploaded_file($this->path);
    }
}
