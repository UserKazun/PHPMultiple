<?php

declare(strict_types=1);

use PHPMultiple\PhpMultiple;
use PHPUnit\Framework\TestCase;

class PhpMultipleTest extends TestCase
{
    protected PhpMultiple $phpMultiple;
    protected Shmop $shmId;

    public function setUp(): void
    {
        $this->phpMultiple = new PhpMultiple(1024);
        $shmKey = ftok(__FILE__, 't');
        $this->shmId = shmop_open($shmKey, "c", 0644, 100);
    }

    public function tearDown(): void
    {
        $this->phpMultiple->deleteSharedMemotyBlocks();
    }

    /**
     * @dataProvider targetDataProvider
     */
    public function testCopyDataToBeProcessedByTheChildProcess(array $data, int $i, int $singleProcJobNum, int $dataCount, array $expected)
    {
        $holdChildProcData = $this->phpMultiple->copyDataToProcessedByChildProc($data, $i, $singleProcJobNum, $dataCount);

        $this->assertSame($expected, $holdChildProcData);
    }

    /**
     * array $data, int $i, int $singleProcJobNum, int $dataCount, array $expected)
     */
    public function targetDataProvider()
    {
        return [
            'simple array' => [[1, 2, 3, 4, 5, 6], 0, 3, 6, [1, 2, 3]],
            'multi array' => [[[1],[2], [3], [4], [5], [6]], 0, 3, 6, [[1], [2], [3]]],
            'array with key value' => [
                ['key' => 'value', 'key2' => 'value2', 'key3' => 'value3', 'key4' => 'value4'],
                0,
                2,
                4,
                ['key' => 'value', 'key2' => 'value2']
            ]
        ];
    }

    // public function testIsSetSharedMemoryBlocks()
    // {
    //     $this->assertNotFalse($this->phpMultiple->setSharedMemoryBlocks());
    // }

    public function testIsWriteToSharedMemoryBlocks()
    {
        $randStr = $this->generateRandomString();
        $this->assertSame(strlen($randStr), $this->phpMultiple->writeToSharedMemoryBlocks($randStr, 0));
    }

    public function testIsReadSharedMemoryBlock()
    {
        $randStr = $this->generateRandomString();
        $this->phpMultiple->writeToSharedMemoryBlocks($randStr, 0);

        $this->assertSame(strlen($randStr), $this->phpMultiple->readDataFromSharedMemoryBlock($this->shmId, 0, 100));
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    private function generateRandomString()
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';

        return substr(str_shuffle(str_repeat($str, 10)), 0, 1024);
    }
}
