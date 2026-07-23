{include file="customer/header.tpl"}
<!-- user-activation-list -->
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

    #datatable tbody tr[onclick] {
        cursor: pointer;
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
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel mb20 panel-hovered panel-primary">
            <div class="panel-heading">{Lang::T('Transaction History List')}</div>
            <div class="panel-body">
                <div class="table_mobile">
                    <table id="datatable">
                        <thead>
                            <tr>
                                <th>{Lang::T('Invoice')}</th>
                                <th>{Lang::T('Package Name')}</th>
                                <th>{Lang::T('Package Price')}</th>
                                <th>{Lang::T('Type')}</th>
                                <th>{Lang::T('Created On')}</th>
                                <th>{Lang::T('Expires On')}</th>
                                <th>{Lang::T('Method')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $d as $ds}
                                <tr onclick="window.location.href = '{Text::url('voucher/invoice/')}{$ds.id|escape:'html'}'">
                                    <td>{$ds.invoice|escape:'html'}</td>
                                    <td>{$ds.plan_name|escape:'html'}</td>
                                    <td>{Lang::moneyFormat($ds.price)}</td>
                                    <td>{$ds.type|escape:'html'}</td>
                                    <td>{Lang::dateAndTimeFormat($ds.recharged_on, $ds.recharged_time)}</td>
                                    <td>{Lang::dateAndTimeFormat($ds.expiration, $ds.time)}</td>
                                    <td>{$ds.method|escape:'html'}</td>
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
