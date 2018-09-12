/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

(function( $ ) {
	'use strict';

    (function (o, n, e, p, a, y) {
        var s = n.createElement(p);
        s.type = "text/javascript";
        s.src = e;
        s.onload = s.onreadystatechange = function () {
            if (!o && (!s.readyState
                || s.readyState === "loaded")) {
                y();
            }
        };

        var t = n.getElementsByTagName("script")[0];
        p = t.parentNode;
        p.insertBefore(s, t);
    })(false, document, "https://cdn.rawgit.com/TransbankDevelopers/transbank-sdk-js-onepay/v1.4.3/lib/merchant.onepay.js",
        "script",window, function () {
            console.log("Onepay JS library successfully loaded.");

            $('#payment-confirmation > .ps-shown-by-js > button').click(function(e) {
                var myPaymentMethodSelected = $('.payment-options').find("input[data-module-name='tbk-onepay']").is(':checked');

                if (myPaymentMethodSelected){
                    var options = {
                        endpoint: transaction_url,
                        callbackUrl: commit_url
                        };

                    if (prestashop.shop.logo) {
                        options.commerceLogo = window.location.origin + prestashop.shop.logo;
                    }
                    Onepay.checkout(options);
                }
                 return false;
            });
        });
})( jQuery );
