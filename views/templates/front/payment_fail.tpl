{capture name=path}{l s='Payment not completed' mod='paytpv'}{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Payment not completed' mod='paytpv'}</h2>


	{if isset($msg_paytpv_contrasena) && ($msg_paytpv_contrasena!="")}
	<img src="{$base_dir}img/admin/icon-cancel.png"/> &nbsp;&nbsp; {$msg_paytpv_contrasena}
	</tr>
	{else}
	<img src="{$base_dir}img/admin/icon-cancel.png"/>&nbsp;&nbsp;   
	{l s='Sorry. Your payment has not been completed. You can try again or choose another payment method. Remember that you can use cards attached to secure payment system Visa, called "Verified by Visa" or MasterCard, called "MasterCard SecureCode".'  mod='paytpv'}
	{/if}

<ul class="footer_links">    
	<li>    	
		<a href="{$link->getPageLink('my-account')}" title="{l s='Go to your account'  mod='paytpv'}">    		
			<img src="{$base_dir}img/admin/nav-user.gif" alt="{l s='Go to your account' mod='paytpv'}" class="icon" />&nbsp;{l s='Go to your account'  mod='paytpv'}    	
		</a>
	</li>
	<li>&nbsp;&nbsp;</li>    
	<li>    	
		<a href="{$link->getPageLink('order',false, NULL,'step=3')}" title="{l s='Choose payment method'  mod='paytpv'}">    		
			<img src="{$base_dir}img/admin/cart.gif" alt="{l s='Choose payment method' mod='paytpv'}" class="icon" />&nbsp;{l s='Choose payment method'  mod='paytpv'}    	
	    </a>    
	</li>    
	<li>&nbsp;&nbsp;</li>    
	<li>    	
		<a href="{$base_dir}">    		
			<img src="{$base_dir}img/admin/home.gif" alt="{l s='Home' mod='paytpv'}" class="icon" />&nbsp;{l s='Home'  mod='paytpv'}    	
		</a>    
	</li>
</ul>