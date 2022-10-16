<?php

declare(strict_types=1);

namespace PHPMultiple;

use Closure;
use RuntimeException;
use Shmop;

class PhpMultiple
{
    const IS_CHILD_PROC = 0;

    protected int $numberOfChildProc;
    private Shmop $shmId;

    /**
     * @param int $sharedMemorySize
     */
    public function __construct(int $sharedMemorySize)
    {
        if (!function_exists('pcntl_fork')) {
            throw new RuntimeException('This SAPI does not support pcntl functions.');
        }

        $shmKey = ftok(__FILE__, 't');
        $this->shmId = shmop_open($shmKey, "c", 0644, $sharedMemorySize);
    }

    /**
     * execute
     *
     * @param array $data
     * @param Closure $executor
     * @return void
     */
    public function run(array $data, Closure $executor)
    {
        // Calculate the number of cases to be processed by a single child process.
        $dataCount = count($data);
        $singleProcJobNum = intval(ceil($dataCount / 1));

        return $this->runChildProc($data, $dataCount, $executor);
    }

    /**
     * Fork and run Closure in a child process
     *
     * @param Closure $executor
     * @return array
     */
    public function runChildProc(array $data, int $dataCount, Closure $executor)
    {
        $pid = pcntl_fork();
        $result = [];
        if ($pid !== self::IS_CHILD_PROC) {
             $result = $executor($this->copyDataToProcessedByChildProc($data, 0, 1, $dataCount));
        }

        return $result;
    }

    /**
     * Copy data to be processed by the child process.
     *
     * @param array $data
     * @param int $i
     * @param int $singleProcJobNum
     * @param int $dataCount
     * @return array
     */
    public function copyDataToProcessedByChildProc(array $data, int $i, int $singleProcJobNum, int $dataCount)
    {
        $limit = 0;
        $offset = $i * $singleProcJobNum;
        if($offset + $singleProcJobNum >= $dataCount) {
            $limit = $dataCount - $offset;
        } else {
            $limit = $singleProcJobNum;
        }

        return array_slice($data, $offset, $limit);
    }

    /**
     * Allocation of shared memory blocks.
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
     * @param int $writeOffset
     * @return int
     */
    public function writeToSharedMemoryBlocks(string $writeTarget, int $writeOffset)
    {
        return shmop_write($this->shmId, $writeTarget, $writeOffset);
    }

    /**
     * Read data in shared memory block.
     *
     * @param Shmop $shmop
     * @param int $offset
     * @param int $size
     * @return string
     */
    public function readDataFromsharedMemoryBlock(Shmop $shmop, int $offset, int $size)
    {
        return shmop_read($shmop, $offset, $size);
    }

    /**
     * Delete shared memory block.
     *
     * @return void
     */
    public function deleteSharedMemotyBlocks()
    {
        return shmop_delete($this->shmId);
    }
}
