<?php

declare(strict_types=1);

require '../vendor/autoload.php';

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

$data = generateArrayData();
$executor($data);

$phpMultiple = new PhpMultiple(1024);
$result =  $phpMultiple->run($data, $executor);

echo '実行後: ' . print_r($result, true) . PHP_EOL;

/**
 * @return array
 */
function generateArrayData()
{
    $data = [];
    for ($i = 0; $i < 100; $i++) {
        $data[] = $i;
    }

    echo '実行前: ' . print_r($data, true) . PHP_EOL;

    return $data;
}
