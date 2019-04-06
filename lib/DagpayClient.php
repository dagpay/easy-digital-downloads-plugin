<?php

class DagpayClient
{
    private $curl;
    private $environment_id;
    private $user_id;
    private $secret;
    private $test;
    private $platform;
    private $curlFactory;

    public function __construct(
        $environment_id,
        $user_id,
        $secret,
        $mode,
        $platform
    ) {
        $this->environment_id = $environment_id;
        $this->user_id = $user_id;
        $this->secret = $secret;
        $this->test = $mode;
        $this->platform = $platform;
    }

    private function getRandomString($length)
    {
        return strtoupper(
            bin2hex(
                random_bytes(
                    ceil($length / 2)
                )
            )
        );
    }

    private function getSignature($tokens)
    {
        return hash_hmac(
            "sha512",
            implode(":", $tokens),
            $this->secret
        );
    }

    private function getCreateInvoiceSignature($info)
    {
        return $this->getSignature([
            $info["currencyAmount"],
            $info["currency"],
            $info["description"],
            $info["data"],
            $info["userId"],
            $info["paymentId"],
            $info["date"],
            $info["nonce"]
        ]);
    }

    public function getInvoiceInfoSignature($info)
    {
        return $this->getSignature([
            $info->id,
            $info->userId,
            $info->environmentId,
            $info->coinAmount,
            $info->currencyAmount,
            $info->currency,
            $info->description,
            $info->data,
            $info->paymentId,
            $info->qrCodeUrl,
            $info->paymentUrl,
            $info->state,
            $info->createdDate,
            $info->updatedDate,
            $info->expiryDate,
            $info->validForSeconds,
            $info->statusDelivered ? "true" : "false",
            $info->statusDeliveryAttempts,
            $info->statusLastAttemptDate !== null ? $info->statusLastAttemptDate : "",
            $info->statusDeliveredDate !== null ? $info->statusDeliveredDate : "",
            $info->date,
            $info->nonce
        ]);
    }

    public function createInvoice($id, $currency, $total)
    {
        $invoice = [
            "userId" => $this->user_id,
            "environmentId" => $this->environment_id,
            "currencyAmount" => (float) $total,
            "currency" => $currency,
            "description" => "Dagcoin Payment Gateway invoice",
            "data" => "Order",
            "paymentId" => (string) $id,
            "date" => date('c'),
            "nonce" => $this->getRandomString(32)
        ];

        $signature = $this->getCreateInvoiceSignature($invoice);
        $create_invoice_request_info = $invoice;
        $create_invoice_request_info["signature"] = $signature;

        $result = $this->makeRequest('POST', 'invoices', $create_invoice_request_info);

        return $result;
    }

    public function getInvoiceInfo($id)
    {
        $result = $this->makeRequest('GET', 'invoices/' . $id);
        return $result;
    }

    public function cancelInvoice($id)
    {
        $result = $this->makeRequest('POST', 'invoices/cancel', [
            "invoiceId" => $id
        ]);

        return $result;
    }

    private function initCurl()
    {
        if ($this->curl === null) {
            $this->curl = $this->curlFactory->create();
        }
    }

    private function makeRequest($method, $url, $data = [])
    {
        if ($this->platform === 'standalone') {
            $this->initCurl();
            if ($method == 'POST') {
                return json_decode($this->curl->post($this->getUrl() . $url, $data));
            } elseif ($method == 'GET') {
                return json_decode($this->curl->get($this->getUrl() . $url));
            }
        } elseif ('wordpress') {
            $data = json_encode($data);
            $request["headers"] = [ 'Content-Type' => 'application/json'];
            $response = null;

            if ($method == 'POST') {
                $request["body"] = $data;
                $response = wp_safe_remote_post($this->getUrl() . $url, $request);
            } elseif ($method == 'GET') {
                $response = wp_safe_remote_get($this->getUrl() . $url, $request);
            }

            $data = json_decode(wp_remote_retrieve_body($response));
            if (!$data->success) {
                throw new \Exception($data->error ? $data->error : "Something went wrong! Please try again later or contact our support...");
            }

            return $data->payload;
        }
    }

    private function getUrl()
    {
        return $this->test ? 'https://test-api.dagpay.io/api/' : 'https://api.dagpay.io/api/';
    }
}
