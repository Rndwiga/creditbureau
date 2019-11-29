<?php

namespace Rndwiga\CreditBureau\Api\Http\Controllers;

use Illuminate\Http\Request;
use Rndwiga\CreditBureau\Api\Http\Requests\CreditReportFormRequest;
use Rndwiga\CreditBureau\Infrastructure\ExposedService\MetropolExposed;
use Rndwiga\CreditBureau\Infrastructure\Services\PaymentProcessingService;

class MetropolApiIntegration extends ApiController
{

    public function __construct()
    {
        //TODO can handle further pre and post processing here
    }

    public function getCreditReport(CreditReportFormRequest $request)
    {
       return (new PaymentProcessingService())->startPaymentProcess($request->all());
    }

    public function handleStkPaymentNotifications(Request$request){
        return (new PaymentProcessingService())->completePaymentProcess($request->all());
    }

    public function exposedGetFullMetropolReport(Request$request){
        return (new MetropolExposed())->getFullMetropolReport(($request->route()->parameters())['nationalId']);
    }

    public function exposedGetMetropolReportScore(Request $request){
        return (new MetropolExposed())->getReportCreditScore($request->input('nationalId'));
    }
}
