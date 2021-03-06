/**
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
    })(false, document, "https://unpkg.com/transbank-onepay-frontend-sdk@1.5/lib/merchant.onepay.min.js",
        "script",window, function () {

            $('#onepay_place_order').unbind('click').click(function(e) {

                $.getJSON(transaction_url + '?config=true', function(config) {

                    var options = {
                        endpoint: transaction_url,
                        callbackUrl: commit_url,
                        transactionDescription: config.transactionDescription || ''
                    };

                    if (typeof prestashop !== 'undefined') {
                        if (prestashop && prestashop.shop && prestashop.shop.logo) {
                            options.commerceLogo = window.location.origin + prestashop.shop.logo;
                        }
                    }

                    Onepay.checkout(options);
                });
                return false;
            });
        });
})( jQuery );
