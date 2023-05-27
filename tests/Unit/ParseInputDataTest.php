<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\ParseInputData;
use PHPUnit\Framework\TestCase;

class ParseInputDataTest extends TestCase
{

    public function testCheckFileType()
    {
        $file = 'input.csv';
        $parseData = new ParseInputData($file);
        $parseData->checkFileAvailability();

        $result = $parseData->checkFileType();

        $this->assertTrue($result);
    }

    public function testCheckClientType()
    {
        $parseData = new ParseInputData;
        $this->assertTrue($parseData->checkClientType('private'));
        $this->assertTrue($parseData->checkClientType('business'));
    }

    public function testCheckOperationType()
    {
        $parseData = new ParseInputData;
        $this->assertTrue($parseData->checkOperationType('deposit'));
        $this->assertTrue($parseData->checkOperationType('withdraw'));
    }

    public function testCheckOperationCurrency()
    {
        $parseData = new ParseInputData;
        $this->assertTrue($parseData->checkOperationCurrency('USD'));
        $this->assertTrue($parseData->checkOperationCurrency('JPY'));
        $this->assertTrue($parseData->checkOperationCurrency('EUR'));
    }
}
