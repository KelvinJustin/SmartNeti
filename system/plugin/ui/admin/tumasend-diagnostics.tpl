<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">TumaSend Diagnostics</h3>
        <a href="{$_url}plugin/tumasend/settings" class="btn btn-default btn-sm pull-right">Back to Settings</a>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                    <th>Current Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$diagnostics item=diag}
                <tr>
                    <td><strong>{$diag.name}</strong></td>
                    <td>
                        {if $diag.status == 'pass'}
                        <span class="label label-success">Pass</span>
                        {elseif $diag.status == 'fail'}
                        <span class="label label-danger">Fail</span>
                        {elseif $diag.status == 'warn'}
                        <span class="label label-warning">Warning</span>
                        {elseif $diag.status == 'skip'}
                        <span class="label label-default">Skipped</span>
                        {/if}
                    </td>
                    <td>{$diag.value}</td>
                    <td>{$diag.required}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        
        <div class="box box-solid box-info" style="margin-top: 20px;">
            <div class="box-header with-border">
                <h3 class="box-title">Recommendations</h3>
            </div>
            <div class="box-body">
                <ul>
                    {foreach from=$diagnostics item=diag}
                    {if $diag.status == 'fail'}
                    <li class="text-danger"><strong>{$diag.name}:</strong> {$diag.value} (Required: {$diag.required})</li>
                    {elseif $diag.status == 'warn'}
                    <li class="text-warning"><strong>{$diag.name}:</strong> {$diag.value} (Recommended: {$diag.required})</li>
                    {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="button" class="btn btn-primary" onclick="location.reload()">Re-run Diagnostics</button>
            <a href="{$_url}plugin/tumasend/settings" class="btn btn-default">Back to Settings</a>
        </div>
    </div>
</div>
