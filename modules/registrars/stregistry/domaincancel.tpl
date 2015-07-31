
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
    <div class="alert alert-error">
        <p><strong>{$LANG.domaincurrentlyunlocked}</strong> {$LANG.domaincurrentlyunlockedexp}</p>
    </div>
{/if}

<div class="page-header">
    <div class="styled_title">
        <h1>{$title}{if $desc} <small>{$desc}</small>{/if}</h1>
    </div>
</div>

<div class="tab-content">

    <div class="row">

        <div class="col30">
            <div class="internalpadding">
                <div class="styled_title"><h2>{$LANG.stcanceldomain}</h2></div>
                <p>{$LANG.stcanceldomaintext}</p>
            </div>
            <input class="btn" type="button" onclick="history.back()" value="« {$LANG.stbackbtn}">
        </div>
        <div class="col70">
            <div class="internalpadding">
                <div class="internalpadding">
                    <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails&modop=custom&a=CancelDomain" class="form-horizontal">
                        <input type="hidden" name="id" value="{$domainid}">
                        <input type="hidden" name="canceldomain" value="on">
                            <input type="hidden" name="autorenew" value="disable">
                            <p><input id="submit" type="submit" class="btn btn-large btn-danger" value="{$LANG.stcanselbtn}" /></p>
                    </form>
                </div>
                <br />
                <br />
                <br />
                <br />
            </div>
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