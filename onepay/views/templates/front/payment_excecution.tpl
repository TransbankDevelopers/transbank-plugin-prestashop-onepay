{extends file='page.tpl'}
{block name="content"}
<div class="box cheque-box">
    <h3 class="page-subheading">Pago por Onepay</h3>
    <p>Se realizara la compra a traves de Onepay por un total de <b>$ {$amount}</b></p>
</div>
<p class="cart_navigation clearfix" id="cart_navigation">
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default">
        <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='onepay'}
    </a>
    <button type="submit" class="btn btn-primary" id="onepay_place_order">
        <span>Pagar<i class="icon-chevron-right right"></i></span>
    </button>
</p>
{/block}
