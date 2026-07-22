{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" role="form" action="{$_url}paymentgateway/paychangu" >
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">
                    <div class="panel-title">PayChangu Settings</div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Secret Key</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="secret_key" name="secret_key" placeholder="Enter your PayChangu Secret Key" value="{$_c['paychangu_secret_key']}" required>
                            <small class="form-text text-muted">Login to <a href="https://paychangu.com/dashboard" target="_blank">https://paychangu.com/dashboard</a> to get your secret key from Settings → API Keys. Used for API authentication.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Public Key</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="public_key" name="public_key" placeholder="Enter your PayChangu Public Key" value="{$_c['paychangu_public_key']}">
                            <small class="form-text text-muted">Get your public key from Settings → API Keys. Used for Inline Checkout (optional).</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Webhook Secret Key</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="webhook_secret" name="webhook_secret" placeholder="Enter your Webhook Secret Key" value="{$_c['paychangu_webhook_secret']}" required>
                            <small class="form-text text-muted">Login to <a href="https://paychangu.com/dashboard" target="_blank">https://paychangu.com/dashboard</a> to generate your webhook secret key. This is used to verify webhook authenticity.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Return URL</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="return_url" name="return_url" placeholder="https://yourdomain.com/payment-success" value="{$_c['paychangu_return_url']}">
                            <small class="form-text text-muted">The URL where customers are redirected when payment times out or is cancelled. Leave blank to use default.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Currency</label>
                        <div class="col-md-6">
                            <select class="form-control" id="currency" name="currency">
                                <option value="MWK" {if $_c['paychangu_currency'] == 'MWK'}selected{/if}>MWK - Malawian Kwacha</option>
                                <option value="USD" {if $_c['paychangu_currency'] == 'USD'}selected{/if}>USD - US Dollar</option>
                                <option value="EUR" {if $_c['paychangu_currency'] == 'EUR'}selected{/if}>EUR - Euro</option>
                                <option value="GBP" {if $_c['paychangu_currency'] == 'GBP'}selected{/if}>GBP - British Pound</option>
                                <option value="ZAR" {if $_c['paychangu_currency'] == 'ZAR'}selected{/if}>ZAR - South African Rand</option>
                            </select>
                            <small class="form-text text-muted">Select the currency for payments</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-2 control-label">Webhook URL (Callback)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="webhook" name="callback_url" value="{$_c['paychangu_callback_url']}" required>
                            <small class="form-text text-muted">Copy this URL and add it to your PayChangu dashboard webhook settings. This is where PayChangu sends payment notifications when payment is successful.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">SAVE CHANGES</button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Hotspot Walled Garden Configuration:</strong>
                        <pre>/ip hotspot walled-garden
add dst-host=paychangu.com
add dst-host=*.paychangu.com
add dst-host=checkout.paychangu.com
add dst-host=api.paychangu.com</pre>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
let input = document.getElementById('webhook');
var fullURL = window.location.href;
if (!input.value) {
    input.value = "https://"+fullURL.split('/')[2]+"/index.php?_route=callback/paychangu";
}
</script>
{include file="sections/footer.tpl"}
