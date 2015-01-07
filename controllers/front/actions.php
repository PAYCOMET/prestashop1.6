<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class PaytpvActionsModuleFrontController extends ModuleFrontController
{
	/**
	 * @var int
	 */
	
	public function init()
	{

		
	}

	public function postProcess()
	{
		
		if (Tools::getValue('process') == 'removeCard')
			$this->processRemoveCard();

		if (Tools::getValue('process') == 'cancelSuscription')
			$this->processCancelSuscription();

		if (Tools::getValue('process') == 'addCard')
			$this->processAddCard();

		if (Tools::getValue('process') == 'suscribe')
			$this->processSuscribe();
		
		exit;
	}

	/**
	 * Remove card
	 */
	public function processRemoveCard()
	{
		$paytpv = $this->module;

		if ($paytpv->removeCard(Tools::getValue('paytpv_cc')))
			die('0');
		die('1');
	}

	/**
	 * Remove suscription
	 */
	public function processCancelSuscription()
	{
		$paytpv = $this->module;
		if ($paytpv->cancelSuscription(Tools::getValue('id_suscription')))
			die('0');
		die('1');
	}

	/**
	 * add Card
	 */
	public function processAddCard()
	{
		global $cookie;
		$paytpv = $this->module;

		$id_cart = Tools::getValue('id_cart');

		$cart = new Cart($id_cart);

		$paytpv_agree = Tools::getValue('paytpv_agree');
		$suscripcion = 0;
		$periodicity = 0;
		$cycles = 0;

		// Valor de compra				
		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));

		$currency = new Currency(intval($id_currency));		
		$importe = "100";//number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency)*100, 0, '.', '');

		$ps_language = new Language(intval($cookie->id_lang));

		$values = array(
			'id_cart' => $cart->id,
			'key' => Context::getContext()->customer->secure_key
		);

		$ssl = Configuration::get('PS_SSL_ENABLED');

		$URLOK=$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'url',$values,$ssl);

		$paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT) . date('is');



		$arrReturn = array();
		$arrReturn["error"] = 1;
		if ($paytpv->save_paytpv_order_info((int)$this->context->customer->id,$cart->id,$paytpv_agree,$suscripcion,$periodicity,$cycles)){
			$OPERATION = "1";
			// Cálculo Firma
			$signature = md5($paytpv->clientcode.$paytpv->term.$OPERATION.$paytpv_order_ref.$importe.$currency->iso_code.md5($paytpv->pass));
			$fields = array
			(
				'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
				'MERCHANT_TERMINAL' => $paytpv->term,
				'OPERATION' => $OPERATION,
				'LANGUAGE' => $ps_language->iso_code,
				'MERCHANT_MERCHANTSIGNATURE' => $signature,
				'MERCHANT_ORDER' => $paytpv_order_ref,
				'MERCHANT_AMOUNT' => $importe,
				'MERCHANT_CURRENCY' => $currency->iso_code,
				'URLOK' => $URLOK,
				'URLKO' => $URLKO,
				'3DSECURE' => $paytpv->tdfirst
			);
			$query = http_build_query($fields);
			$url_paytpv = "https://secure.paytpv.com/gateway/bnkgateway.php?".$query;
			$arrReturn["error"] = 0;
			$arrReturn["url"] = $url_paytpv;
		}
		
		print json_encode($arrReturn);
	}


	/**
	 * add Card
	 */
	public function processSuscribe()
	{
		global $cookie;
		$paytpv = $this->module;

		$id_cart = Tools::getValue('id_cart');

		$cart = new Cart($id_cart);

		$paytpv_agree = Tools::getValue('paytpv_agree');
		$suscripcion = Tools::getValue('paytpv_suscripcion');
		$periodicity = Tools::getValue('paytpv_periodicity');
		$cycles = Tools::getValue('paytpv_cycles');

		// Valor de compra				
		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));

		$currency = new Currency(intval($id_currency));		
		$importe = "100";//number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $currency)*100, 0, '.', '');

		$ps_language = new Language(intval($cookie->id_lang));

		$values = array(
			'id_cart' => $cart->id,
			'key' => Context::getContext()->customer->secure_key
		);

		$ssl = Configuration::get('PS_SSL_ENABLED');

		$URLOK=$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'url',$values,$ssl);

		$paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT) . date('is');

		$arrReturn = array();
		$arrReturn["error"] = 1;
		if ($paytpv->save_paytpv_order_info((int)$this->context->customer->id,$cart->id,$paytpv_agree,$suscripcion,$periodicity,$cycles)){
			$OPERATION = "9";
			$subscription_stratdate = date("Ymd");
			$susc_periodicity = $periodicity;
			$subs_cycles = $cycles;

			// Si es indefinido, ponemos como fecha tope la fecha + 10 años.
			if ($subs_cycles==0)
				$subscription_enddate = date("Y")+5 . date("m") . date("d");
			else{
				// Dias suscripcion
				$dias_subscription = $subs_cycles * $susc_periodicity;
				$subscription_enddate = date('Ymd', strtotime("+".$dias_subscription." days"));
			}
			// Cálculo Firma
			
			$signature = md5($paytpv->clientcode.$paytpv->term.$OPERATION.$paytpv_order_ref.$importe.$currency->iso_code.md5($paytpv->pass));
			$fields = array
			(
				'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
				'MERCHANT_TERMINAL' => $paytpv->term,
				'OPERATION' => $OPERATION,
				'LANGUAGE' => $ps_language->iso_code,
				'MERCHANT_MERCHANTSIGNATURE' => $signature,
				'MERCHANT_ORDER' => $paytpv_order_ref,
				'MERCHANT_AMOUNT' => $importe,
				'MERCHANT_CURRENCY' => $currency->iso_code,
				'SUBSCRIPTION_STARTDATE' => $subscription_stratdate, 
				'SUBSCRIPTION_ENDDATE' => $subscription_enddate,
				'SUBSCRIPTION_PERIODICITY' => $susc_periodicity,
				'URLOK' => $URLOK,
				'URLKO' => $URLKO,
				'3DSECURE' => $paytpv->tdfirst
			);
			$query = http_build_query($fields);
			$url_paytpv = "https://secure.paytpv.com/gateway/bnkgateway.php?".$query;
			$arrReturn["error"] = 0;
			$arrReturn["url"] = $url_paytpv;
		}
		
		print json_encode($arrReturn);
	}
}