<?php

declare(strict_types=1);

use PHPMultiple\PhpMultiple;
use PHPUnit\Framework\TestCase;

class PhpMultipleTest extends TestCase
{
    protected PhpMultiple $phpMultiple;
    protected Shmop $shmId;

    protected $faker;

    public function setUp(): void
    {
        $this->phpMultiple = new PhpMultiple(100);
    }

    public function tearDown(): void
    {
        $this->phpMultiple->closeSharedMemotyBlocks();
    }

    public function testIsSetSharedMemoryBlocks()
    {
        $this->assertNotFalse($this->phpMultiple->setSharedMemoryBlocks());
    }

    public function testIsWriteToSharedMemoryBlocks()
    {
        $randStr = $this->generateRandomString();
        $this->assertSame(strlen($randStr), $this->phpMultiple->writeToSharedMemoryBlocks($randStr, 0));
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    private function generateRandomString()
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';

        return substr(str_shuffle(str_repeat($str, 10)), 0, 8);
    }
}
