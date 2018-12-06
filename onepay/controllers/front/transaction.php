<?php

use Transbank\Onepay\OnepayBase;
use Transbank\Onepay\ShoppingCart;
use Transbank\Onepay\Item;
use Transbank\Onepay\Transaction;
use Transbank\Onepay\Options;
use \Transbank\Onepay\Exceptions\TransactionCreateException;
use \Transbank\Onepay\Exceptions\TransbankException;

class OnepayTransactionModuleFrontController extends ModuleFrontController
{
    public function initContent() {
        parent::initContent();

        if (isset($_GET['config']) && $_GET['config'] == 'true') {

            $ps_cart = $this->context->cart;
            $ps_products = $ps_cart->getProducts(true);

            $transactionDescription = '';

            if (count($ps_products) == 1) {
                $transactionDescription = strval($ps_products[0]['name']);
            }

            $response = array(
                'transactionDescription' => $transactionDescription
            );

            $this->ajaxDie(json_encode($response));
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $channel = isset($_POST['channel']) ? $_POST['channel'] : null;
            $endpoint = Configuration::get('ONEPAY_ENDPOINT', null);
            $apiKey = Configuration::get('ONEPAY_APIKEY', null);
            $sharedSecret = Configuration::get('ONEPAY_SHARED_SECRET', null);
            $callbackUrl = Context::getContext()->link->getModuleLink('onepay', 'commit', array(), true);

            OnepayBase::setApiKey($apiKey);
            OnepayBase::setSharedSecret($sharedSecret);
            OnepayBase::setCurrentIntegrationType($endpoint);
            OnepayBase::setCallbackUrl($callbackUrl);
            
            $ps_cart = $this->context->cart;

            $ps_products = $ps_cart->getProducts(true);

            $ps_summary = $ps_cart->getSummaryDetails();
            $ps_shipping_price = (int) round($ps_summary['total_shipping']);

            $carro = new ShoppingCart();

            foreach($ps_products as $product) {
                $nombre = strval($product['name']);
                $cantidad = intval($product['cart_quantity']);
                $precio = (int) round($product['price_wt']);

                $item = new Item($nombre, $cantidad, $precio);
                $carro->add($item);
            }

            if ($ps_shipping_price != 0) {
                $item = new Item("Costo por envio", 1, $ps_shipping_price);
                $carro->add($item);
            }

            try {

                $options = new Options();

                if ($endpoint == "LIVE") {
                    $options->setAppKey("C7EE0F59-9353-408B-B81C-E1E8F08305FF");
                }

                $transaction = Transaction::create($carro, $channel, $options);
                $this->ajaxDie(json_encode([
                    'occ' => $transaction->getOcc(),
                    'ott' => $transaction->getOtt(),
                    'externalUniqueNumber' => $transaction->getExternalUniqueNumber(),
                    'qrCodeAsBase64' => $transaction->getQrCodeAsBase64(),
                    'issuedAt' => $transaction->getIssuedAt(),
                    'signature' => $transaction->getSignature(),
                    'amount' => $carro->getTotal(),
                    'callbackUrl' => $callbackUrl
                ]));

            } catch (TransbankException $transbank_exception) {
                $msg = $transbank_exception->getMessage();
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