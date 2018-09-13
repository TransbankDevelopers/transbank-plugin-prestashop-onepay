{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Tu orden está completa.' mod='onepay'}</h3>
<p>

	<br />- {l s='Monto' mod='onepay'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Referencia' mod='onepay'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>

	<br />- {l s='OCC' mod='onepay'} : <span class="reference"><strong>{$info->occ|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Número de carro' mod='onepay'} : <span class="reference"><strong>{$info->externalUniqueNumber|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Código de autorización' mod='onepay'} : <span class="reference"><strong>{$info->authorizationCode|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Orden de compra' mod='onepay'} : <span class="reference"><strong>{$info->buyOrder|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Estado' mod='onepay'} : <span class="reference"><strong>{$info->description|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Monto de compra' mod='onepay'} : <span class="reference"><strong>{$info->amount|escape:'html':'UTF-8'}</strong></span>

	{if $info->installmentsNumber eq 1}
	<br />- {l s='Numero de cuotas' mod='onepay'} : <span class="reference"><strong>Sin cuotas</strong></span>
	{else}
	<br />- {l s='Numero de cuotas' mod='onepay'} : <span class="reference"><strong>{$info->installmentsNumber|escape:'html':'UTF-8'}</strong></span>
	<br />- {l s='Monto cuota' mod='onepay'} : <span class="reference"><strong>{$info->installmentsAmount|escape:'html':'UTF-8'}</strong></span>
	{/if}

	<br />- {l s='Fecha' mod='onepay'} : <span class="reference"><strong>{$info->issuedAt|escape:'html':'UTF-8'}</strong></span>

	<br /><br />{l s='Un email ha sido enviado con esta información.' mod='onepay'}
	<br /><br />{l s='Si tienes preguntas, comentarios o dudas, por favor contacta nuestro' mod='onepay'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='equipo de ayuda.' mod='onepay'}</a>
</p>
{else}
<h3>{l s='Tu orden no ha sido aceptada.' mod='onepay'}</h3>
<p>
	<br />- {l s='Referencia' mod='onepay'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Por favor, intenta nuevamente.' mod='onepay'}
	<br /><br />{l s='Si tienes preguntas, comentarios o dudas, por favor contacta nuestro' mod='onepay'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='equipo de ayuda.' mod='onepay'}</a>
</p>
{/if}
<hr />