<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Onepay extends PaymentModule {

    protected $config_form = false;

    public function __construct() {

        $this->name = 'onepay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Transbank';
        $this->need_instance = 0;

        $this->controllers = array('payment','transaction', 'commit', 'diagnostic');

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Onepay');
        $this->description = $this->l('¡Paga con Onepay! Podrás comprar con tus tarjetas de crédito escaneando el código QR, o ingresando el código de compra.');

        $this->limited_countries = array('CL');

        $this->limited_currencies = array('CLP');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install() {

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        Configuration::updateValue('ONEPAY_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('payment') &&
            $this->registerHook('moduleRoutes') &&
            $this->registerHook('header') &&
            $this->registerHook('paymentReturn');
    }

    public function uninstall() {
        Configuration::deleteByName('ONEPAY_LIVE_MODE');
        return parent::uninstall();
    }

    public function hookDisplayHeader() {    
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        return '<script type="text/javascript"> ' .
        'window.transaction_url="' . $this->context->link->getModuleLink("onepay", "transaction", array(), null, null, null, true) . '";' .
        'window.commit_url="' . $this->context->link->getModuleLink("onepay", "commit", array(), null, null, null, true) . '";' .
        '</script>';
    }

    public function hookModuleRoutes() {
        require_once __DIR__.'/vendor/autoload.php'; // This way you can autoload dependencies on all your custom classes
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params) {

        if ($this->active == false)
            return;

        $nameOrderRef = isset($params['order']) ? 'order' : 'objOrder';

        $order = $params[$nameOrderRef];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR'))
            $this->smarty->assign('status', 'ok');

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'shop_name' => $this->context->shop->name,
            'info' => json_decode(current(Message::getMessagesByOrderId($order->id, true))['message']),
            'total' => Tools::displayPrice($order->getOrdersTotalPaid(), new Currency($order->id_currency), false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

        /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params) {

        if (!$this->active) {
            return;
        }

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false)
            return false;

        Context::getContext()->smarty->assign(array(
            'logo' => 'https://www.transbankdevelopers.cl/public/library/img/img_onepay.png',
            'title' => 'Onepay'
        ));
        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentOptions($params) {
        if (!$this->active || !Configuration::get('ONEPAY_LIVE_MODE', false) || !Configuration::get('ONEPAY_APIKEY', null) || !Configuration::get('ONEPAY_APIKEY', null)) {
            return;
        }
        $onepayOption = new PaymentOption();
        $onepayOption->setCallToActionText($this->l('Pagar con Onepay'))
                      //->setModuleName('tbk-onepay')
                      ->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), null, null, null, true));
        return [$onepayOption];
    }

    /**
     * Load the configuration form
     */
    public function getContent() {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitOnepayModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign(array('module_dir' => $this->_path, 'diagnostic_url' => $this->context->link->getModuleLink($this->name, 'diagnostic', array(), null, null, null, true)));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $this->renderForm().$output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm() {

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOnepayModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm() {

        $options = array(
            array(
              'id_option' => "TEST",
              'name' => $this->l('Integración')
            ),
            array(
              'id_option' => "LIVE",
              'name' => $this->l('Producción')
            ),
          );

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activar Onepay'),
                        'name' => 'ONEPAY_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Activa o desactiva el medio de pago'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activado')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Desactivado')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Ingresa el APIKey entregada'),
                        'name' => 'ONEPAY_APIKEY',
                        'label' => $this->l('APIKey'),
                    ),
                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Ingresa la Shared Secret'),
                        'name' => 'ONEPAY_SHARED_SECRET',
                        'label' => $this->l('Shared Secret'),
                    ),
                    array(
                        'col' => 5,
                        'type' => 'select',
                        'required' => true,
                        'prefix' => '<i class="icon icon-cloud"></i>',
                        'desc' => $this->l('Selecciona el ambiente de conexión'),
                        'name' => 'ONEPAY_ENDPOINT',
                        'label' => $this->l('Endpoint'),
                        'options' => array(
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name'
                          )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues() {
        return array(
            'ONEPAY_LIVE_MODE' => Configuration::get('ONEPAY_LIVE_MODE', true),
            'ONEPAY_APIKEY' => Configuration::get('ONEPAY_APIKEY', null),
            'ONEPAY_SHARED_SECRET' => Configuration::get('ONEPAY_SHARED_SECRET', null),
            'ONEPAY_ENDPOINT' => Configuration::get('ONEPAY_ENDPOINT', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess() {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader() {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader() {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
