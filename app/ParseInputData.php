<?php

declare(strict_types=1);

namespace App;

use Carbon\Carbon;
use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class parseInputData
{
    private string $file;
    private bool $isFileExists = false;
    private bool $isFileTypeCorrect = false;
    private bool $isClientTypeCorrect = false;
    private bool $isOperationTypeCorrect = false;
    private bool $isCurrencyTypeCorrect = false;
    private array $allowedFileTypes = ['text/csv'];

    private array $parsedData;


    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function checkFileAvailability(): object
    {
        if (file_exists($this->file)) {
            $this->isFileExists = true;
        } else {
            throw new FileNotFoundException();
            // throw new NotFoundResourceException("File not exists, please check the file path is correct or not.");
        }
        return $this;
    }

    public function checkFileType(): bool|array
    {
        if ($this->isFileExists) {
            $mime = mime_content_type($this->file);

            if ($mime && in_array($mime, $this->allowedFileTypes))
                $this->isFileTypeCorrect = true;
            else
                throw new Exception("File format is not correct, should be in csv format.");
        }

        return $this->isFileTypeCorrect;
    }

    public function checkClientType(string $clientType): bool
    {
        if ($clientType != 'private' && $clientType != 'business') {
            throw new NotFoundResourceException("Invalid user's type '$clientType'. User type should be one of 'private' or 'business'");
        } else {
            $this->isClientTypeCorrect = true;
        }

        return $this->isClientTypeCorrect;
    }

    public function checkOperationType(string $operationTtype): bool
    {
        if ($operationTtype != 'deposit' && $operationTtype != 'withdraw') {
            throw new NotFoundResourceException("Invalid operation type '$operationTtype'. Operation type should be one of 'deposit' or 'withdraw'");
        } else {
            $this->isOperationTypeCorrect = true;
        }

        return $this->isOperationTypeCorrect;
    }

    public function checkOperationCurrency(string $currency): bool
    {
        if ($currency != 'EUR' && $currency != 'USD' && $currency != 'JPY') {
            throw new NotFoundResourceException("Invalid currency '$currency' provided. Currency should be one of 'EUR', 'USD', 'JPY'");
        } else {
            $this->isCurrencyTypeCorrect = true;
        }

        return $this->isCurrencyTypeCorrect;
    }

    public function getParsedData(): array
    {

        $this->checkFileAvailability();
        $this->checkFileType();

        $handle = fopen($this->file, "r");

        if ($handle !== FALSE) {
            $row = 1;
            while (($data = fgetcsv($handle, null, ",")) !== FALSE) {

                // Check user/client type is correct in the
                $this->checkClientType($data[2]);

                // Check operation type is correct in the
                $this->isOperationTypeCorrect = $this->checkOperationType($data[3]);


                // Check currency
                $this->isCurrencyTypeCorrect = $this->checkOperationCurrency($data[5]);


                $this->parsedData[] = [
                    'date' => Carbon::parse($data[0])->format('Y-m-d'),
                    'week' => Carbon::parse($data[0])->startOfWeek(Carbon::MONDAY)->endOfWeek(Carbon::SUNDAY)->week(),
                    'day' => Carbon::parse($data[0])->weekday(),
                    'user_id' => $data[1],
                    'user_type' => $data[2],
                    'operation_type' => $data[3],
                    'amount' => (float) $data[4],
                    'currency' => $data[5]
                ];


                $row++;
            }
            fclose($handle);
        }

        return $this->parsedData;
    }
}
