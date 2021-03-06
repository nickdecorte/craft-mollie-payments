<?php

namespace studioespresso\molliepayments\services;

use craft\base\Component;
use studioespresso\molliepayments\models\PaymentFormModel;
use studioespresso\molliepayments\records\PaymentFormRecord;

class Form extends Component
{
    public function save(PaymentFormModel $paymentFormModel)
    {
        $paymentFormRecord = false;
        if (isset($paymentFormModel->id)) {
            $paymentFormRecord = PaymentFormRecord::findOne([
                'id' => $paymentFormModel->id
            ]);
        }

        if (!$paymentFormRecord) {
            $paymentFormRecord = new PaymentFormRecord();
        }


        $paymentFormRecord->title = $paymentFormModel->title;
        $paymentFormRecord->handle = $paymentFormModel->handle;
        $paymentFormRecord->currency = $paymentFormModel->currency;
        $paymentFormRecord->descriptionFormat = $paymentFormModel->descriptionFormat;
        $paymentFormRecord->fieldLayout = $paymentFormModel->fieldLayout;
        return $paymentFormRecord->save();
    }

    public function getAllForms()
    {
        $forms = PaymentFormRecord::find()->all();
        return $forms;
    }

    public function getFormByid($id)
    {
        $form = PaymentFormRecord::findOne(['id' => $id]);
        return $form;
    }

    public function getFormByHandle($handle)
    {
        $form = PaymentFormRecord::findOne(['handle' => $handle]);
        return $form;
    }

    public function delete($id) {
        $paymentFormRecord = PaymentFormRecord::find(['id' => $id])->one();
        return $paymentFormRecord->delete();
    }
}
