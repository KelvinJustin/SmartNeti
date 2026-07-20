{include file="sections/header.tpl"}
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">TumaSend Settings</h3>
    </div>
    <div class="box-body">
        <form role="form" method="post" action="{$_url}plugin/tumasend/settings/save">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Plugin Status</label>
                        <select class="form-control" name="tumasend_enabled">
                            <option value="0" {if $settings.tumasend_enabled == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.tumasend_enabled == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">API Key</label>
                        <input type="password" class="form-control" name="tumasend_api_key" value="{$settings.tumasend_api_key|default:''}" placeholder="Your TumaSend API key">
                        <small class="form-text text-muted">Your TumaSend API key from the dashboard</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Sender ID</label>
                        <input type="text" class="form-control" name="tumasend_sender_id" value="{$settings.tumasend_sender_id|default:''}" placeholder="Your Sender ID">
                        <small class="form-text text-muted">The sender name displayed to recipients</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">API Base URL</label>
                        <input type="text" class="form-control" name="tumasend_api_base_url" value="{$settings.tumasend_api_base_url|default:'https://gateway.tumasend.com'}">
                        <small class="form-text text-muted">TumaSend API endpoint URL</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Connection Timeout (seconds)</label>
                        <input type="number" class="form-control" name="tumasend_connection_timeout" value="{$settings.tumasend_connection_timeout|default:'30'}">
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Retry Attempts</label>
                        <input type="number" class="form-control" name="tumasend_retry_attempts" value="{$settings.tumasend_retry_attempts|default:'3'}">
                        <small class="form-text text-muted">Maximum retry attempts for failed requests</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Retry Delay (seconds)</label>
                        <input type="number" class="form-control" name="tumasend_retry_delay" value="{$settings.tumasend_retry_delay|default:'5'}">
                        <small class="form-text text-muted">Initial delay before retry (uses exponential backoff)</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Environment</label>
                        <input type="text" class="form-control" value="{$environment|default:'unknown'}" disabled>
                        <small class="form-text text-muted">Automatically detected from API key</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Debug Logging</label>
                        <select class="form-control" name="tumasend_debug_logging">
                            <option value="0" {if $settings.tumasend_debug_logging == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.tumasend_debug_logging == '1'}selected{/if}>Enabled</option>
                        </select>
                        <small class="form-text text-muted">Enable detailed logging for troubleshooting</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Webhook Secret</label>
                        <input type="password" class="form-control" name="tumasend_webhook_secret" value="{$settings.tumasend_webhook_secret|default:''}" placeholder="Your webhook secret">
                        <small class="form-text text-muted">Secret for verifying webhook signatures</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Enable Webhooks</label>
                        <select class="form-control" name="tumasend_webhook_enabled">
                            <option value="0" {if $settings.tumasend_webhook_enabled == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.tumasend_webhook_enabled == '1'}selected{/if}>Enabled</option>
                        </select>
                        <small class="form-text text-muted">Process delivery status webhooks from TumaSend</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Enable Queue</label>
                        <select class="form-control" name="tumasend_queue_enabled">
                            <option value="0" {if $settings.tumasend_queue_enabled == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.tumasend_queue_enabled == '1'}selected{/if}>Enabled</option>
                        </select>
                        <small class="form-text text-muted">Queue messages for background processing</small>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Data Usage Alerts</label>
                        <select class="form-control" name="data_usage_alerts_enabled">
                            <option value="0" {if $settings.data_usage_alerts_enabled == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.data_usage_alerts_enabled == '1'}selected{/if}>Enabled</option>
                        </select>
                        <small class="form-text text-muted">Send SMS alerts at configured data usage thresholds (requires cron job)</small>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Data Usage Thresholds (%)</label>
                        <input type="text" class="form-control" name="data_usage_thresholds" value="{$settings.data_usage_thresholds|default:'50,80,100'}" placeholder="50,80,100">
                        <small class="form-text text-muted">Comma-separated thresholds (e.g., 50,80,100)</small>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <h4>Alert Type Toggles</h4>
                    <p class="text-muted">Enable/disable specific alert types that trigger SMS notifications</p>
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Payment Confirmation Alerts</label>
                        <select class="form-control" name="alert_payment_confirmation">
                            <option value="0" {if $settings.alert_payment_confirmation == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_payment_confirmation == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Voucher Delivery Alerts</label>
                        <select class="form-control" name="alert_voucher_delivery">
                            <option value="0" {if $settings.alert_voucher_delivery == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_voucher_delivery == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Password Reset Alerts</label>
                        <select class="form-control" name="alert_password_reset">
                            <option value="0" {if $settings.alert_password_reset == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_password_reset == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Account Activation Alerts</label>
                        <select class="form-control" name="alert_account_activation">
                            <option value="0" {if $settings.alert_account_activation == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_account_activation == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Package Activation Alerts</label>
                        <select class="form-control" name="alert_package_activation">
                            <option value="0" {if $settings.alert_package_activation == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_package_activation == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Package Expiration Alerts</label>
                        <select class="form-control" name="alert_package_expiration">
                            <option value="0" {if $settings.alert_package_expiration == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_package_expiration == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Low Balance Alerts</label>
                        <select class="form-control" name="alert_low_balance">
                            <option value="0" {if $settings.alert_low_balance == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_low_balance == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Invoice Alerts</label>
                        <select class="form-control" name="alert_invoice">
                            <option value="0" {if $settings.alert_invoice == '0'}selected{/if}>Disabled</option>
                            <option value="1" {if $settings.alert_invoice == '1'}selected{/if}>Enabled</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <a href="{$_url}plugin/tumasend/diagnostics" class="btn btn-default">Run Diagnostics</a>
                    <a href="{$_url}plugin/tumasend/history" class="btn btn-default">View SMS History</a>
                    <a href="{$_url}plugin/tumasend/templates" class="btn btn-default">Message Templates</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="box box-solid box-primary" style="margin-top: 20px;">
    <div class="box-header with-border">
        <h3 class="box-title">Test SMS</h3>
    </div>
    <div class="box-body">
        <form role="form" method="post" action="{$_url}plugin/tumasend/test">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <div class="form-group">
                <label class="control-label">Test Recipient</label>
                <input type="text" class="form-control" name="test_recipient" placeholder="+265991234567">
            </div>
            <div class="form-group">
                <label class="control-label">Test Message</label>
                <textarea class="form-control" name="test_message" rows="3">This is a test message from TumaSend plugin.</textarea>
            </div>
            <button type="submit" name="test_sms" value="1" class="btn btn-info">Send Test SMS</button>
        </form>
    </div>
</div>
{include file="sections/footer.tpl"}
