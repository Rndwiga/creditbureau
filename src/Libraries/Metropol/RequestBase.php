<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 11/8/18
 * Time: 9:54 AM
 */

namespace Rndwiga\CreditBureau\Libraries\Metropol;


use Carbon\Carbon;

class RequestBase
{
    protected $public_api_key;
    protected $private_api_key;
    protected $base_end_point;
    protected $port;
    protected $api_version;

    protected $id_number;
    protected $report_type;
    protected $loan_amount;
    protected $reference_no;

    public function setBaseEndPoint(string $base_end_point = "https://api.metropol.co.ke")
    {
        $this->base_end_point = $base_end_point;
        return $this;
    }

    public function setIdNumber(string $id_number)
    {
        $this->id_number = $id_number;
        return $this;
    }

    public function setLoanAmount(int $loan_amount)
    {
        $this->loan_amount = $loan_amount;
        return $this;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    public function setPrivateApiKey(string $private_api_key)
    {
        $this->private_api_key = $private_api_key;
        return $this;
    }

    public function setPublicApiKey(string $public_api_key)
    {
        $this->public_api_key = $public_api_key;
        return $this;
    }

    public function setReferenceNo(string $reference_no)
    {
        $this->reference_no = $reference_no;
        return $this;
    }

    public function setReportType(int $report_type)
    {
        $this->report_type = $report_type;
        return $this;
    }

    public function setApiVersion($api_version)
    {
        $this->api_version = $api_version;
        return $this;
    }

    protected function setHeaders($payload)
    {
        //calculate the timestamp as required e.g 2014 07 08 17 58 39 987843
        //Format: Year, Month, Day, Hour, Minute, Second, Milliseconds
        $now = Carbon::now('UTC');

        $apiTimestamp = $now->format('Y-m-d-H-i-s-u');
        $apiTimestamp = str_replace('-', '', $apiTimestamp);

        //calculate the rest api hash as required
        $stringHash = $this->private_api_key . trim(json_encode($payload)) . $this->public_api_key . $apiTimestamp;

        $apiHash = hash('sha256', $stringHash);

        $headers = [
            "X-METROPOL-REST-API-KEY:" . $this->public_api_key,
            "X-METROPOL-REST-API-HASH:" . $apiHash,
            "X-METROPOL-REST-API-TIMESTAMP:" . $apiTimestamp,
            "Content-Type:application/json"
        ];

        return array_values($headers);
    }


}
