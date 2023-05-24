<?php

require __DIR__ . '/vendor/autoload.php';


use App\Commission;
use App\ParseInputData;
use App\Enums\ClientType;



// $rates = file_get_contents("https://developers.paysera.com/tasks/api/currency-exchange-rates");
// dd(json_decode($rates, true));

try {
    $time = (isset($argv[2]) && $argv[2] === '-t') ? true : false;

    $time_start = microtime($time);

    if (count($argv) == 1) {
        echo "Please provide a file path as first argument.";
        return;
    }

    $file = $argv[1];



    $inputFile = new ParseInputData($file);
    $operations = $inputFile->getParsedData();
    // dd($operations);

    // foreach ($operations as $transactoin) {
    $newComm = new Commission();
    // $val = $newComm->getDecimalRoundValue(2.023);
    // dd($val);
    $newComm->getCommission($operations);

    $time_end =  microtime($time);
    $execution_time_sec = ((float) $time_end - (float) $time_start);

    if ($time)
        echo "Time: " . number_format($execution_time_sec, 5) . " Sec ";
} catch (\Exception $ex) {
    echo $ex->getMessage();
    return;
}



// var_dump(new Commission(ClientType::PRIVATE));

// $today = \Carbon\Carbon::now();
// echo $today;
