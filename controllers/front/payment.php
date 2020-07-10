<?php
/**
* 2007-2015 PrestaShop
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
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

/**
 * @since 1.5.0
 */
class PaytpvPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_top  = false;

    
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->display_top = false;
        $this->display_menu = false;


        parent::initContent();

        $paytpv = $this->module;

        $this->context->smarty->assign('msg_paytpv', "");
        
        $msg_paytpv = "";

        $this->context->smarty->assign('msg_paytpv', $msg_paytpv);
        

        // Valor de compra
        $id_currency = (int)(Configuration::get('PS_CURRENCY_DEFAULT'));

        $currency = new Currency((int)($id_currency));
        // $importe = number_format(Context::getContext()->cart->getOrderTotal(true, Cart::BOTH)*100, 0, '.', '');
        $importe_tienda = Context::getContext()->cart->getOrderTotal(true, Cart::BOTH);

        // $paytpv_order_ref = str_pad(Context::getContext()->cart->id, 8, "0", STR_PAD_LEFT);
        $ssl = Configuration::get('PS_SSL_ENABLED');
        $values = array(
            'id_cart' => (int)Context::getContext()->cart->id,
            'key' => Context::getContext()->customer->secure_key
        );

        $active_suscriptions = (int)(Configuration::get('PAYTPV_SUSCRIPTIONS'));

        $saved_card = PaytpvCustomer::getCardsCustomer($this->context->customer->id);
        $index = 0;
        foreach ($saved_card as $key => $val) {
            $values_aux = array_merge($values, array("TOKEN_USER"=>$val["TOKEN_USER"]));
            $saved_card[$key]['url'] =
            Context::getContext()->link->getModuleLink($this->module->name, 'capture', $values_aux, $ssl);

            $index++;
        }
        $saved_card[$index]['url'] = 0;

        $cart = Context::getContext()->cart;
        $datos_pedido = $this->module->terminalCurrency($cart);
        $idterminal = $datos_pedido["idterminal"];
        $idterminal_ns = $datos_pedido["idterminal_ns"];
        $jetid = $datos_pedido["jetid"];
        $jetid_ns = $datos_pedido["jetid_ns"];

        if ($idterminal>0) {
            $secure_pay = $paytpv->isSecureTransaction($idterminal, $importe_tienda, 0)?1:0;
        } else {
            $secure_pay = $paytpv->isSecureTransaction($idterminal_ns, $importe_tienda, 0)?1:0;
        }

        // Miramos a ver por que terminal enviamos la operacion
        if ($secure_pay) {
            $jetid_sel = $jetid;
        } else {
            $jetid_sel = $jetid_ns;
        }


        $newpage_payment = (int)(Configuration::get('PAYTPV_NEWPAGEPAYMENT'));
        $paytpv_integration = (int)(Configuration::get('PAYTPV_INTEGRATION'));

        print "Tx:" . $paytpv->iframe_height;
        exit;
        $iframe_height = $paytpv->iframe_height;


        $disableoffersavecard = Configuration::get('PAYTPV_DISABLEOFFERSAVECARD');
        $remembercardunselected = Configuration::get('PAYTPV_REMEMBERCARDUNSELECTED');

        $this->context->smarty->assign('newpage_payment', $newpage_payment);
        $this->context->smarty->assign('iframe_height', $iframe_height);
        $this->context->smarty->assign('paytpv_integration', $paytpv_integration);

        $this->context->smarty->assign('jet_id', $jetid_sel);

        $language_data = explode("-", $this->context->language->language_code);
        $language = $language_data[0];

        $this->context->smarty->assign('jet_lang', $language);

        $this->context->smarty->assign(
            'paytpv_jetid_url',
            Context::getContext()->link->getModuleLink($this->module->name, 'capture', array(), $ssl)
        );

        $tmpl_vars = array();
        $tmpl_vars['capture_url'] =
        Context::getContext()->link->getModuleLink($this->module->name, 'capture', $values, $ssl);

        $this->context->smarty->assign('active_suscriptions', $active_suscriptions);
        $this->context->smarty->assign('saved_card', $saved_card);
        $this->context->smarty->assign('commerce_password', $this->module->commerce_password);
        $this->context->smarty->assign('id_cart', Context::getContext()->cart->id);
        
        $this->context->smarty->assign('base_dir', __PS_BASE_URI__);

        
        $tmpl_vars = array_merge(
            array(
            'this_path' => $this->module->getPath())
        );
        $this->context->smarty->assign($tmpl_vars);
        
        
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/payment.css', 'all');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/fullscreen.css', 'all');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/paytpv.js');


        $this->context->smarty->assign('total_amount', $importe_tienda);
        $this->context->smarty->assign('currency_symbol', $currency->sign);

        $this->context->smarty->assign('disableoffersavecard', $disableoffersavecard);
        $this->context->smarty->assign('remembercardunselected', $remembercardunselected);


        $this->context->smarty->assign('paytpv_iframe', $this->module->paytpvIframeURL());

        // Bankstore JET
        if ($paytpv_integration==1) {
            $this->context->smarty->assign('js_code', '');
        }
        

        $this->setTemplate('../hook/payment_bsiframe.tpl');
    }
}
