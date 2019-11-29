<?php


namespace Rndwiga\CreditBureau\Infrastructure\Services;


use Rndwiga\CreditBureau\Libraries\Metropol\MakeCalls;
use Rndwiga\CreditBureau\Model\MetropolEnhancedCreditInfo;
use Rndwiga\Toolbox\Infrastructure\Services\AppJsonManager;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;
use Ramsey\Uuid\Uuid;

class MetropolApiProcessingService
{
    private $nationalIdNumber, $companyIdentifier, $reportType;
    public function getMetropol(): MakeCalls
    {
        return $this->metropol = new MakeCalls();
    }

    public function setNationalId(int $nationalIdNumber):MetropolApiProcessingService{
        $this->nationalIdNumber = $nationalIdNumber;
        return $this;
    }

    public function setCompanyIdentifier(string $companyIdentifier):MetropolApiProcessingService{
        $this->companyIdentifier = $companyIdentifier;
        return $this;
    }

    public function setReportType(int $reportType):MetropolApiProcessingService{
        $this->reportType = $reportType;
        return $this;
    }

    private function getAutoUploadingToServiceStatus(){
        if (empty(env('CREDIT_BUREAU_UPLOAD_TO_SERVICE'))){
            die("Please define :: CREDIT_BUREAU_UPLOAD_TO_SERVICE :: on the environment file");
        }
        return env('CREDIT_BUREAU_UPLOAD_TO_SERVICE');
    }

    public function fetchCorporateReportInJson(){
        //return (new ServicesTests())->testProcessedDataFlow();
        $result= $this->getMetropol()->setBaseEndPoint()
            ->setPublicApiKey(env('CREDIT_BUREAU_METROPOL_PUBLIC_KEY'))
            ->setPrivateApiKey(env('CREDIT_BUREAU_METROPOL_PRIVATE_KEY'))
            ->setPort(env('CREDIT_BUREAU_METROPOL_PORT_NUMBER'))
            ->setApiVersion(env('CREDIT_BUREAU_METROPOL_API_VERSION'))
            ->setIdNumber($this->companyIdentifier)
            ->setLoanAmount(500)->getReportJson($this->reportType,"005");

        (new AppLogger('crbReportGeneration','report_generation'))->logInfo([
            'where'=>'MetropolApiProcessingService->fetchCorporateReportInJson',
            'apiResponseData'=>$result,
            'params'=>[
                'identifier' => $this->companyIdentifier,
                'reportType' => $this->reportType,
                'identifierType' => "company",
            ],
        ]);


        return $this->finalizeProcessingApiRequest($result);

    }

    public function fetchIndividualReportInJson(){
        //return (new ServicesTests())->testProcessedDataFlow();
        $result= $this->getMetropol()->setBaseEndPoint()
            ->setPublicApiKey(env('CREDIT_BUREAU_METROPOL_PUBLIC_KEY'))
            ->setPrivateApiKey(env('CREDIT_BUREAU_METROPOL_PRIVATE_KEY'))
            ->setPort(env('CREDIT_BUREAU_METROPOL_PORT_NUMBER'))
            ->setApiVersion(env('CREDIT_BUREAU_METROPOL_API_VERSION'))
            ->setIdNumber($this->nationalIdNumber)
            ->setLoanAmount(500)->getReportJson($this->reportType);

        (new AppLogger('crbReportGeneration','report_generation'))->logInfo([
            'where'=>'MetropolApiProcessingService->fetchIndividualReportInJson',
            'apiResponseData'=>$result,
            'params'=>[
                'identifier' => $this->companyIdentifier,
                'reportType' => $this->reportType,
                'identifierType' => "individual",
            ],
        ]);

        return $this->finalizeProcessingApiRequest($result);
    }

    public function finalizeProcessingApiRequest(string $result){
        if (count($this->validateApiResponse($result)) > 0){

            $processedReport = $this->processedApiCallRequest(json_decode($result,true));

            if ($processedReport['status'] == 'success'){
                $saved = $this->saveAccessedCreditInfo($processedReport['data']);

                if ($this->getAutoUploadingToServiceStatus() == true){
                    //TODO:: You can make a single file were all services to be updated can be defined
                    $toCbsResponse = (new CbsService())->updateClientProfileOnMifos($saved['forCbsReporting']);
                }

                if (count($saved['computedReport']) > 0){
                    return [
                        'status' => 'success',
                        'message' => 'report accessed and analyzed successfully',
                        'data' => $saved['computedReport']
                    ];
                }else{
                    return [
                        'status' => 'fail',
                        'message' => isset($processedReport['data']['api_code_description'])?$processedReport['data']['api_code_description']:'There was an error in accessed report',
                        'data' => [
                            'reportRetrieved' => $result,
                            'processedInformation' => $processedReport,
                        ]
                    ];
                }

            }elseif ($processedReport['status'] == 'fail'){
                //TODO:; this report failed update and communicate so
                //TODO:: Update the DB about the failure
                return [
                    'status' => 'fail',
                    'message' => 'There was an error encountered during analysis of accessed report',
                    'data' => [
                        'reportRetrieved' => $result,
                        'processedInformation' => $processedReport,
                    ]
                ];
            }
        }else{
            //TODO:: The response was not a valid json, update as failure, communicate
            return [
                'status' => 'fail',
                'message' => 'The response received was not correct',
                'developerMessage' => 'The response received from Metropol was an invalid json',
                'data' => [
                    'reportRetrieved' => $result,
                ]
            ];
        }
    }

    private function validateApiResponse(string $apiRequest):array {
        $jsonTest = AppJsonManager::validateJsonData($apiRequest);
        if ($jsonTest['status'] == 'success'){
            return json_decode($apiRequest,true);
        }else{
            //TODO:: raise alarm here
            (new AppLogger('crbReportGeneration','invalid_json'))->logInfo([
                'where'=>'MetropolApiProcessingService->validateApiResponse::failure',
                'jsonReceived'=>$apiRequest,
            ]);
            return [];
        }
    }
    private function processedApiCallRequest(array $response){
        if (isset($response['has_error']) && $response['has_error'] == true){
            $errorCode = $response['api_code'];
            switch ($errorCode){
                case 'E027':
                    return [
                        'status' => 'fail',
                        'data' => [
                            'errorCode' => $errorCode,
                            'errorDescription' => $response['api_code_description'],
                            'errorDetails' => 'Hash sent in API call doesn\'t match with Hash generated by the server'
                        ]
                    ];
                    break;
                default:
                    return [
                        'status' => 'fail',
                        'data' => [
                            'errorCode' => $errorCode,
                            'errorDescription' => $response['api_code_description'],
                            'errorDetails' => ''
                        ]
                    ];
            }
        }else{
            return [
                'status' => 'success',
                'data' => $response
            ];
        }
    }

    private function saveAccessedCreditInfo(array $reportPayload){
        $formatted = $reportPayload;
        $uniqueKey = Uuid::uuid4();
        $data =  [
            'trx_id' => $formatted['trx_id'] ? $formatted['trx_id'] : '',
            'transaction_uid' => $uniqueKey,
            'api_code' => isset($formatted['api_code']) ? $formatted['api_code'] : '',
            'api_description' => isset($formatted['api_code_description'])?$formatted['api_code_description']:'',
            'delinquency_code' => isset($formatted['delinquency_code'])?$formatted['delinquency_code']:'',
            'credit_score' => isset($formatted['credit_score'])?$formatted['credit_score']:'',
            'has_error' => isset($formatted['has_error'])?$formatted['has_error']:'',
            'has_fraud' => isset($formatted['has_fraud'])?$formatted['has_fraud']:'',
            'identity_number' => isset($formatted['identity_number'])?$formatted['identity_number']:'',
            'identity_type' => isset($formatted['identity_type'])?$formatted['identity_type']:'',
            'is_guarantor' => isset($formatted['is_guarantor'])?$formatted['is_guarantor']:'',

            'guarantors' => isset($formatted['guarantors'])?$formatted['guarantors']:null,
            'lender_sector' => isset($formatted['lender_sector'])?$formatted['lender_sector']:null,
            'no_of_bounced_cheques' => isset($formatted['no_of_bounced_cheques'])?$formatted['no_of_bounced_cheques']:null,
            'no_of_credit_applications' => isset($formatted['no_of_credit_applications'])?$formatted['no_of_credit_applications']:null,
            'no_of_enquiries' => isset($formatted['no_of_enquiries'])?$formatted['no_of_enquiries']:null,
            'account_info' => isset($formatted['account_info'])?$formatted['account_info']:[],

            'identity_scrub' => isset($formatted['identity_scrub'])?$formatted['identity_scrub']:null,
            'identity_verification' => isset($formatted['identity_verification'])?$formatted['identity_verification']:null,
            'metro_score_trend' => isset($formatted['metro_score_trend'])?$formatted['metro_score_trend']:null,
        ];
        $savedReport = MetropolEnhancedCreditInfo::create($data);
        $analysis = array_merge($this->computeMetropolCreditInformation($savedReport['account_info']),[
            'credit_score' => $savedReport['credit_score']
        ]);
        return [
            'computedReport' => $analysis,
            'forCbsReporting' => array_merge([
                'identity_number' => $savedReport['identity_number'],
                'identity_type' => $savedReport['identity_type'],
                'transaction_uid' => $savedReport['trx_id'],
                'delinquency_code' => $savedReport['delinquency_code'],
                'is_guarantor' => $savedReport['is_guarantor'],
            ],$analysis),
            'savedReport' => $savedReport,
        ];
    }

    public function computeMetropolCreditInformation(array $enhancedCreditInfoArray){
        $clientInfo = [];
        if (count($enhancedCreditInfoArray) > 0){
            array_walk($enhancedCreditInfoArray,function ($account_info) use (&$clientInfo){

                if ($account_info['account_status'] == 'Active'){ //2 ->front , 64 -> back
                    $clientInfo['active_loans'][] = $account_info['highest_days_in_arrears'];
                }elseif ($account_info['account_status'] == 'Fully Settled'){ //2 ->front , 64 -> back
                    $clientInfo['settled_loans'][] = $account_info['highest_days_in_arrears'];
                }else{
                    $clientInfo['defaulted_loans'][] = $account_info['highest_days_in_arrears'];
                }
                $clientInfo['highest_arrears_days'][] = $account_info['highest_days_in_arrears'];
            });

            return [
                'total_loans' => count($enhancedCreditInfoArray),
                'total_active_loans' => isset($clientInfo['active_loans']) ? count($clientInfo['active_loans']):0,
                'total_defaulted_loans' => isset($clientInfo['defaulted_loans']) ? count($clientInfo['defaulted_loans']): 0,
                'total_settled_loans' => isset($clientInfo['settled_loans'])? count($clientInfo['settled_loans']):0,
                'maximum_defaulted_days' => isset($clientInfo['highest_arrears_days'])? max($clientInfo['highest_arrears_days']):0,
            ];
        }
        return [];

    }

}
