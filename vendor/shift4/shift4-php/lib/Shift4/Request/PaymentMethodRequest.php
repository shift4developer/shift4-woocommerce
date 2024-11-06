<?php

namespace Shift4\Request;

class PaymentMethodRequest extends AbstractRequest
{

    public function getId()
    {
        return $this->get('id');
    }

    public function id($id)
    {
        return $this->set('id', $id);
    }

    public function getCustomerId()
    {
        return $this->get('customerId');
    }

    public function customerId($customerId)
    {
        return $this->set('customerId', $customerId);
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function type($type)
    {
        return $this->set('type', $type);
    }

    /**
     * @return \Shift4\Request\BillingRequest
     */
    public function getBilling()
    {
        return $this->getObject('billing', '\Shift4\Request\BillingRequest');
    }


    /**
     * @param \Shift4\Request\BillingRequest $billing
     */
    public function billing($billing)
    {
        return $this->set('billing', $billing);
    }

    /**
     * @return \Shift4\Request\PaymentMethodRequestApplePay
     */
    public function getApplePay()
    {
        return $this->getObject('applePay', '\Shift4\Request\PaymentMethodRequestApplePay');
    }

    /**
     * @param \Shift4\Request\PaymentMethodRequestApplePay $applePay
     */
    public function applePay($applePay)
    {
        return $this->set('applePay', $applePay);
    }
}
