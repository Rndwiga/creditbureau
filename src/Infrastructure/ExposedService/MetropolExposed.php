<?php


namespace Rndwiga\CreditBureau\Infrastructure\ExposedService;


use Rndwiga\CreditBureau\Infrastructure\Services\MetropolApiProcessingService;
use Rndwiga\CreditBureau\Model\MetropolEnhancedCreditInfo;

class MetropolExposed
{
    public function getReportCreditScore(string $nationalId){
        $fullScore = ($this->getFullMetropolReport($nationalId));
        if ($fullScore['status'] == 'success'){
            return [
                'metropolScore' => $fullScore['data']['summary']['credit_score'],
                'nationalId' => $nationalId
            ];
        }else{
            return [
                'metropolScore' => 0,
                'nationalId' => $nationalId
            ];
        }
    }
    public function getFullMetropolReport(string $nationalId){
        $fr = $this->getReportFromStorage($nationalId);
        if (empty($fr)){
            return [
                'status' => 'fail',
                'message' => 'Sorry, there is no report for that identifier'
            ];
        }

        return [
            'status' => 'success',
            'data' => [
                'summary' => $this->convertKeysToCamelCase((new MetropolApiProcessingService())->computeMetropolCreditInformation(($this->getReportFromStorage($nationalId))['account_info'])),
                'fullReport' => $this->convertKeysToCamelCase($fr)
            ]
        ];
    }

    private function convertKeysToCamelCase($apiResponseArray)
    {
        $arr = [];
        foreach ($apiResponseArray as $key => $value) {
            if (preg_match('/_/', $key)) {
                preg_match('/[^_]*/', $key, $m);
                preg_match('/(_)([a-zA-Z]*)/', $key, $v);
                $key = $m[0] . ucfirst($v[2]);
            }


            if (is_array($value))
                $value = $this->convertKeysToCamelCase($value);

            $arr[$key] = $value;
        }
        return $arr;
    }

    private function getReportFromStorage(string $nationalId){
        $fr = (MetropolEnhancedCreditInfo::where('identity_number',$nationalId)->orderBy('id','desc')->get()->toArray());
        if (count($fr) > 0){
            return $fr[0];
        }else{
            return[];
        }
    }
}
