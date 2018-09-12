<?php

use Transbank\Onepay\OnepayBase;
use Transbank\Onepay\ShoppingCart;
use Transbank\Onepay\Item;
use Transbank\Onepay\Transaction;
use \Transbank\Onepay\Exceptions\TransactionCreateException;
use \Transbank\Onepay\Exceptions\TransbankException;

class OnepayTransactionModuleFrontController extends ModuleFrontController
{
    public function initContent() {
        parent::initContent();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            OnepayBase::setSharedSecret(Configuration::get('ONEPAY_SHARED_SECRET', null));
            OnepayBase::setApiKey(Configuration::get('ONEPAY_APIKEY', null));
            OnepayBase::setCurrentIntegrationType(Configuration::get('ONEPAY_ENDPOINT', null));
            
            $ps_cart = $this->context->cart;

            $ps_products = $ps_cart->getProducts(true);

            $ps_summary = $ps_cart->getSummaryDetails();
            $ps_shipping_price = intval($ps_summary['total_shipping']);

            $carro = new ShoppingCart();

            foreach($ps_products as $product) {
                $nombre = strval($product['name']);
                $cantidad = intval($product['cart_quantity']);
                $precio = intval($product['price_wt']);

                $item = new Item($nombre, $cantidad, $precio);
                $carro->add($item);
            }

            if ($ps_shipping_price != 0) {
                $item = new Item("Costo por envio", 1, $ps_shipping_price);
                $carro->add($item);
            }

            try {
                $transaction = Transaction::create($carro);
                $this->ajaxDie(json_encode([
                    'occ' => $transaction->getOcc(),
                    'ott' => $transaction->getOtt(),
                    'externalUniqueNumber' => $transaction->getExternalUniqueNumber(),
                    'qrCodeAsBase64' => $transaction->getQrCodeAsBase64(),
                    'issuedAt' => $transaction->getIssuedAt(),
                    'signature' => $transaction->getSignature(),
                    'amount' => $carro->getTotal()
                ]));

            } catch (TransbankException $transbank_exception) {
                $msg =  $transbank_exception->getMessage();
                PrestaShopLogger::addLog("Creación de Transacción fallida: ".$msg, 3, null, null, null, true, null);
                $this->ajaxDie(json_encode([
                    'success' => false
                ]));
                throw new TransactionCreateException($msg);
            }

        } else {
            $this->ajaxDie(json_encode([
                'success' => false
            ]));
        }
    }
}