<?php

namespace Paynl\Api\Transaction;

use Paynl\Error;
use Paynl\Helper;

/**
 * Api class to refund a transaction
 *
 * @author Andy Pieters <andy@pay.nl>
 */
class Refund extends Transaction
{
    protected $apiTokenRequired = true;

    protected $version = 15;

    /**
     * @var string the transactionId
     */
    private $transactionId;
    /**
     * @var int the amount in cents
     */
    private $amount;
    /**
     * @var string the description for this refund
     */
    private $description;
    /**
     * @var \DateTime the date the refund should take place
     */
    private $processDate;
    /**
     * @var array|null Array of products which should be refunded
     */
    private ?array $products = null;
    /**
     * @var int (optional) The vat percentage this refund applies to (AfterPay/Focum only)
     */
    private $vatPercentage;
    /**
     * @var int (optional) The currency in which the amount is specified. If no amount is specified, the full amount is refunded and currency is not used. Standard in euro.
     */
    private $currency;

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @param int|float|null $vatPercentage
     */
    public function setVatPercentage($vatPercentage)
    {
        $this->vatPercentage = $vatPercentage;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = (int)$amount;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param \DateTime $processDate
     */
    public function setProcessDate(\DateTime $processDate)
    {
        $this->processDate = $processDate;
    }

    public function setProducts(?array $products)
    {
        $this->products = $products;
    }

    /**
     * @inheritdoc
     * @throws Error\Required TransactionId is required
     */
    protected function getData()
    {
        if (empty($this->transactionId)) {
            throw new Error\Required('TransactionId is required');
        }

        $this->data['transactionId'] = $this->transactionId;

        if (!empty($this->amount)) {
            $this->data['amount'] = $this->amount;
        }
        if (!empty($this->description)) {
            $this->data['description'] = $this->description;
        }
        if ($this->processDate instanceof \DateTime) {
            $this->data['processDate'] = $this->processDate->format('d-m-Y');
        }
        if (!empty($this->vatPercentage)) {
            $this->data['vatPercentage'] = $this->vatPercentage;
        }
        if (!empty($this->currency)) {
            $this->data['currency'] = $this->currency;
        }
        if (!is_null($this->products)) {
            $this->data['products'] = $this->products;
        }

        return parent::getData();
    }

    /**
     * @param object|array $result
     *
     * @return array
     * @throws Error\Api
     */
    protected function processResult($result)
    {
        $output = Helper::objectToArray($result);

        if (!is_array($output)) {
            throw new Error\Api($output);
        }

        if (
            isset($output['request']) &&
            $output['request']['result'] != 1 &&
            $output['request']['result'] !== 'TRUE') {
            throw new Error\Api($output['request']['errorId'] . ' - ' . $output['request']['errorMessage']. ' '. (isset($output['description']) ? $output['description'] : ''));
        }

        return parent::processResult($result);
    }
    /**
     * @inheritdoc
     */
    public function doRequest($endpoint = null, $version = null)
    {
        return parent::doRequest('transaction/refund');
    }
}
