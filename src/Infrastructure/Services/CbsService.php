<?php


namespace Rndwiga\CreditBureau\Infrastructure\Services;


use Rndwiga\CreditBureau\Libraries\Metropol\MetropolResponseFormatter;
use Rndwiga\Mifos\DataUpload;
use Rndwiga\Mifos\src\Modules\Search\ClientSearch;

class CbsService
{
    public function updateClientProfileOnMifos(array $savedReport){
        $clientId = $this->getMusoniClientId($savedReport['identity_number']);

        if ($clientId > 0){
            $urlSegment = "/datatables/cct_CreditReportMetropol/". $clientId;
            $postData = [
                'Report_URL12' => route('admin.metropol.report.creditInfo.report') .'/' . $savedReport['transaction_uid'],
                'client_id' => $clientId,
                'Credit_Score1' => $savedReport['credit_score'] ? (string)$savedReport['credit_score'] : '',
                'Delinquency_Status4' => ((new MetropolResponseFormatter())->delinquencyCodes($savedReport['delinquency_code']))['reportName'],
                'Has_Fraud5' => $savedReport['credit_score'] === true ? 'Yes' : 'No',
                'Identity_Number6' => $savedReport['identity_number'],
                'Identity_Type7' => ((new MetropolResponseFormatter())->identityType($savedReport['identity_type']))['type'],
                'Is_Guarantor8' => $savedReport['is_guarantor'] === true ? 'Yes' : 'No',
                'Total_Number_Loans9' => $savedReport['total_loans'],
                'Active_Loans_Number10'  => $savedReport['total_active_loans'],
                'Default_Loans_Number11'  => $savedReport['total_defaulted_loans'],
                'Maximum_Days_De13'  => $savedReport['maximum_defaulted_days'],
                'submittedon_date' => date("d M Y"),
                'submittedon_userid' => 613,
                'dateFormat'=> "dd MMMM yyyy",
                'locale' => 'en'
            ];
            return (new DataUpload())->sendData($postData,$urlSegment,"POST");
        }
        return $clientId;
    }

    private function getMusoniClientId(string $identifierNumber):int {
        $clientIdentifiers = (new ClientSearch())->setArrayResponse(true)->setResourceType('clientIdentifiers')
            ->setClientIdentifier($identifierNumber)->searchClient();

        if (count($clientIdentifiers) > 0) {
            return $clientIdentifiers[0]['parentId'];
        }else{
           return 0;
        }
    }
}
