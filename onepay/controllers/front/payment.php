<?php
require_once(dirname(__FILE__).'../../../../../config/config.inc.php');
if (!defined('_PS_VERSION_')) exit;

require_once(_PS_MODULE_DIR_.'onepay/libs/OnepayUtils.php');

class OnepayPaymentModuleFrontController extends ModuleFrontController {

    public function initContent() {

        $this->ssl = true;
        $this->display_column_left = false;
        parent::initContent();

        $cart = $this->context->cart;
        $amount = $cart->getOrderTotal(true, Cart::BOTH);

        Context::getContext()->smarty->assign(array(
            'amount' => $amount
        ));

        if (OnepayUtils::isPrestashop_1_6()) {
            $this->setTemplate('payment_excecution_1.6.tpl');
        } else {
            $this->setTemplate('module:onepay/views/templates/front/payment_excecution.tpl');
        }
    }
}
