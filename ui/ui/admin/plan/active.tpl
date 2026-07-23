{include file="sections/header.tpl"}
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-block;
        padding: 5px 10px;
        margin-right: 5px;
        border: 1px solid #ccc;
        background-color: #fff;
        color: #333;
        cursor: pointer;
    }

    /* Modern Active Customers Table Styling */
    .table_mobile {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        background-color: transparent;
    }

    #activeCustomersTable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        white-space: nowrap;
    }

    #activeCustomersTable thead th {
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

    #activeCustomersTable tbody td {
        padding: 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        transition: background-color 0.15s ease;
        color: #1f2937;
    }

    #activeCustomersTable tbody tr:last-child td {
        border-bottom: none;
    }

    #activeCustomersTable tbody tr:hover td {
        background-color: #f9fafb;
    }

    #activeCustomersTable tbody tr.danger td {
        background-color: rgba(239, 68, 68, 0.1);
    }

    #activeCustomersTable tbody tr.danger:hover td {
        background-color: rgba(239, 68, 68, 0.2);
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

    .status-active {
        background-color: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-expired {
        background-color: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* Modernized Action Buttons (Button Group) */
    .action-group {
        display: inline-flex;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .btn-action {
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background-color: transparent;
        color: #1f2937;
        border-right: 1px solid #e5e7eb;
        transition: background-color 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-action:last-child {
        border-right: none;
    }

    .btn-action:hover {
        background-color: #f9fafb;
    }

    .btn-action.edit { color: #0891b2; }
    .btn-action.delete { color: #dc2626; }
    .btn-action.extend { color: #d97706; }

    .btn-action:hover.edit { background-color: rgba(8, 145, 178, 0.1); }
    .btn-action:hover.delete { background-color: rgba(220, 38, 38, 0.1); }
    .btn-action:hover.extend { background-color: rgba(217, 119, 6, 0.1); }

    /* Dark mode support */
    body.dark-mode #activeCustomersTable thead th,
    [data-theme="dark"] #activeCustomersTable thead th {
        background-color: rgba(39, 42, 53, 0.95);
        color: #9ca3af;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #activeCustomersTable tbody td,
    [data-theme="dark"] #activeCustomersTable tbody td {
        color: #f3f4f6;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #activeCustomersTable tbody tr:hover td,
    [data-theme="dark"] #activeCustomersTable tbody tr:hover td {
        background-color: #272a35;
    }

    body.dark-mode #activeCustomersTable tbody tr.danger td,
    [data-theme="dark"] #activeCustomersTable tbody tr.danger td {
        background-color: rgba(239, 68, 68, 0.1);
    }

    body.dark-mode #activeCustomersTable tbody tr.danger:hover td,
    [data-theme="dark"] #activeCustomersTable tbody tr.danger:hover td {
        background-color: rgba(239, 68, 68, 0.2);
    }

    body.dark-mode .action-group,
    [data-theme="dark"] .action-group {
        border-color: #2d3342;
    }

    body.dark-mode .btn-action,
    [data-theme="dark"] .btn-action {
        color: #f3f4f6;
        border-right-color: #2d3342;
    }

    body.dark-mode .btn-action:hover,
    [data-theme="dark"] .btn-action:hover {
        background-color: #272a35;
    }

    body.dark-mode .status-active,
    [data-theme="dark"] .status-active {
        background-color: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.2);
    }

    body.dark-mode .status-expired,
    [data-theme="dark"] .status-expired {
        background-color: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.2);
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                    <div class="btn-group pull-right">
                        <a class="btn btn-primary btn-xs" title="save" href="{Text::url('')}plan/sync"
                            onclick="return ask(this, '{Lang::T("This will sync dan send Customer active package to Mikrotik")}?')"><span
                                class="glyphicon glyphicon-refresh" aria-hidden="true"></span> {Lang::T("Sync")}</a>
                    </div>
                {/if}
                &nbsp;
                {Lang::T('Active Customers')}
            </div>
            <form id="site-search" method="post" action="{Text::url('')}plan/list/">
                <div class="panel-body">
                    <div class="row row-no-gutters" style="padding: 5px">
                        <div class="col-lg-2">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <a class="btn btn-danger" title="Clear Search Query"
                                        href="{Text::url('')}plan/list"><span
                                            class="glyphicon glyphicon-remove-circle"></span></a>
                                </div>
                                <input type="text" name="search" class="form-control"
                                    placeholder="{Lang::T("Search")}..." value="{$search}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-4">
                            <select class="form-control" id="router" name="router">
                                <option value="">{Lang::T("Location")}</option>
                                {foreach $routers as $r}
                                    <option value="{$r}" {if $router eq $r }selected{/if}>{$r}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-lg-2 col-xs-4">
                            <select class="form-control" id="plan" name="plan">
                                <option value="">{Lang::T("Plan Name")}</option>
                                {foreach $plans as $p}
                                    <option value="{$p['id']}" {if $plan eq $p['id'] }selected{/if}>{$p['name_plan']}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-lg-2 col-xs-4">
                            <select class="form-control" id="status" name="status">
                                <option value="-">{Lang::T("Status")}</option>
                                <option value="on" {if $status eq 'on' }selected{/if}>{Lang::T("Active")}</option>
                                <option value="off" {if $status eq 'off' }selected{/if}>{Lang::T("Expired")}</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <button class="btn btn-success btn-block" type="submit"><span
                                    class="fa fa-search"></span></button>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <a href="{Text::url('')}plan/recharge" class="btn btn-primary btn-block"><i
                                    class="ion ion-android-add">
                                </i> {Lang::T("Recharge Account")}</a>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive table_mobile">
                <table id="activeCustomersTable">
                    <thead>
                        <tr>
                            <th>{Lang::T("Username")}</th>
                            <th>{Lang::T("Plan Name")}</th>
                            <th>{Lang::T("Type")}</th>
                            <th>{Lang::T("Created On")}</th>
                            <th>{Lang::T("Expires On")}</th>
                            <th>{Lang::T("Method")}</th>
                            <th><a href="{Text::url('')}routers/list">{Lang::T("Location")}</a></th>
                            <th>{Lang::T("Manage")}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $d as $ds}
                            <tr {if $ds['status']=='off' }class="danger" {/if}>
                                <td>
                                    {if $ds['customer_id'] == '0'}
                                        <a href="{Text::url('plan/voucher/&search=')}{$ds['username']}">{$ds['username']}</a>
                                    {else}
                                        <a href="{Text::url('customers/viewu/')}{$ds['username']}">{$ds['username']}</a>
                                    {/if}
                                </td>
                                <td>
                                    {if $ds['type'] == 'Hotspot'}
                                        <a href="{Text::url('')}services/edit/{$ds['plan_id']}">{$ds['namebp']}</a>
                                        <span api-get-text="{Text::url('')}autoload/customer_is_active/{$ds['username']}/{$ds['plan_id']}"></span>
                                    {elseif $ds['type'] == 'PPPOE'}
                                        <a href="{Text::url('')}services/pppoe-edit/{$ds['plan_id']}">{$ds['namebp']}</a>
                                        <span api-get-text="{Text::url('')}autoload/customer_is_active/{$ds['username']}/{$ds['plan_id']}"></span>
                                    {elseif $ds['type'] == 'VPN'}
                                        <a href="{Text::url('')}services/vpn-edit/{$ds['plan_id']}">{$ds['namebp']}</a>
                                    {/if}
                                </td>
                                <td>
                                    {if $ds['status']=='on'}
                                        <span class="status-pill status-active">{Lang::T("Active")}</span>
                                    {else}
                                        <span class="status-pill status-expired">{Lang::T("Expired")}</span>
                                    {/if}
                                </td>
                                <td>{Lang::dateAndTimeFormat($ds['recharged_on'],$ds['recharged_time'])}</td>
                                <td>{Lang::dateAndTimeFormat($ds['expiration'],$ds['time'])}</td>
                                <td>{$ds['method']}</td>
                                <td>{$ds['routers']}</td>
                                <td>
                                    <div class="action-group">
                                        <a href="{Text::url('')}plan/edit/{$ds['id']}" class="btn-action edit">{Lang::T("Edit")}</a>
                                        {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                            <a href="{Text::url('')}plan/delete/{$ds['id']}" id="{$ds['id']}"
                                                onclick="return ask(this, '{Lang::T("Delete")}?')"
                                                class="btn-action delete"><i class="glyphicon glyphicon-trash"></i></a>
                                        {/if}
                                        {if $ds['status']=='off' && $_c['extend_expired']}
                                            <a href="javascript:extend('{$ds['id']}')" class="btn-action extend">{Lang::T("Extend")}</a>
                                        {/if}
                                    </div>
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

<script>
    function extend(idP) {
        var res = prompt("Extend for many days?", "3");
        if (res) {
            if (confirm("Extend for " + res + " days?")) {
                window.location.href = "{Text::url('plan/extend/')}" + idP + "/" + res + "{Text::isQA('? or &')}stoken={App::getToken()}";
            }
        }
    }
</script>

{include file="sections/footer.tpl"}
