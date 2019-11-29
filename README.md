# CreditBureau Referencing
This is package handles the following::
* Requesting for payment via STK service
* Requesting for credit report from Metropol - (reportType 14)
* Pushing the accessed report to an external service
* It accepts external services to connect to it giving either Score based on identifier or full score

## Composer Installation
As the package has not been pushed to packagist yet, it can be added to composer as a local library.

This is how to go about it::
* In the require block add the line ``````"rndwiga/credit_bureau": "@dev",``````
* Under the repositories block add
``````
    "repositories": [
        {
            "type": "path",
            "url": "path_to_this_package",
            "options": {
                "symlink": true
            }
        }
    ]
``````
* Then run the command ``````composer update`````` to install

##  Installation and Configuration
After the package is installed, you need to set-up the following environmental variables for it to work properly:
``````

CREDIT_BUREAU_TRANSACTION_AMOUNT=0
CREDIT_BUREAU_STK_SERVICE_URL=""
CREDIT_BUREAU_SMS_SERVICE_URL=""
CREDIT_BUREAU_UPLOAD_TO_SERVICE=bool

CREDIT_BUREAU_METROPOL_API_VERSION=""
CREDIT_BUREAU_METROPOL_PORT_NUMBER=""
CREDIT_BUREAU_METROPOL_PUBLIC_KEY=""
CREDIT_BUREAU_METROPOL_PRIVATE_KEY=""

``````
These keys and database can be populated automatically by running the commands
``````
 php artisan CreditBureau:migrate
 php artisan CreditBureau:install
``````

#### How to request for credit report (initial)
Make an API request to the endpoint ``````POST api/v1/hooks/credit/report``````
with the following data sample

``````
{
	"phoneNumber": 254700000000,
	"idNumber": "12345678",
	"sourceApp": "sourceApp",
	"bureauApp": "crb",
	"commandType" :"businessCrbCheck"
}
``````
The commandType can either be  ```````` businessCrbCheck```````` or ```````` individualCrbCheck````````

When the above request is made, the given phoneNumber will receive an STK menu to complete the transaction

#### How to request for stored credit report (stored)
Make an API request to the endpoint ``````GET api/v1/creditbureau/metropol/score/{nationalId}``````

The application will provide appropriate response
