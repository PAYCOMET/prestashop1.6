<?php

/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 13573 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
/**
 * @since 1.5.0
 */

include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
class PaytpvCaptureModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $ssl = true;
   
    /**
     * @see FrontController::initContent()
     */
   	public function initContent()
    {
    	global $smarty,$cookie,$cart;

    	parent::initContent();
        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));  

		$paytpv = $this->module;

		$msg_paytpv_contrasena = "";
		// Verificar contraseña usuario.
        if (!$paytpv->validPassword($this->context->cart->id_customer,Tools::getValue('password'))){
        	$this->setTemplate('payment_fail.tpl');
        	$msg_paytpv_contrasena = $paytpv->l('Contraseña invalida');
        	$smarty->assign('msg_paytpv_contrasena',$msg_paytpv_contrasena);
        	return;
        }

        $data = $paytpv->getDataToken($_GET["TOKEN_USER"]);

		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency = new Currency(intval($id_currency));
		$importe = number_format(Tools::convertPrice($this->context->cart->getOrderTotal(true, 3), $currency)*100, 0, '.', '');
		
		$paytpv->save_paytpv_order_info((int)$this->context->customer->id,$this->context->cart->id,0,0,0,0);
		
		$terminales = Configuration::get('PAYTPV_TERMINALES');
		
		// Si el cliente solo tiene un terminal seguro, el segundo pago va siempre por seguro.
		// Si tiene un terminal NO Seguro ó ambos, el segundo pago siempre lo mandamos por NO Seguro
		
		// PAGO SEGURO
		if ($terminales==0){
			
			$ps_language = new Language(intval($cookie->id_lang));		
			$paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT) . date('is');

			$values = array(
				'id_cart' => (int)$this->context->cart->id,
				'key' => Context::getContext()->customer->secure_key
			);
			$ssl = Configuration::get('PS_SSL_ENABLED');
			$URLOK=$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'url',$values,$ssl);

		
			$OPERATION = "109"; //exec_purchase_token
			$signature = md5($paytpv->clientcode.$data["IDUSER"].$data['TOKEN_USER'].$paytpv->term.$OPERATION.$paytpv_order_ref.$importe.$currency->iso_code.md5($paytpv->pass));
	
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
					'IDUSER' => $data["IDUSER"],
					'TOKEN_USER' => $data['TOKEN_USER'],
					'3DSECURE' => $paytpv->tdfirst,
					'URLOK' => $URLOK,
					'URLKO' => $URLKO
				);
				
			$query = http_build_query($fields);

			$salida = "https://secure.paytpv.com/gateway/bnkgateway.php?".$query;

			header('Location: '.$salida);
			exit;
		}
		/* FIN AÑADIDO */
		
		
		$client = new WS_Client(
			array(
				'clientcode' => $paytpv->clientcode,
				'term' => $paytpv->term,
				'pass' => $paytpv->pass,
			)
		);
		
		
		$charge = $client->execute_purchase( $data['IDUSER'],$data['TOKEN_USER'],$this->context->currency->iso_code,$importe,$this->context->cart->id );

		if ( ( int ) $charge[ 'DS_RESPONSE' ] == 1 ) {
			$id_cart = (int)substr($charge[ 'DS_MERCHANT_ORDER'],0,8);
			$importe = number_format($charge[ 'DS_MERCHANT_AMOUNT']/ 100, 2);
			$result = $charge[ 'DS_ERROR_ID'];
			$transaction = array(
				'transaction_id' => $charge['DS_MERCHANT_AUTHCODE'],
				'result' => $result
			);
			$customer = new Customer((int) $this->context->cart->id_customer);
			// Registramos el pago
			$pagoRegistrado = $paytpv->validateOrder($id_cart, _PS_OS_PAYMENT_, $importe, $paytpv->displayName, NULL, $transaction, NULL, false, $customer->secure_key);
			$id_order = Order::getOrderByCartId(intval($id_cart));
			$paytpv->savePayTpvOrder($data['IDUSER'],$data['TOKEN_USER'],0,$customer->id,$id_order,$importe);
			$values = array(
				'id_cart' => $this->context->cart->id,
				'id_module' => (int)$this->module->id,
				'id_order' => $id_order,
				'key' => $this->context->customer->secure_key
			);
			Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation',$this->ssl,null,$values));
			return;
		}else{

			if (isset($reg_estado) && $reg_estado == 1)
			//se anota el pedido como no pagado
			class_registro::add($cart->id_customer, $this->context->cart->id, $importe, $charge[ 'DS_RESPONSE' ]);
		}
				
		
		$smarty->assign('base_dir',__PS_BASE_URI__);
        $this->setTemplate('payment_fail.tpl');

    }

}
