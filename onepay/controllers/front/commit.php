<?php

use Transbank\Onepay\OnepayBase;
use Transbank\Onepay\ShoppingCart;
use Transbank\Onepay\Item;
use Transbank\Onepay\Transaction;
use Transbank\Onepay\Options;
use \Transbank\Onepay\Exceptions\TransactionCreateException;
use \Transbank\Onepay\Exceptions\TransbankException;

class OnepayCommitModuleFrontController extends ModuleFrontController
{
    public function postProcess() {

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $endpoint = Configuration::get('ONEPAY_ENDPOINT', null);
            
            OnepayBase::setSharedSecret(Configuration::get('ONEPAY_SHARED_SECRET', null));
            OnepayBase::setApiKey(Configuration::get('ONEPAY_APIKEY', null));
            OnepayBase::setCurrentIntegrationType($endpoint);

            $externalUniqueNumber = Tools::getValue('externalUniqueNumber');
            $occ = Tools::getValue('occ');

            try {
                $options = new Options();

                if ($endpoint == "LIVE") {
                    $options->setAppKey("C7EE0F59-9353-408B-B81C-E1E8F08305FF");
                }

                $transactionCommitResponse = Transaction::commit($occ, $externalUniqueNumber, $options);

                $cart_id=Context::getContext()->cart->id;
                $secure_key=Context::getContext()->customer->secure_key;
                $cart = new Cart((int)$cart_id);
                $customer = new Customer((int)$cart->id_customer);

                $full_response = [];
                $full_response['occ'] = $transactionCommitResponse->getOcc();
                $full_response['externalUniqueNumber'] = $externalUniqueNumber;
                $full_response['authorizationCode'] = $transactionCommitResponse->getOcc();
                $full_response['buyOrder'] = $transactionCommitResponse->getBuyOrder();
                $full_response['description'] = $transactionCommitResponse->getDescription();
                $full_response['amount'] = $transactionCommitResponse->getAmount();
                $full_response['installmentsNumber'] = $transactionCommitResponse->getInstallmentsNumber();
                $full_response['installmentsAmount'] = $transactionCommitResponse->getInstallmentsAmount();
                $full_response['issuedAt'] = $transactionCommitResponse->getIssuedAt();

                if ($transactionCommitResponse->getDescription() == "OK"){
                    $payment_status = Configuration::get('PS_OS_PAYMENT');
                    $message = json_encode($full_response);

                    $module_name = $this->module->displayName;
                    $currency_id = (int)Context::getContext()->currency->id;
                    $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);

                    $order_id = Order::getOrderByCartId((int)$cart->id);
                    if ($order_id && ($secure_key == $customer->secure_key)) {
                        $module_id = $this->module->id;
                        Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$secure_key);
                    } else {
                        PrestaShopLogger::addLog("Confirmación de transacción fallida: ".$message, 3, null, null, null, true, null);
                        return $this->setTemplate('module:onepay/views/templates/front/error.tpl');
                    }
                } else {
                    return $this->setTemplate('module:onepay/views/templates/front/error.tpl');
                }
            }
            catch (TransbankException $transbank_exception) {
                PrestaShopLogger::addLog("Confirmación de transacción fallida: ".$transbank_exception->getMessage(), 3, null, null, null, true, null);
                return $this->setTemplate('module:onepay/views/templates/front/error.tpl');
            }

        } else {
            $this->ajaxDie(json_encode([
                'success' => false
            ]));
        }
    }
}