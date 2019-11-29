<?php


namespace Rndwiga\CreditBureau\Infrastructure\Services;


class ServicesTests
{
    public function testProcessedDataFlow(){
        $responses  = [
            [
                "status"=> "fail",
                "message"=> "No Account Information",
                "data"=> [
                    "reportRetrieved"=> ["api_code"=> "E017",
                        "api_code_description"=> "No Account Information","has_error"=>
                            false,"identity_number"=> "27236239","identity_type"=> "001",
                        "loan_amount"=> 500,"trx_id"=>
                            "911c1681-5608-4bd3-9fd2-60b5e81783bb"],
                    "processedInformation"=> [
                        "status"=> "success",
                        "data"=> [
                            "api_code"=> "E017",
                            "api_code_description"=> "No Account Information",
                            "has_error"=> false,
                            "identity_number"=> "27236239",
                            "identity_type"=> "001",
                            "loan_amount"=> 500,
                            "trx_id"=> "911c1681-5608-4bd3-9fd2-60b5e81783bb"
                        ]
                    ]
                ]
            ],
            [
                "status"=> "success",
                "message"=> "report accessed and analyzed successfully",
                "data"=> [
                    "credit_score"=> 0,
                    "total_loans"=> 0,
                    "total_active_loans"=> 0,
                    "total_defaulted_loans"=> 0,
                    "total_settled_loans"=> 0,
                    "maximum_defaulted_days"=> 0
                ]
            ],
            [
                "status"=> "success",
                "message"=> "report accessed and analyzed successfully",
                "data"=> [
                    "credit_score"=> 20,
                    "total_loans"=> 20,
                    "total_active_loans"=> 2,
                    "total_defaulted_loans"=> 2,
                    "total_settled_loans"=> 16,
                    "maximum_defaulted_days"=> 0
                ]
            ],
            [
                "status"=> "fail",
                "message"=> "The response received was not correct",
                "developerMessage"=> "The response received from Metropol was an invalid json",
                "data"=> [
                    "reportRetrieved"=> "<html lang=\"en\">\n    <head>\n        <meta charset=\"UTF-8\">\n        <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n\n        <meta name=\"csrf-token\" content=\"txl7oDaaMtyJUyQLfZOF0o7leTtahE0tTWVFRObI\">\n\n        <title>Laravel</title>\n        <!-- Favicon -->\n        <link href=\"http://127.0.0.1:8000/argon/img/brand/favicon.png\" rel=\"icon\" type=\"image/png\">\n        <!-- Fonts -->\n        <link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700\" rel=\"stylesheet\">\n        <!-- Icons -->\n        <link href=\"http://127.0.0.1:8000/argon/vendor/nucleo/css/nucleo.css\" rel=\"stylesheet\">\n        <link href=\"http://127.0.0.1:8000/argon/vendor/@fortawesome/fontawesome-free/css/all.min.css\" rel=\"stylesheet\">\n        <link href=\"http://127.0.0.1:8000/argon/vendor/select2/dist/css/select2.min.css\" rel=\"stylesheet\">\n        <link href=\"http://127.0.0.1:8000/argon/vendor/summernote/dist/summernote-bs4.css\" rel=\"stylesheet\">\n        <!-- Argon CSS -->\n        <link type=\"text/css\" href=\"http://127.0.0.1:8000/argon/css/argon.css?v=1.0.0\" rel=\"stylesheet\">\n    </head>\n    <body class=\"bg-default\">\n    </body>\n    </html>"
                ]
            ]

        ];
        $randomStatus = array_rand($responses,1);
        return $responses[$randomStatus];
    }

    public function testRequestData(){
        $requestResponse = file_get_contents('php://input');
        return (new MetropolApiProcessingService())->finalizeProcessingApiRequest($requestResponse);
    }
}
