<?php

require __DIR__ . "/index.php";

use App\ParseInputData;
use App\Commission;

try {
    $time = (isset($argv[2]) && $argv[2] === '-t') ? true : false;

    $et = -hrtime(true);

    if (count($argv) == 1) {
        echo "Please provide a file path as first argument.";
        return;
    }

    $inputFile = new ParseInputData($argv[1]);
    $operations = $inputFile->getParsedData();

    $newComm = new Commission;

    $finalCommission = $newComm->setOperations($operations);

    $et += hrtime(true);
    echo implode("\n", $finalCommission);
    echo ($time) ? "\n--------------------------- Execution Time: " . $et / 1e+6 . " milliseconds" : '';
} catch (\Exception $ex) {
    echo $ex->getMessage();
    return;
}
