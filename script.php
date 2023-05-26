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

    $newComm = new Commission();

    // $number = $newComm->getDecimalRoundValue(0.6);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(3);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0.06);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(1.5);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0.69481973288041);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0.3);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0.3);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(3);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(0);
    // echo "$number \n";
    // $number = $newComm->getDecimalRoundValue(8611.41);
    // echo "$number \n-----------------";
    // exit;

    $finalCommission = $newComm->setOperations($operations);

    $et += hrtime(true);
    echo implode("\n", $finalCommission);
    echo ($time) ? "\n--------------------------- Execution Time: " . $et / 1e+6 . " milliseconds" : '';
} catch (\Exception $ex) {
    echo $ex->getMessage();
    return;
}
