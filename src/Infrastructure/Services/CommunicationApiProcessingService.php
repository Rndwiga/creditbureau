<?php


namespace Rndwiga\CreditBureau\Infrastructure\Services;


class CommunicationApiProcessingService extends BaseService
{
    private $mobileNumber, $formattedReportResponse;

    /**
     * @param mixed $mobileNumber
     * @return CommunicationApiProcessingService
     */
    public function setMobileNumber(int $mobileNumber): CommunicationApiProcessingService
    {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    /**
     * @param mixed $formattedReportResponse
     * @return CommunicationApiProcessingService
     */
    public function setFormattedReportResponse(array $formattedReportResponse):CommunicationApiProcessingService
    {
        $this->formattedReportResponse = $formattedReportResponse;
        return $this;
    }

    private function getSMSServiceUrl(){
        if (empty(env('CREDIT_BUREAU_SMS_SERVICE_URL'))){
            die("Please define :: CREDIT_BUREAU_SMS_SERVICE_URL :: on the environment file");
        }
        return env('CREDIT_BUREAU_SMS_SERVICE_URL');
    }

    public function successfulReportGenerationSms(){
        $creditScore = $this->formattedReportResponse['credit_score']
            ? (string)$this->formattedReportResponse['credit_score'] : '';

        $commRequest = $this->postToService($this->getSMSServiceUrl(),[
            'mobileNumber' => $this->mobileNumber,
            'message' => "Here is your credit details
            creditScore is: {$creditScore}
            TotalNumberOfLoans is: {$this->formattedReportResponse['total_loans']}
            NumberOfActiveLoans is: {$this->formattedReportResponse['total_active_loans']}
            NumberOfDefautLoans is: {$this->formattedReportResponse['total_defaulted_loans']}",
            'senderId' => '4eeeedcb106d62188ba58d90d0a960ec'
        ]);

        return $commRequest;
    }

    public function failedReportGenerationSms(){
        $commRequest = $this->postToService($this->getSMSServiceUrl(),[
            'mobileNumber' => $this->mobileNumber,
            'message' => "Sorry.{$this->formattedReportResponse['message']}",
            'senderId' => '4eeeedcb106d62188ba58d90d0a960ec'
        ]);
        return $commRequest;
    }

    public function failedStkPaymentSms(){

    }

}
