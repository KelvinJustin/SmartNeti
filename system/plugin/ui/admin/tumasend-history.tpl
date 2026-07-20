{include file="sections/header.tpl"}
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">SMS History</h3>
        <a href="{$_url}plugin/tumasend/settings" class="btn btn-default btn-sm pull-right">Back to Settings</a>
    </div>
    <div class="box-body">
        <form method="get" action="{$_url}plugin/tumasend/history">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="">All</option>
                            <option value="queued" {if $_GET.status == 'queued'}selected{/if}>Queued</option>
                            <option value="sent" {if $_GET.status == 'sent'}selected{/if}>Sent</option>
                            <option value="delivered" {if $_GET.status == 'delivered'}selected{/if}>Delivered</option>
                            <option value="failed" {if $_GET.status == 'failed'}selected{/if}>Failed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Search</label>
                        <input type="text" class="form-control" name="search" value="{$_GET.search|default:''}" placeholder="Phone or message">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="box">
    <div class="box-body table-responsive no-padding">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Recipient</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Batch ID</th>
                    <th>Credits</th>
                    <th>Queued At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$messages item=msg}
                <tr>
                    <td>{$msg.id}</td>
                    <td>{$msg.recipient}</td>
                    <td>
                        <small>{$msg.message|truncate:50}</small>
                        {if strlen($msg.message) > 50}
                        <button type="button" class="btn btn-xs btn-info" onclick="toggleMessage({$msg.id})">View</button>
                        {/if}
                    </td>
                    <td>
                        {if $msg.status == 'queued'}
                        <span class="label label-warning">Queued</span>
                        {elseif $msg.status == 'sent'}
                        <span class="label label-primary">Sent</span>
                        {elseif $msg.status == 'delivered'}
                        <span class="label label-success">Delivered</span>
                        {elseif $msg.status == 'failed'}
                        <span class="label label-danger">Failed</span>
                        {/if}
                    </td>
                    <td><small>{$msg.batch_id|default:'N/A'}</small></td>
                    <td>
                        {if $msg.credits_used}
                        <small>{$msg.credits_used} used<br>{$msg.credits_remaining} remaining</small>
                        {else}
                        <small>N/A</small>
                        {/if}
                    </td>
                    <td><small>{$msg.queued_at}</small></td>
                    <td>
                        {if $msg.status == 'failed'}
                        <button type="button" class="btn btn-xs btn-warning" onclick="resendSMS({$msg.id})">Retry</button>
                        {/if}
                        <button type="button" class="btn btn-xs btn-info" onclick="toggleDetails({$msg.id})">Details</button>
                    </td>
                </tr>

                <!-- Message Detail Row -->
                <tr id="messageRow{$msg.id}" class="detail-row" style="display: none;">
                    <td colspan="8" style="padding: 15px; background-color: #f9f9f9;">
                        <h5>Full Message</h5>
                        <pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">{$msg.message}</pre>
                    </td>
                </tr>

                <!-- SMS Details Row -->
                <tr id="detailRow{$msg.id}" class="detail-row" style="display: none;">
                    <td colspan="8" style="padding: 15px; background-color: #f5f5f5; border-top: 2px solid #ddd;">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-condensed" style="background: #fff; border: 1px solid #ddd;">
                                    <tr>
                                        <th style="width: 30%;">Sent At</th>
                                        <td>{$msg.sent_at|default:'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Delivered At</th>
                                        <td>{$msg.delivered_at|default:'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Failed At</th>
                                        <td>{$msg.failed_at|default:'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Credits Used</th>
                                        <td>{$msg.credits_used|default:'N/A'}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-condensed" style="background: #fff; border: 1px solid #ddd;">
                                    <tr>
                                        <th style="width: 30%;">Environment</th>
                                        <td>{$msg.environment|default:'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Retry Count</th>
                                        <td>{$msg.retry_count}</td>
                                    </tr>
                                    {if $msg.error_message}
                                    <tr>
                                        <th>Error</th>
                                        <td><span class="text-danger">{$msg.error_message}</span></td>
                                    </tr>
                                    {/if}
                                </table>
                            </div>
                        </div>
                        {if $msg.raw_response}
                        <div style="margin-top: 15px;">
                            <h5>Raw API Response</h5>
                            <pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto;"><code>{$msg.raw_response}</code></pre>
                        </div>
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div class="box-footer">
        <div class="pull-left">
            Showing {$page} of {$total_pages} pages ({$total} total)
        </div>
        <div class="pull-right">
            {if $page > 1}
            <a href="{$_url}plugin/tumasend/history/{$page-1}" class="btn btn-default btn-sm">Previous</a>
            {/if}
            {if $page < $total_pages}
            <a href="{$_url}plugin/tumasend/history/{$page+1}" class="btn btn-default btn-sm">Next</a>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<style>
.detail-row {
    transition: background-color 0.2s ease;
}
.detail-row:hover {
    background-color: #f0f0f0;
}
</style>

<script>
function toggleMessage(id) {
    var row = document.getElementById('messageRow' + id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

function toggleDetails(id) {
    var row = document.getElementById('detailRow' + id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

function resendSMS(id) {
    if (confirm('Are you sure you want to resend this SMS?')) {
        // Implement resend functionality
        alert('Resend functionality to be implemented');
    }
}
</script>
{include file="sections/footer.tpl"}
