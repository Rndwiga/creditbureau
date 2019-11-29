<?php

namespace Rndwiga\CreditBureau\Model;

use Illuminate\Database\Eloquent\Model;

class MetropolEnhancedCreditInfo extends Model
{
    protected $guarded = ['id'];

    //casting the json attribute to array
    protected $casts = [
        'account_info' => 'array',
        'no_of_enquiries' => 'array',
        'no_of_credit_applications' => 'array',
        'no_of_bounced_cheques' => 'array',
        'lender_sector' => 'array',
        'guarantors' => 'array',

        'identity_scrub' => 'array',
        'identity_verification' => 'array',
        'metro_score_trend' => 'array',
    ];
}
