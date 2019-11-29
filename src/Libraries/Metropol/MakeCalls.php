<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 11/8/18
 * Time: 10:24 AM
 */

namespace Rndwiga\CreditBureau\Libraries\Metropol;


class MakeCalls extends ApiConnect
{
    public function sampleRequest(){

        $this->setBaseEndPoint()->setPublicApiKey(env('CREDIT_BUREAU_METROPOL_PUBLIC_KEY'))
            ->setPrivateApiKey(env('CREDIT_BUREAU_METROPOL_PRIVATE_KEY'))
            ->setPort(5555)->setApiVersion("v2_1")
            ->setReportType(8)->setIdNumber("880000088")
            ->setLoanAmount(500)->getCreditInfo();
    }

    public function getIdentityVerification(int $report_type = 1)
    {
        $endpoint = '/identity/verify';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
        ];

        return $this->apiHttpPost($endpoint, $payload);
    }

    public function getDeliquencyStatus(int $report_type = 2)
    {
        $endpoint = '/delinquency/status';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
            "loan_amount"     => $this->loan_amount,
        ];

        return $this->apiHttpPost($endpoint, $payload);
    }


    public function getConsumerScore(int $report_type = 3)
    {
        $endpoint = '/score/consumer';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
        ];

        return $this->apiHttpPost($endpoint, $payload);
    }

    public function getReportPdf(int $report_type = 4){
        $urlSegment = '/report/pdf';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
            "loan_amount"     => $this->loan_amount,
            "report_reason"   => 1,
        ];

        return $this->apiHttpPost($urlSegment,$payload);
    }

    public function getReportJson(int $report_type = 5,string $identityType = "001"){
        $urlSegment = '/report/json';

        if ($identityType == "005"){
            $payload = [
                "report_type"=>$report_type,
                "identity_number"=>$this->id_number,
                "identity_type"=>$identityType,
                "loan_amount"=>$this->loan_amount,
                "report_reason"=>1
            ];
        }else{
            $payload = [
                "report_type"=>$report_type,
                "identity_number"=>(string)$this->id_number,
                "identity_type"=>$identityType,
                "loan_amount"=>$this->loan_amount,
                "report_reason"=>1
            ];
        }

        return $this->apiHttpPost($urlSegment,$payload);
    }

    public function getIdentityScrub(int $report_type = 6){
        $urlSegment = '/identity/scrub';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001"
        ];

        return $this->apiHttpPost($urlSegment,$payload);
    }


    public function getCreditInfo(int $report_type = 8)
    {
        $endpoint = '/report/credit_info';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
            "loan_amount"     => $this->loan_amount,
            "report_reason"   => 1,
        ];

        return $this->apiHttpPost($endpoint, $payload);
    }


    public function getEnhancedCreditInfo(int $report_type = 10){
        $urlSegment = '/report/credit_info_enhanced';

        $payload = [
            "report_type"     => $report_type,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
            "loan_amount"     => $this->loan_amount,
            "report_reason"   => 1,
        ];

        return $this->apiHttpPost($urlSegment,$payload);
    }

    public function getCreditInfoWithRef(int $report_type = 10){
        $urlSegment = '/report/credit_info_enhanced';

        $payload = [
            "report_type"     => $report_type,
            "report_reason"   => 1,
            "identity_number" => (string) $this->id_number,
            "identity_type"   => "001",
            "loan_amount"     => $this->loan_amount,
            "application_ref_no" => (string)$this->reference_no,
        ];
        return $this->apiHttpPost($urlSegment,$payload);
    }

    public function getEnhancedMultipleInfo(array $IdentityNumberArray, array $identityTypesArray,int $report_type = 10){
        //TODO:: Enhance this method, it has alot of work-arounds
        $nationalIds = implode(',', $IdentityNumberArray);
        $identityTypes = implode(',', $identityTypesArray);

        $urlSegment = '/report/credit_info_enhanced';

        $payload = [
            "report_type"     => $report_type,
            "report_reason"   => 1,
            "identity_number" => (string) $nationalIds,
            "identity_type"   => (string) $identityTypes,
            "loan_amount"     => $this->loan_amount,
        ];

        return $this->apiHttpPost($urlSegment,$payload);
    }
}
