<?php

namespace Choccybiccy\TwitchBot\Storage;

/**
 * Interface StorageInterface.
 */
interface StorageInterface
{
    /**
     * Read from storage.
     *
     * @param string $key
     * @return string
     */
    public function read(string $key): string;

    /**
     * Write to storage.
     *
     * @param string $key
     * @param mixed $data
     * @param boolean $overwrite
     * @return boolean
     */
    public function write(string $key, $data, bool $overwrite): bool;

    /**
     * Delete from storage.
     *
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool;
}
