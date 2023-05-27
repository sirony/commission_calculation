<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Commission;
use PHPUnit\Framework\TestCase;

class CommissionTest extends TestCase
{

    public function testCommissionOperaton()
    {
        $commission = new Commission;

        $operations = [
            'date' => '2014-12-31',
            'user_id' => 1,
            'user_type' => 'private',
            'operation_type' => 'withdraw',
            'amount' => (float) 1200.00,
            'currency' => 'EUR'
        ];

        $result = $commission->setOperations($operations);

        $this->assertEquals((float) 0.6, $result);
    }

    public function testGetDecimalRoundValue()
    {
        $commission = new Commission;

        // $commission->commissionDecimalPlace = 2;
        $result = $commission->getDecimalRoundValue(0.023);

        $this->assertEquals(0.03, $result);
    }
}
