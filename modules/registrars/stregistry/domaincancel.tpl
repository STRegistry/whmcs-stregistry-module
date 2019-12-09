<link href="{$WEB_ROOT}/templates/{$template}/css/domain-overview.css" rel="stylesheet">
<div class="container nopadding">
    <div class="row table-row ml-0 mr-0">
		{include file="$template/includes/sidebar.tpl"}
        <div class="tab-content col-md-9 col-sm-8">
            <div class="section-heading">
                <h2 class="page-title"><span>{$LANG.managing}</span> {$domain}</h2>
            </div>
            {if $registrarcustombuttonresult}
                <div class="alert alert-error">
                    <p><strong>{$LANG.moduleactionfailed}:</strong> {$registrarcustombuttonresult}</p>
                </div>
            {elseif $registrarcustombuttonresult=="success"}
                <div class="alert alert-success">
                    <p>{$LANG.moduleactionsuccess}</p>
                </div>
            {elseif $error}
                <div class="alert alert-error">
                    <p>{$error}</p>
                </div>
            {elseif $updatesuccess}
                <div class="alert alert-success">
                    <p>{$LANG.changessavedsuccessfully}</p>
                </div>
            {elseif $lockstatus=="unlocked"}
                <div class="alert alert-error" style="display: none;">
                    <p><strong>{$LANG.domaincurrentlyunlocked}:</strong> {$LANG.domaincurrentlyunlockedexp}</p>
                </div>
            {/if}

            <div class="notify" style="margin-bottom: 25px;">
                <div class="notify--icon">
                    <img src="{$WEB_ROOT}/templates/{$template}/img/ico-cancel.png"" alt="">
                </div>
                <div class="notify--content">
                    <h3>{$LANG.stcanceldomain}</h3>
                    <p>{$LANG.stcanceldomaintext}</p>
                </div>
            </div>

            <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&modop=custom&a=CancelDomain" class="form-horizontal">
                <input type="hidden" name="id" value="{$domainid}">
                <input type="hidden" name="canceldomain" value="on">
                <input type="hidden" name="autorenew" value="disable">
                <p class="text-center">
                    <input id="submit" type="submit" class="btn btn-action btn-action-disable" value="{$LANG.stcancelbtn}" />
                </p>
            </form>
        </div>
    </div>
</div>

{literal}
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery("#submit").click(function(e) {
                var cancel =  confirm("{/literal}{$LANG.stconfirmcancel}{literal}");
                if(cancel ==  false) {
                    e.preventDefault();
                }
            });

        });
    </script>
{/literal}