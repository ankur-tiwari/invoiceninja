<?php

/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\PaymentDrivers;

use App\Models\ClientGatewayToken;
use App\Models\GatewayType;
use App\PaymentDrivers\Authorize\AuthorizeCreditCard;
use App\PaymentDrivers\Authorize\AuthorizePaymentMethod;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\GetMerchantDetailsRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\GetMerchantDetailsController;

/**
 * Class BaseDriver
 * @package App\PaymentDrivers
 *
 */
class AuthorizePaymentDriver extends BaseDriver
{

    public $merchant_authentication;

    public static $methods = [
        GatewayType::CREDIT_CARD => AuthorizeCreditCard::class,
    ];

    public function setPaymentMethod($payment_method_id)
    {

        $class = self::$methods[$payment_method_id];

        $this->payment_method = new $class($this);

        return $this;

    }
    /**
     * Returns the gateway types
     */
    public function gatewayTypes() :array
    {
        $types = [
            GatewayType::CREDIT_CARD,
        ];

        return $types;
    }

    public function init()
    {
        error_reporting (E_ALL & ~E_DEPRECATED);

        $this->merchant_authentication = new MerchantAuthenticationType();
        $this->merchant_authentication->setName($this->company_gateway->getConfigField('apiLoginId'));
        $this->merchant_authentication->setTransactionKey($this->company_gateway->getConfigField('transactionKey'));

        return $this;
    }

    public function getPublicClientKey()
    {

        $request = new GetMerchantDetailsRequest();
        $request->setMerchantAuthentication($this->merchant_authentication);

        $controller = new GetMerchantDetailsController($request);
        $response = $controller->executeWithApiResponse($this->mode());

        return $response->getPublicClientKey();

    }

    public function mode()
    {

        if($this->company_gateway->getConfigField('testMode'))
            return  ANetEnvironment::SANDBOX;
        
        return $env = ANetEnvironment::PRODUCTION;

    }

    public function authorizeView($payment_method)
    {
        return (new AuthorizePaymentMethod($this))->authorizeView($payment_method);
    }

    public function authorizeResponseView(array $data)
    {
        return (new AuthorizePaymentMethod($this))->authorizeResponseView($data['gateway_type_id'], $data);
    }

    public function authorize($payment_method) 
    {
        return $this->authorizeView($payment_method);
    }
    
    public function processPaymentView($data)
    {

        return $this->payment_method->processPaymentView($data);

    }

    public function processPaymentResponse($request)
    {

        return $this->payment_method->processPaymentResponse($request);

    }

    public function purchase($amount, $return_client_response = false) 
    {
        return false;
    }

    public function refund($amount, $transaction_reference, $return_client_response = false) 
    {

    }

    public function findClientGatewayRecord() :?ClientGatewayToken
    {
        return ClientGatewayToken::where('client_id', $this->client->id)
                                 ->where('company_gateway_id', $this->company_gateway->id)
                                 ->first();
    }
}