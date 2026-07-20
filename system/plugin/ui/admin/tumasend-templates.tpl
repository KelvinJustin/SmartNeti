{include file="sections/header.tpl"}
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Message Templates</h3>
        <a href="{$_url}plugin/tumasend/settings" class="btn btn-default btn-sm pull-right">Back to Settings</a>
    </div>
    <div class="box-body">
        <div class="row">
            {foreach from=$templates item=tpl}
            <div class="col-md-6">
                <div class="box box-solid box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{$tpl.template_name}</h3>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                    <div class="box-body">
                        <form method="post" action="{$_url}plugin/tumasend/templates/save">
                            <input type="hidden" name="template_key" value="{$tpl.template_key}">
                            <div class="form-group">
                                <label class="control-label">Template Key</label>
                                <input type="text" class="form-control" value="{$tpl.template_key}" disabled>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Template Content</label>
                                <textarea class="form-control" name="template_content" rows="6">{$tpl.template_content}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Available Placeholders</label>
                                <div class="well well-sm">
                                    <code>{customer_name}</code> - Customer full name<br>
                                    <code>{username}</code> - Username<br>
                                    <code>{voucher_code}</code> - Voucher code<br>
                                    <code>{plan_name}</code> - Plan name<br>
                                    <code>{expiry_date}</code> - Expiration date<br>
                                    <code>{amount}</code> - Amount<br>
                                    <code>{invoice_number}</code> - Invoice number<br>
                                    <code>{company_name}</code> - Company name
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save Template</button>
                        </form>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
</div>
{include file="sections/footer.tpl"}
