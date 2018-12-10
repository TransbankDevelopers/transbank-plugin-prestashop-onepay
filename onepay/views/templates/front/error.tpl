{extends file='page.tpl'}
{block name='page_content'}
<div class="box cheque-box">
	<h1 align="center" style="color: orange;">Tu compra no pudo ser realizada</h1>
    <br/>
	{if isset(Context::getContext()->cart)}<p>Orden de compra: <b>{Context::getContext()->cart->id}</b></p>{/if}
	<br>
	<a href="{$link->getPageLink('order', null, null, 'step=3')}" class="btn btn-danger">{l s='Vuelve e intenta nuevamente' mod='peinau'} </a>
</div>
{/block}