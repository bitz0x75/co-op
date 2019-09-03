<?php
namespace Osen\Coop;

use Osen\Coop\Bank;

class PesaLinkSendToAccount extends Bank
{
    public static function send($messageReference, $accountNumber, $amount, $transactionCurrency = 'KES', $narration = 'Payment', $destinations = array(), $callback = null)
    {
        $url   = parent::$host . '/FundsTransfer/Internal/2.0.0/SendToAccountt';
        $token = parent::token();

        $aDestinations = array();
        foreach ($destinations as $destination) {
            if (!isset($destination["ReferenceNumbenceNumber"])) {$destination["ReferenceNumbenceNumber"] = $messageReference;}
            if (!isset($destination["AccountNumber"])) {$destination["AccountNumber"] = self::$config->accountNumber;}
            if (!isset($destination["BankCode"])) {$destination["BankCode"] = self::$config->BankCode;}
            if (!isset($destination["BranchCode"])) {$destination["BranchCode"] = self::$config->BranchCode;}
            if (!isset($destination["Amount"])) {$destination["Amount"] = $amount;}
            if (!isset($destination["transactionCurrency"])) {$destination["transactionCurrency"] = self::$config->transactionCurrency;}
            if (!isset($destination["Narration"])) {$destination["Narration"] = $narration;}

            $aDestinations[] = $destination;
        }

        $requestPayload = array(
            "MessageReference" => $messageReference,
            "CallBackUrl"      => parent::$config->callback_url,
            "Source"           => array(
                "AccountNumber"       => self::$config->accountNumber,
                "Amount"              => $amount,
                "transactionCurrency" => self::$config->transactionCurrency,
                "Narration"           => $narration,
            ),
            "Destinations"     => $aDestinations,
        );

        $headers = array('Content-Type: application/json', "Authorization: Bearer {$token}");

        $process = curl_init();

        curl_setopt($process, CURLOPT_URL, $url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($requestPayload));
        curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

        $return   = curl_exec($process);
        $response = json_decode($return, true);

        return is_null($callback)
        ? $response
        : \call_user_func_array(array($callback), array($response));
    }
}
