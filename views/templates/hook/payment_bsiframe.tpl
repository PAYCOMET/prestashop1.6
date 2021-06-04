{*
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
    *}

    {capture name=path}
    <a href="{$link->getPageLink('order', true)|escape:'htmlall':'UTF-8'}">{l s='Cart' mod='paytpv'}</a>
    <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8':FALSE}</span>
    {l s='Pay with Card' mod='paytpv'}</a>
    {/capture}

    <div class="row">
        <input type="hidden" name="newpage_payment" id="newpage_payment" value="{$newpage_payment|escape:'htmlall':'UTF-8':FALSE}">
        <input type="hidden" name="paytpv_integration" id="paytpv_integration" value="{$newpage_payment|escape:'htmlall':'UTF-8':FALSE}">
        <input type="hidden" name="id_cart" id="id_cart"  value="{$id_cart|escape:'htmlall':'UTF-8':FALSE}">
        <input type="hidden" name="paytpv_iframe_aux" id="paytpv_iframe_aux"  value="">


        {if ($newpage_payment==1)}
            <div class="col-xs-12">
        {else}
            <div class="col-xs-12">
        {/if}
        <div class="paytpv">

                {if ($paytpv_integration==1)}
                    <form action="{$paytpv_jetid_url|escape:'htmlall':'UTF-8'}" method="POST" class="paytpv_jet" id="paycometPaymentForm" style="clear:left;">
                {/if}
                
                <div class="paytpv_title">
                    <a href="http://www.paycomet.com" target="_blank"><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/paytpv_logo.svg" width="135"></a>
                    {l s='Pay with Card' mod='paytpv'}
                </div>
                {if ($msg_paytpv!="")}
                <p>
                    <span class="message">{$msg_paytpv|escape:'htmlall':'UTF-8':FALSE}</span>
                </p>
                {/if}
                {if ($active_suscriptions)}
                    {include file='modules/paytpv/views/templates/hook/inc_payment_suscription.tpl'}
                {/if}

                {if ($newpage_payment==1)}
                    <div class="operation_data">
                        <div class="pad">
                        <h3>{l s='Credit Card Operation' mod='paytpv'}</h3>
                        <div style="display:inline-table;">
                            <div class="operation">
                                <h4 class="cost_num">{l s='Total Amount' mod='paytpv'}:<b>{$total_amount|escape:'htmlall':'UTF-8':FALSE} {$currency_symbol|escape:'htmlall':'UTF-8':FALSE}</b></h4>
                            </div>
                        </div>
                        </div>
                </div>
                {/if}

                <div id="saved_cards" style="display:none">
                    {include file='modules/paytpv/views/templates/hook/inc_payment_cards.tpl'}

                    {if (sizeof($saved_card)>1) || ($newpage_payment==2)}
                        <div id="button_directpay" style="margin-top:10px;">
                            <button id="exec_directpay" href="#" class="btn btn-primary button button-medium center-block exec_directpay paytpv_pay">
                                <span>{l s='Pay' mod='paytpv'}<i class="icon-chevron-right right"></i></span>
                            </button>
                            <img id='clockwait' style="display:none" src="{$base_dir|escape:'htmlall':'UTF-8'}modules/paytpv/views/img/clockpayblue.gif"></img>
                        </div>
                    {/if}
                </div>

                {if (!$disableoffersavecard==1)}
                    {include file='modules/paytpv/views/templates/hook/inc_payment_savecards.tpl'}
                {/if}

                <br class="clear"/>


            <div class="payment_module paytpv_iframe" style="display:none">

                    {if ($newpage_payment==1)}
                        <div class="info_paytpv">
                        <h5>{l s='The input data is stored on servers in PAYCOMET company with PCI / DSS Level 1 certification, making payments 100% secure.' mod='paytpv'}</h5>
                        </div>
                    {/if}


                    {if ($newpage_payment<2)}
                        {if ($paytpv_integration==0)}
                            <p id='ajax_loader' style="display:none">
                                <img id='ajax_loader' src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/clockpayblue.gif"></img>
                                {l s='Loading payment form...' mod='paytpv'}
                            </p>
                            <iframe id="paytpv_iframe" src="{$paytpv_iframe|escape:'htmlall':'UTF-8':FALSE}" name="paytpv" style="width: 98%; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: {$iframe_height|escape:'htmlall':'UTF-8'}px;" marginheight="0" marginwidth="0" scrolling="no"></iframe>
                        {else}

                            {include file="modules/paytpv/views/templates/hook/inc_payment_jetIframe.tpl"}
                        {/if}

                        {if ($newpage_payment==1)}
                        <div class="paytpv_footer">
                            <div class="paytpv_wrapper mobile">
                                <div class="footer_line">
                                    <div class="footer_logo">
                                        <a href="https://www.paycomet.com" target="_blank">
                                        <img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/paytpv_logo.svg">
                                        </a>
                                    </div>
                                    <ul class="payment_icons">
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/visa.png" alt="Visa"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/visa_electron.png" alt="Visa Electron"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/mastercard.png" alt="Mastercard"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/maestro.png" alt="Maestro"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/amex.png" alt="American Express"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/jcb.png" alt="JCB card"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/veryfied_by_visa.png" alt="Veryfied by Visa"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/mastercard_secure_code.png" alt="Mastercard Secure code"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/pci.png" alt="PCI"></li>
                                        <li><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/thawte.png" alt="Thawte"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    {/if}
                {/if}
            </div>

             {if ($paytpv_integration==1)}
                    </form>
                {/if}

        </div>
    </div>

    <div style="display: none;">
        <div id="directpay" style="overflow:auto;">
            <form name="pago_directo" id="pago_directo" action="" method="post"></form>
        </div>
    </div>

    {foreach $apmsUrls as $key => $apm}
        {if $apm != false}
            <div class="col-xs-12">
                <p class="payment_module">
                    <a class="paycometPayments" href="{$apm['url']|escape:'htmlall':'UTF-8':FALSE}" rel="nofollow" style="background: url({$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/apms/{$apm['img_name']|escape:'htmlall':'UTF-8':FALSE}.svg) 15px 12px no-repeat #fbfbfb; ">
                        {l s='Pay with ' mod='paytpv'} {$apm['method_name']|escape:'htmlall':'UTF-8':FALSE}
                    </a>
                    {if $apm['html_code'] != ''}
                        {$apm['html_code']}
                    {/if}
                </p>
            </div>

        {/if}
    {/foreach}

    <input type="hidden" name="paytpv_module" id="paytpv_module" value="{$link->getModuleLink('paytpv', 'actions',[], true)|escape:'htmlall':'UTF-8'}">

    <script type="text/javascript">
    paytpv_initialize();
    </script>
</div>
