<?php

declare(strict_types=1);

namespace PHPMultiple;

use Shmop;

class PhpMultiple
{
    protected Shmop $shmId;

    /**
     * @param int $sharedMemorySize
     */
    public function __construct(int $sharedMemorySize)
    {
        $shmKey = ftok(__FILE__, 't');
        $this->shmId = shmop_open($shmKey, "c", 0644, $sharedMemorySize);
    }

    /**
     *
     *
     * @return void
     */
    public function setSharedMemoryBlocks(): void
    {
        $shmKey = ftok(__FILE__, 't');
        $this->shmId = shmop_open($shmKey, "c", 0644, 100);
    }

    /**
     * Undocumented function
     *
     * @param string $writeTarget
     * @param integer $writeOffset
     * @return int
     */
    public function writeToSharedMemoryBlocks(string $writeTarget, int $writeOffset)
    {
        return shmop_write($this->shmId, $writeTarget, $writeOffset);
    }

    /**
     * @param int $memorySize
     *
     * @return int
     */
    // public function calculateSharedMemoryBlocks(int $memorySize)
    // {
    //     $shmKey = ftok(__FILE__, 't');
    //     $shmId = shmop_open($shmKey, "c", 0644, $memorySize);
    //     $sharedMemBlocks = shmop_size($shmId);

    //     return $sharedMemBlocks;
    // }

    public function closeSharedMemotyBlocks()
    {
        return shmop_delete($this->shmId);
    }
}
