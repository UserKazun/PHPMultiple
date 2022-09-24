<?php

declare(strict_types=1);

use PHPMultiple\PhpMultiple;
use PHPUnit\Framework\TestCase;

class PhpMultipleTest extends TestCase
{
    public function testFirst(): void
    {
        $phpMultiple = new PHPMultiple();

        $this->assertTrue($phpMultiple->returnTrue());
    }

    public function testFail(): void
    {
        $this->Fail('This test is fail.');
    }
}
