<?php

declare(strict_types=1);

namespace PHPMultiple;

use Closure;
use RuntimeException;
use Shmop;

class PhpMultiple
{
    protected int $numberOfChildProc;
    protected Shmop $shmId;

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
     * @param array|Object $targetData
     * @param Closure $executor
     * @return void
     */
    public function run(array $data, Closure $executor)
    {
        // Calculate the number of cases to be processed by a single child process.
        $dataCount = count($data);
        $singleProcJobNum = intval(ceil($dataCount / $this->numberOfChildProc));

        for ($i = 0; $i <= $this->numberOfChildProc; $i++) {
            $pid = pcntl_fork();

            $holdChildProcData = $this->copyDataToProcessedByChildProc($data, $i, $singleProcJobNum, $dataCount);

            $this->writeToSharedMemoryBlocks(implode(',', $holdChildProcData), 0);
            $this->waitChildProcess();
        }
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

    public function waitChildProcess()
    {
        // 終了した子プロセスのプロセスIDを受け取る
        $pid = pcntl_wait(null);
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
