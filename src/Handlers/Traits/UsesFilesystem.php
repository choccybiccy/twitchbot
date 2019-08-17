<?php

namespace Choccybiccy\TwitchBot\Handlers\Traits;

use League\Flysystem\FilesystemInterface;

/**
 * Trait UsesFilesystem.
 */
trait UsesFilesystem
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }
}
