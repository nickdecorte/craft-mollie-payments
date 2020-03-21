<?php

namespace studioespresso\molliepayments\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\models\PaymentTransactionModel;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\records\PaymentFormRecord;

class Mollie extends Component
{
    private $mollie;

    public function init()
    {
        $this->mollie = new \Mollie\Api\MollieApiClient();
        $this->mollie->setApiKey(Craft::parseEnv(MolliePayments::getInstance()->getSettings()->apiKey));
    }

    public function generatePayment(Payment $payment, $redirect)
    {
        $paymentForm = MolliePayments::getInstance()->forms->getFormByid($payment->formId);
        $baseUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();


        if($paymentForm->descriptionFormat) {
           $description = Craft::$app->getView()->renderObjectTemplate($paymentForm->descriptionFormat, $payment);
        } else {
            $description = "Order #{$payment->id}";
        }

        $authorization = $this->mollie->payments->create([
            "amount" => [
                "currency" => $paymentForm->currency,
                "value" => number_format($payment->amount, 2, '.', '') // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "method" => $payment->method,
            "description" => $description,
            "redirectUrl" => UrlHelper::url("{$baseUrl}mollie-payments/payment/redirect", [
                "order_id" => $payment->uid,
                "redirect" => $redirect
            ]),
            "webhookUrl" => "{$baseUrl}mollie-payments/payment/webhook",
            "metadata" => [
                "redirectUrl" => $redirect,
                "element" => $payment->uid,
                "description" => $payment->title
            ],
        ]);


        $transaction = new PaymentTransactionModel();
        $transaction->id = $authorization->id;
        $transaction->payment = $payment->id;
        $transaction->currency = $paymentForm->currency;
        $transaction->amount = $payment->amount;
        $transaction->status = $authorization->status;

        MolliePayments::getInstance()->transaction->save($transaction);


        return $authorization->_links->checkout->href;
    }

    public function getStatus($orderId)
    {
        return $this->mollie->payments->get($orderId);
    }

}
