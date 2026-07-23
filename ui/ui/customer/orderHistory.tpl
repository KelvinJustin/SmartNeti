{include file="customer/header.tpl"}
<!-- user-orderHistory -->
<style>
    .table_mobile {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        background-color: transparent;
    }

    #datatable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        white-space: nowrap;
    }

    #datatable thead th {
        position: sticky;
        top: 0;
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        color: #374151;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        z-index: 10;
    }

    #datatable tbody td {
        padding: 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        transition: background-color 0.15s ease;
        color: #1f2937;
    }

    #datatable tbody tr:last-child td {
        border-bottom: none;
    }

    #datatable tbody tr:hover td {
        background-color: #f9fafb;
    }

    /* Modern Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-paid {
        background-color: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-unpaid {
        background-color: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .status-failed {
        background-color: rgba(245, 158, 11, 0.15);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .status-canceled {
        background-color: rgba(107, 114, 128, 0.15);
        color: #6b7280;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }

    .status-unknown {
        background-color: rgba(156, 163, 175, 0.15);
        color: #9ca3af;
        border: 1px solid rgba(156, 163, 175, 0.2);
    }

    /* Dark mode support */
    body.dark-mode #datatable thead th,
    [data-theme="dark"] #datatable thead th {
        background-color: rgba(39, 42, 53, 0.95);
        color: #9ca3af;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #datatable tbody td,
    [data-theme="dark"] #datatable tbody td {
        color: #f3f4f6;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #datatable tbody tr:hover td,
    [data-theme="dark"] #datatable tbody tr:hover td {
        background-color: #272a35;
    }

    body.dark-mode .status-paid,
    [data-theme="dark"] .status-paid {
        background-color: rgba(16, 185, 129, 0.15);
        color: #059669;
    }

    body.dark-mode .status-unpaid,
    [data-theme="dark"] .status-unpaid {
        background-color: rgba(239, 68, 68, 0.15);
        color: #dc2626;
    }

    body.dark-mode .status-failed,
    [data-theme="dark"] .status-failed {
        background-color: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }

    body.dark-mode .status-canceled,
    [data-theme="dark"] .status-canceled {
        background-color: rgba(107, 114, 128, 0.15);
        color: #9ca3af;
    }

    body.dark-mode .status-unknown,
    [data-theme="dark"] .status-unknown {
        background-color: rgba(156, 163, 175, 0.15);
        color: #9ca3af;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel mb20 panel-hovered panel-primary">
            <div class="panel-heading">{Lang::T('Order History')}</div>
            <div class="panel-body">
                <div class="table_mobile">
                    <table id="datatable">
                        <thead>
                            <tr>
                                <th>{Lang::T('Package Name')}</th>
                                <th>{Lang::T('Payment Method')}</th>
                                <th>Routers</th>
                                <th>{Lang::T('Type')}</th>
                                <th>{Lang::T('Package Price')}</th>
                                <th>{Lang::T('Created on')}</th>
                                <th>{Lang::T('Expires on')}</th>
                                <th>{Lang::T('Date')}</th>
                                <th>{Lang::T('Status')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $d as $ds}
                                <tr>
                                    <td><a href="{Text::url('order/view/')}{$ds['id']}">{$ds['plan_name']}</a></td>
                                    <td>{$ds['gateway']}</td>
                                    <td>{$ds['routers']}</td>
                                    <td>{$ds['payment_channel']}</td>
                                    <td>{Lang::moneyFormat($ds['price'])}</td>
                                    <td>{date("{$_c['date_format']} H:i", strtotime($ds['created_date']))}</td>
                                    <td>{date("{$_c['date_format']} H:i", strtotime($ds['expired_date']))}</td>
                                    <td>{if $ds['status']!=1}{date("{$_c['date_format']} H:i", strtotime($ds['paid_date']))}{/if}</td>
                                    <td>
                                        {if $ds['status']==1}
                                            <span class="status-pill status-unpaid">{Lang::T('UNPAID')}</span>
                                        {elseif $ds['status']==2}
                                            <span class="status-pill status-paid">{Lang::T('PAID')}</span>
                                        {elseif $ds['status']==3}
                                            <span class="status-pill status-failed">{$_L['FAILED']}</span>
                                        {elseif $ds['status']==4}
                                            <span class="status-pill status-canceled">{Lang::T('CANCELED')}</span>
                                        {elseif $ds['status']==5}
                                            <span class="status-pill status-unknown">{Lang::T('UNKNOWN')}</span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {include file="pagination.tpl"}
            </div>
        </div>
    </div>
</div>


{include file="customer/footer.tpl"}