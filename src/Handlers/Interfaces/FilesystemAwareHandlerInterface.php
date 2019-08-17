<?php

namespace Choccybiccy\TwitchBot\Handlers\Interfaces;

use League\Flysystem\FilesystemInterface;

/**
 * Interface FilesystemAwareHandlerInterface
 */
interface FilesystemAwareHandlerInterface
{
    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface;

    /**
     * @param FilesystemInterface $filesystem
     *
     * @return mixed
     */
    public function setFilesystem(FilesystemInterface $filesystem);
}