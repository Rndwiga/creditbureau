<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 11/8/18
 * Time: 10:16 AM
 */

namespace Rndwiga\CreditBureau\Libraries\Metropol;


use Carbon\Carbon;

class ApiConnect extends RequestBase
{
    public function apiHttpPost($endpoint, $payload){

        $url = $this->base_end_point . ":" . $this->port . '/' . $this->api_version . $endpoint;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            // CURLOPT_PORT => "8443",
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false, //turn this off when going live
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $this->setHeaders($payload),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $response = "cURL Error #:" . $err;
            return $response;
        } else {
            return $response;
        }
    }
}
