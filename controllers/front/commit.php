<?php

use Transbank\Onepay\OnepayBase;
use Transbank\Onepay\ShoppingCart;
use Transbank\Onepay\Item;
use Transbank\Onepay\Transaction;
use \Transbank\Onepay\Exceptions\TransactionCreateException;
use \Transbank\Onepay\Exceptions\TransbankException;

class OnepayCommitModuleFrontController extends ModuleFrontController
{
    public function init() {

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            OnepayBase::setSharedSecret(Configuration::get('ONEPAY_SHARED_SECRET', null));
            OnepayBase::setApiKey(Configuration::get('ONEPAY_APIKEY', null));
            OnepayBase::setCurrentIntegrationType(Configuration::get('ONEPAY_ENDPOINT', null));

            $this->ajaxDie(json_encode([
                'success' => true,
                'operation' => 'get'
            ]));
        } else {
            $this->ajaxDie(json_encode([
                'success' => false
            ]));
        }
    }
}