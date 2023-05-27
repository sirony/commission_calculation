<?php

declare(strict_types=1);

namespace App;

use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ParseInputData
{
    /**
     * The operation file path
     */
    private string $file;

    /**
     * Weather file is exist in the path
     */
    private bool $isFileExists = false;

    /**
     * Weather file mime type is as expectation (text/csv)
     */
    private bool $isFileTypeCorrect = false;

    /**
     * Weather user/client type is one of the 'private' or 'business'
     */
    private bool $isClientTypeCorrect = false;

    /**
     * The operation type is one of the 'diposit' or 'withdraw'
     */
    private bool $isOperationTypeCorrect = false;

    /**
     * The currency is correct or not
     */
    private bool $isCurrencyTypeCorrect = false;

    /**
     * Allowd input file type
     */
    private array $allowedFileTypes = ['text/csv'];

    /**
     * allowed currencies
     */
    private array $allowedCurrency = ['EUR', 'USD', 'JPY'];

    /**
     * allowed user type
     */
    private array $allowedUserType = ['private', 'business'];


    /**
     * allowed operation type
     */
    private array $allowedOperationType = ['deposit', 'withdraw'];

    /**
     * Final perse data
     */
    private array $parsedData;


    public function __construct(string|null $file = '')
    {
        $this->file = $file;
    }

    /**
     * Check the input file path is correct or not
     */
    public function checkFileAvailability(): object
    {
        if (file_exists($this->file)) {
            $this->isFileExists = true;
        } else {
            throw new FileNotFoundException();
        }
        return $this;
    }

    /**
     * Check the file mime type is as expected
     * @return bool
     */
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

    /**
     * Check client type is as expected or not.
     * @param string $clientType
     * @return bool
     */
    public function checkClientType(string $clientType): bool
    {
        if (!in_array($clientType, $this->allowedUserType)) {
            throw new NotFoundResourceException("Invalid user's type '$clientType'. User type should be one of 'private' or 'business'");
        } else {
            $this->isClientTypeCorrect = true;
        }

        return $this->isClientTypeCorrect;
    }

    /**
     * Check operation type is correct or not.
     * @param string $operationType
     * @return bool
     */
    public function checkOperationType(string $operationType): bool
    {
        if (!in_array($operationType, $this->allowedOperationType)) {
            throw new NotFoundResourceException("Invalid operation type '$operationType'. Operation type should be one of 'deposit' or 'withdraw'");
        } else {
            $this->isOperationTypeCorrect = true;
        }

        return $this->isOperationTypeCorrect;
    }

    /**
     * Check operation currency is correct or not.
     * @param string $currency
     * @return bool
     */
    public function checkOperationCurrency(string $currency): bool
    {
        $allowedCurrency = $this->allowedCurrency;

        if (!in_array($currency, $allowedCurrency)) {
            throw new NotFoundResourceException("Invalid currency '$currency' provided. Currency should be one of " . implode(", ", $allowedCurrency));
        } else {
            $this->isCurrencyTypeCorrect = true;
        }

        return $this->isCurrencyTypeCorrect;
    }

    /**
     * Parse input file data
     * @return array
     */
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
                    'date' => $data[0],
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
