<?php


namespace Rndwiga\CreditBureau\Infrastructure\Services;


use Rndwiga\CreditBureau\Model\CreditReportRequestDetail;
use Rndwiga\Toolbox\Infrastructure\Services\AppLogger;

class PaymentProcessingService extends BaseService
{
    private function getAmountToPay(){
        if (empty(env('CREDIT_BUREAU_TRANSACTION_AMOUNT'))){
            die("Please declare the amount to be paid on :: CREDIT_BUREAU_TRANSACTION_AMOUNT");
        }
        return env('CREDIT_BUREAU_TRANSACTION_AMOUNT');
    }
    private function getPaymentServiceUrl(){
        if (empty(env('CREDIT_BUREAU_STK_SERVICE_URL'))){
            die("Please define :: CREDIT_BUREAU_STK_SERVICE_URL :: on the environment file");
        }
        return env('CREDIT_BUREAU_STK_SERVICE_URL');
    }

    public function startPaymentProcess(array $requestData){
        $paymentRequestResponse =  $this->initializePaymentViaStk($requestData['phoneNumber'], $requestData['idNumber'], "CRB " . $requestData['idNumber'],$requestData['commandType']);

        (new AppLogger('crbPaymentRequests','payment_processing'))->logInfo([
            'where'=>'PaymentProcessingService->startPaymentProcess',
            'requestData'=>$requestData,
            'paymentRequestResponse'=>$paymentRequestResponse,
        ]);

        return [
            'paymentRequest' =>  $paymentRequestResponse,
        ];
    }

    private function initializePaymentViaStk(int $mssdn, $idNumber, string $transactionDetails,string $commandType){
        $paymentData = [
            'mobileNumber' => $mssdn,
            'nationalId' => (int)$idNumber,
            'amountToPay' => $this->getAmountToPay(),
            'transactionDetails' => $transactionDetails,
            'callBackUrl' => env('APP_URL') . '/api/v1/hooks/payments/stk'
        ];

        $creditReportDt =  (new CreditReportRequestDetail())::create(array_merge($paymentData,['commandType' => $commandType]));

        $paymentRequest = $this->postToService($this->getPaymentServiceUrl(),$paymentData);

        (new AppLogger('crbPaymentRequests','payment_processing'))->logInfo([
            'where'=>'PaymentProcessingService->initializePaymentViaStk',
            'paymentInfo'=>$paymentData,
            'savedRequestDetails'=>$creditReportDt,
            'paymentRequestResponse'=>$paymentRequest,
        ]);

        if (count($paymentRequest) > 0){
            $creditReportDt->requestStatus = $paymentRequest['requestStatus'];
            $creditReportDt->MerchantRequestID = $paymentRequest['MerchantRequestID'];
            $creditReportDt->CheckoutRequestID = $paymentRequest['CheckoutRequestID'];
            $creditReportDt->CustomerMessage = $paymentRequest['CustomerMessage'];
            $creditReportDt->save();
            return [
                'status' => 'success',
                'message' => 'Stk menu requested successfully',
                'data' => $creditReportDt
            ];
        }else{
            //TODO:: Say ? to client payment request failed , 2) to db request failed
            (new CommunicationApiProcessingService())->setMobileNumber($mssdn)
                ->setFormattedReportResponse([
                    'message' => "We encountered an error when requesting for payment from Mpesa"
                ])->failedReportGenerationSms();
            return [
                'status' => 'fail',
                'message' => 'Stk menu NOT requested',
                'data' => $creditReportDt
            ];
        }
    }

    public function completePaymentProcess(array $paymentResponseData){
        $creditReport = (new CreditReportRequestDetail())::where('MerchantRequestID',$paymentResponseData['data']['MerchantRequestID'])->first();

        if ($creditReport){
            $creditReport->responseStatus = $paymentResponseData['status'];
            if ($paymentResponseData['status'] === 'success'){
                $creditReport->MpesaReceiptNumber = $paymentResponseData['data']['MpesaReceiptNumber'];
                $creditReport->save();
                $updatedRequest = $creditReport->toArray();

                if ($updatedRequest['commandType'] == 'businessCrbCheck'){
                    $reportGeneration=  (new MetropolApiProcessingService())->setCompanyIdentifier($updatedRequest['nationalId'])
                        ->setReportType(14)->fetchCorporateReportInJson();
                }else{
                    $reportGeneration = (new MetropolApiProcessingService())->setNationalId($updatedRequest['nationalId'])
                        ->setReportType(14)->fetchIndividualReportInJson();
                }

                if ($reportGeneration['status'] == 'success'){

                   $commRespose =  (new CommunicationApiProcessingService())->setMobileNumber((int)$updatedRequest['mobileNumber'])
                        ->setFormattedReportResponse($reportGeneration['data'])->successfulReportGenerationSms();
                }elseif($reportGeneration['status'] == 'fail'){
                    $commRespose =  (new CommunicationApiProcessingService())->setMobileNumber((int)$updatedRequest['mobileNumber'])
                        ->setFormattedReportResponse([
                            'message' => $reportGeneration['message']
                        ])->failedReportGenerationSms();
                }

                (new AppLogger('crbPaymentRequests','payment_processing'))->logInfo([
                    'where'=>'PaymentProcessingService->completePaymentProcess::success',
                    'apiResponseData'=>$paymentResponseData,
                    'savedItem'=>$creditReport,
                    'generatedAnalysis' => $reportGeneration
                ]);

                return [
                    'status' => 'success',
                    'data' => [
                        'generatedReport' => $reportGeneration,
                        'communicationResponse' => $commRespose,
                    ]
                ];

            }elseif ($paymentResponseData['status'] === 'fail'){
                $creditReport->ResultCode = $paymentResponseData['data']['ResultCode'];
                $creditReport->ResultDesc = $paymentResponseData['data']['ResultDesc'];
                $creditReport->save();

                (new AppLogger('crbPaymentRequests','payment_processing'))->logInfo([
                    'where'=>'PaymentProcessingService->completePaymentProcess::failure',
                    'apiResponseData'=>$paymentResponseData,
                    'savedItem'=>$creditReport,
                ]);

                (new CommunicationApiProcessingService())->setMobileNumber($paymentResponseData['data']['MpesaReceiptNumber'])
                    ->setFormattedReportResponse([
                        'message' => "{$paymentResponseData['data']['ResultDesc']}"
                    ])->failedReportGenerationSms();

                return [
                    'status' => 'fail',
                    'data' => $creditReport->toArray()
                ];
            }
        }else{
            //TODO::Log this failed attempt
            return [
                'status' => 'fail',
                'message' => 'The data received does not have a matching entry in our records',
                'data' => $paymentResponseData
            ];
        }

    }

}
