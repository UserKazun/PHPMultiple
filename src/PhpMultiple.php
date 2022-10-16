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

    public function __construct()
    {
        if (!function_exists('pcntl_fork')) {
            throw new RuntimeException('This SAPI does not support pcntl functions.');
        }
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
        $shmKey = ftok(__FILE__, 't');
        $shmop = shmop_open($shmKey, "c", 0644, 1024);

        // Calculate the number of cases to be processed by a single child process.
        $dataCount = count($data);
        $singleProcJobNum = intval(ceil($dataCount / 2));

        $this->runChildProc($shmop, $data, $dataCount, $singleProcJobNum, $executor);

        $this->readDataFromsharedMemoryBlock($shmop, 0, 1024);
    }

    /**
     * Fork and run Closure in a child process
     *
     * @param Shmop $shmop
     * @param array $data
     * @param int $dataCount
     * @param int $singleProcJobNum
     * @param Closure $executor
     * @return void
     */
    public function runChildProc(Shmop $shmop, array $data, int $dataCount, int $singleProcJobNum, Closure $executor): void
    {
        $pid = pcntl_fork();
        $result = [];
        if ($pid !== self::IS_CHILD_PROC) {
             $result = $executor($this->copyDataToProcessedByChildProc($data, 1, $singleProcJobNum, $dataCount));
        }

        $this->writeToSharedMemoryBlocks($shmop, $result, 0);

        exit;
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
    public function copyDataToProcessedByChildProc(array $data, int $i, int $singleProcJobNum, int $dataCount): array
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
     * Write to share memory block.
     *
     * @param Shmop $shmop
     * @param string $writeTarget
     * @param integer $writeOffset
     * @return integer
     */
    public function writeToSharedMemoryBlocks(Shmop $shmop, string $writeTarget, int $writeOffset): int
    {
        return shmop_write($shmop, $writeTarget, $writeOffset);
    }

    /**
     * Read data in shared memory block.
     *
     * @param Shmop $shmop
     * @param int $offset
     * @param int $size
     * @return string
     */
    public function readDataFromSharedMemoryBlock(Shmop $shmop, int $offset, int $size): string
    {
        return shmop_read($shmop, $offset, $size);
    }

    /**
     * Delete shared memory block.
     *
     * @param Shmop $shmop
     * @return boolean
     */
    public function deleteSharedMemotyBlocks(Shmop $shmop): bool
    {
        return shmop_delete($shmop);
    }
}
