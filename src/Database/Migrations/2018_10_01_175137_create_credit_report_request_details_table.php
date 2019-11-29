<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditReportRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_report_request_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobileNumber');
            $table->unsignedInteger('nationalId');
            $table->string('amountToPay');
            $table->string('transactionDetails');
            $table->string('callBackUrl');
            $table->string('commandType')->nullable();

            $table->string('requestStatus')->nullable();
            $table->string('MerchantRequestID')->nullable();
            $table->string('CheckoutRequestID')->nullable();
            $table->string('CustomerMessage')->nullable();

            $table->string('responseStatus')->nullable();
            $table->string('MpesaReceiptNumber')->nullable();
            $table->string('ResultCode')->nullable();
            $table->string('ResultDesc')->nullable();
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
        Schema::dropIfExists('credit_report_request_details');
    }
}
