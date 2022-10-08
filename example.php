<?php

declare(strict_types=1);

use PHPMultiple\PhpMultiple;

$executor = function($data) {
    $result = [];
    foreach($data as $value) {
        $result[] = $value;

        // Wait 100ms.
        // It simulate long processing time task.
        $wait = 100000;
        usleep($wait);
    }
    return $result;
};

$executor($generateArrayData());

$phpMultiple = new PhpMultiple(1024);
$phpMultiple->run($data, $executor);

/**
 * @return array
 */
function generateArrayData()
{
    $data = [];
    for ($i = 0; $i < 100; $i++) {
        $data[] = $i;
    }

    return $data;
}
