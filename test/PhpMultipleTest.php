<?php

declare(strict_types=1);

use PHPMultiple\PhpMultiple;
use PHPUnit\Framework\TestCase;

class PhpMultipleTest extends TestCase
{
    protected PhpMultiple $phpMultiple;
    protected Shmop $shmop;

    public function setUp(): void
    {
        $this->phpMultiple = new PhpMultiple(1024);
        $shmKey = ftok(__FILE__, 't');
        $this->shmop = shmop_open($shmKey, "c", 0644, 1024);
    }

    public function tearDown(): void
    {
        if ($this->shmop) {
            $this->phpMultiple->deleteSharedMemotyBlocks($this->shmop);
        }
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

    public function testIsWriteToSharedMemoryBlocks()
    {
        $randStr = $this->generateRandomString();
        $this->assertSame(strlen($randStr), $this->phpMultiple->writeToSharedMemoryBlocks($this->shmop, $randStr, 0));
    }

    public function testIsReadSharedMemoryBlock()
    {
        $randStr = $this->generateRandomString();
        $size = $this->phpMultiple->writeToSharedMemoryBlocks($this->shmop, $randStr, 0);

        $this->assertSame($randStr, $this->phpMultiple->readDataFromSharedMemoryBlock($this->shmop, 0, $size));
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
