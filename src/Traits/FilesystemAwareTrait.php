<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 17:19
 */

namespace Mindy\Orm\Traits;

use League\Flysystem\FilesystemInterface;
use function Mindy\app;

/**
 * Class FilesystemAwareTrait
 * @package Mindy\Orm\Traits
 */
trait FilesystemAwareTrait
{
    /**
     * @var string
     */
    public $filesystemName = 'default';
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;
    /**
     * @var mixed
     */
    protected $storage;

    /**
     * @param string $name
     */
    public function setFilesystemName(string $name)
    {
        $this->filesystemName = $name;
    }

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem() : FilesystemInterface
    {
        if ($this->filesystem === null) {
            $this->filesystem = $this->getStorage()->getFilesystem($this->filesystemName);
        }
        return $this->filesystem;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            $this->storage = app()->storage;
        }
        return $this->storage;
    }
}