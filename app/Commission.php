<?php

declare(strict_types=1);

namespace App;

use App\Enums\ClientType;
use PhpParser\Node\Expr\Cast\Double;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Commission
{
    private array $operation = [];
    private array $transactoins = [];

    private float $dipositCommission = 0.03 / 100;
    private float $businessWithdrawCommission = 0.5 / 100;
    private float $privateWithdrawCommission = 0.3 / 100;

    private float $freeWithdraAmount = 1000.00;
    private int $perWeekFreeWithdraw = 3;
    private int $commissionDecimalPlace = 2;

    private array $exchangeRateData = [];

    private string $clientType;
    private string $operationType;
    private float $operationAmount;
    private float $operationCurrency;

    private float $commission = 0.0;

    public function __construct()
    {
    }


    /**
     * Set operation data to calculate commission.
     * @param array single|multi dimentional
     * @return float|array final commission
     */
    public function setOperations(array $operations)
    {
        $finalCommision = [];

        if (count($operations) == count($operations, COUNT_RECURSIVE)) {
            $this->operation = $operations;
            $finalCommision[] = $this->calculateCommission();
        } else {
            foreach ($operations as $operation) {
                $this->operation = $operation;
                $finalCommision[] = $this->calculateCommission();
            }
        }

        return $finalCommision;
    }

    public function calculateCommission()
    {
        $operation = $this->operation;

        $operationtype = $operation['operation_type'];
        $user_type = $operation['user_type'];

        switch ($operationtype) {
            case 'deposit':
                $chargeable_amount = $operation['amount'];
                $commission = $chargeable_amount * $this->dipositCommission;
                break;

            case 'withdraw':
                if ($user_type == 'business') {
                    $chargeable_amount = $operation['amount'];
                    $commission = $chargeable_amount * $this->businessWithdrawCommission;
                } elseif ($user_type == 'private') {
                    $commission = $this->calculatePrivateWithdraCommission();
                }
                break;

            default:
                throw new NotFoundResourceException('Invalid operation type!');
                break;
        }

        $commission = $this->getDecimalRoundValue($commission);
        return $commission;
    }

    public function calculatePrivateWithdraCommission(): float
    {
        $operation      = $this->operation;
        $transactoins   = $this->transactoins;
        $freeWithdraAmount = $this->freeWithdraAmount;
        $perWeekFreeWithdraw = $this->perWeekFreeWithdraw;

        $userId = $operation['user_id'];

        // If currency is not EUR then need to convert
        if ($operation['currency'] != 'EUR') {
            $exchangeDate = $this->getExchangeRate($operation['currency'], $operation['amount']);
            $amount = $exchangeDate[0];
            $exchangeRate = $exchangeDate[1];
        } else {
            $amount = $operation['amount'];
            $exchangeRate = 1;
        }

        // if ($operation['currency'] == 'USD') {
        //     $exchangeRate = 1.1497;
        //     $amount = $operation['amount'] / $exchangeRate;
        // } else if ($operation['currency'] == 'JPY') {
        //     $exchangeRate = 129.53;
        //     $amount = $operation['amount'] / $exchangeRate;
        // } else {
        //     $exchangeRate = 1;
        //     $amount = $operation['amount'];
        // }

        if (!isset($transactoins[$userId])) {
            $transactoins[$userId] = [
                'week' => $operation['week'],
                'count' => 1,
                'amount' => $amount,
                'total_amount' => $amount,
                'this_week_charged' => false
            ];
        } else {
            if ($transactoins[$userId]['week'] == $operation['week']) {
                $transactoins[$userId]['count']++;
                $transactoins[$userId]['amount'] = $amount;
                $transactoins[$userId]['total_amount'] = ($transactoins[$userId]['total_amount'] + $amount);
            } else {
                $transactoins[$userId]['week'] = $operation['week'];
                $transactoins[$userId]['count'] = 1;
                $transactoins[$userId]['amount'] = $amount;
                $transactoins[$userId]['total_amount'] = $amount;
                $transactoins[$userId]['this_week_charged'] = false;
            }
        }

        if ($transactoins[$userId]['count'] == 1) {
            if ($transactoins[$userId]['amount'] <= $freeWithdraAmount) {
                $chargeable_amount = 0;
            } else {
                $chargeable_amount = $transactoins[$userId]['amount'] - $freeWithdraAmount;
                $transactoins[$userId]['this_week_charged'] = true;
            }
        } else if ($transactoins[$userId]['count'] > 1 && $transactoins[$userId]['count'] <= $perWeekFreeWithdraw) {
            if ($transactoins[$userId]['total_amount'] > $freeWithdraAmount && $transactoins[$userId]['this_week_charged']) {
                $chargeable_amount = $transactoins[$userId]['amount'];
                $transactoins[$userId]['this_week_charged'] = true;
            } else if ($transactoins[$userId]['total_amount'] > $freeWithdraAmount && !$transactoins[$userId]['this_week_charged']) {
                $chargeable_amount = $transactoins[$userId]['total_amount'] - $transactoins[$userId]['amount'];
                $transactoins[$userId]['this_week_charged'] = true;
            } else {
                $chargeable_amount =  0;
            }
        } else if ($transactoins[$userId]['count'] > $perWeekFreeWithdraw) {
            $chargeable_amount = $transactoins[$userId]['amount'];
        }


        $commission = $chargeable_amount * $this->privateWithdrawCommission;

        $this->transactoins = $transactoins;

        return $commission * $exchangeRate;
    }


    public function getExchangeRate(string $operationCurrency, float $operationAmount): array
    {
        $exchangeAmount = 0;

        if (empty($this->exchangeRateData)) {
            $apiRates = file_get_contents("https://developers.paysera.com/tasks/api/currency-exchange-rates");
            $apiRates = !empty($apiRates) ? json_decode($apiRates, true) : [];
            $this->exchangeRateData = $apiRates;
        }

        $rates = $this->exchangeRateData;

        if (is_array($rates) && isset($rates['rates'][$operationCurrency])) {

            $exchangeRate = $rates['rates'][$operationCurrency];

            $exchangeAmount = $operationAmount / (float) $exchangeRate;
        } else {
            $exchangeAmount = $operationAmount;
            $exchangeRate = 1;
        }

        return [$exchangeAmount, $exchangeRate];
    }

    public function getDecimalRoundValue(float $number)
    {
        echo "number = $number \n";
        $finalCommision = 0;

        $precision = $this->commissionDecimalPlace;

        $pow = pow(10, $precision);

        $finalCommision = (ceil($pow * $number) + ceil($pow * $number - ceil($pow * $number))) / $pow;

        return sprintf("%0.2f", $finalCommision);
    }
}
