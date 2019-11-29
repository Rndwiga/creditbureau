<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetropolEnhancedCreditInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metropol_enhanced_credit_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trx_id')->nullable()->index();
            $table->string('transaction_uid')->nullable();
            $table->string('api_code')->nullable();
            $table->string('api_description')->nullable();
            $table->string('credit_score')->nullable(); //
            $table->string('delinquency_code')->nullable();
            $table->string('has_error')->nullable();
            $table->string('has_fraud')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('is_guarantor')->nullable();
            $table->json('guarantors')->nullable();
            $table->json('lender_sector')->nullable();
            $table->json('no_of_bounced_cheques')->nullable();
            $table->json('no_of_credit_applications')->nullable();
            $table->json('no_of_enquiries')->nullable();
            $table->json('account_info')->nullable();

            $table->json('identity_scrub')->nullable();
            $table->json('identity_verification')->nullable();
            $table->json('metro_score_trend')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metropol_enhanced_credit_infos');
    }
}
