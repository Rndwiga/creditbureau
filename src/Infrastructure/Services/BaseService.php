<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 10/11/18
 * Time: 4:27 PM
 */

namespace Rndwiga\CreditBureau\Infrastructure\Services;

use Rndwiga\Toolbox\Infrastructure\Services\AppJsonManager;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

class BaseService
{
    private function validateResponse(array $requestResponse):array {

        (new AppLogger('crbPaymentRequests','payment_processing'))->logInfo([
            'where'=>'BaseService->postToService',
            'requestResponse'=>$requestResponse,
        ]);

        if ($requestResponse['status'] == 'fail'){
            return [];
        }elseif ($requestResponse['status'] == 'success'){
            $jsonTest = AppJsonManager::validateJsonData($requestResponse['data']);
            if ($jsonTest['status'] == 'success'){
                return json_decode($requestResponse['data'],true);
            }else{
                return [];
            }
        }else{
            return [];
        }
    }

    protected function postToService(string $endpointUrl, array $payloadData):array {

        if (empty($endpointUrl) || empty(trim($endpointUrl))){
            die("Please declare the url that needs to be accessed");
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpointUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_POSTFIELDS => json_encode($payloadData),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $this->validateResponse([
                'from' => '',
                'status' => 'fail',
                'data' => $err
            ]);
        } else {
            return $this->validateResponse([
                'from' => '',
                'status' => 'success',
                'data' => $response
            ]);
        }
    }
}
