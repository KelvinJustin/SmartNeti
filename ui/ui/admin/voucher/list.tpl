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

    /* Modern Voucher Table Styling */
    .table_mobile {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        background-color: transparent;
    }

    #voucherTable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        white-space: nowrap;
    }

    #voucherTable thead th {
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

    #voucherTable tbody td {
        padding: 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        transition: background-color 0.15s ease;
        color: #1f2937;
    }

    #voucherTable tbody tr:last-child td {
        border-bottom: none;
    }

    #voucherTable tbody tr:hover td {
        background-color: #f9fafb;
    }

    #voucherTable tbody tr.danger td {
        background-color: rgba(239, 68, 68, 0.1);
    }

    #voucherTable tbody tr.danger:hover td {
        background-color: rgba(239, 68, 68, 0.2);
    }

    /* Checkbox styling */
    #voucherTable input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #3b82f6;
        cursor: pointer;
    }

    /* Voucher code styling */
    .voucher-code {
        background-color: #1f2937;
        color: #f3f4f6;
        padding: 4px 8px;
        border-radius: 4px;
        font-family: monospace;
        cursor: pointer;
        transition: all 0.2s;
    }

    .voucher-code:hover {
        background-color: #374151;
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

    .status-not-used {
        background-color: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-used {
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

    .btn-action.view { color: #0d9488; }
    .btn-action.delete { color: #dc2626; }

    .btn-action:hover.view { background-color: rgba(13, 148, 136, 0.1); }
    .btn-action:hover.delete { background-color: rgba(220, 38, 38, 0.1); }

    /* Dark mode support */
    body.dark-mode #voucherTable thead th,
    [data-theme="dark"] #voucherTable thead th {
        background-color: rgba(39, 42, 53, 0.95);
        color: #9ca3af;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #voucherTable tbody td,
    [data-theme="dark"] #voucherTable tbody td {
        color: #f3f4f6;
        border-bottom: 1px solid #2d3342;
    }

    body.dark-mode #voucherTable tbody tr:hover td,
    [data-theme="dark"] #voucherTable tbody tr:hover td {
        background-color: #272a35;
    }

    body.dark-mode #voucherTable tbody tr.danger td,
    [data-theme="dark"] #voucherTable tbody tr.danger td {
        background-color: rgba(239, 68, 68, 0.1);
    }

    body.dark-mode #voucherTable tbody tr.danger:hover td,
    [data-theme="dark"] #voucherTable tbody tr.danger:hover td {
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

    body.dark-mode .status-not-used,
    [data-theme="dark"] .status-not-used {
        background-color: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.2);
    }

    body.dark-mode .status-used,
    [data-theme="dark"] .status-used {
        background-color: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.2);
    }
</style>
<!-- voucher -->
<div class="row" style="padding: 5px">
    <div class="col-lg-3 col-lg-offset-9">
        <div class="btn-group btn-group-justified" role="group">
            <div class="btn-group" role="group">
                <a href="{Text::url('')}plan/add-voucher" class="btn btn-primary"><i class="ion ion-android-add"></i>
                    {Lang::T('Vouchers')}</a>
            </div>
            <div class="btn-group" role="group">
                <a href="{Text::url('')}plan/print-voucher" target="print_voucher" class="btn btn-info"><i
                        class="ion ion-android-print"></i> {Lang::T('Print')}</a>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-hovered mb20 panel-primary">
    <div class="panel-heading">
        {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
            <div class="btn-group pull-right">
                <a class="btn btn-danger btn-xs" title="Remove used Voucher" href="{Text::url('')}plan/remove-voucher"
                    onclick="return ask(this, 'Delete all used voucher code more than 3 months?')"><span
                        class="glyphicon glyphicon-trash" aria-hidden="true"></span> {Lang::T('Delete')} &gt; {Lang::T('3
                Months')}</a>
            </div>
        {/if}
        &nbsp;
    </div>
    <div class="panel-body">
        <form id="site-search" method="post" action="{Text::url('')}plan/voucher/">
            <div class="row" style="padding: 5px">
                <div class="col-lg-2">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="fa fa-search"></span>
                        </div>
                        <input type="text" name="search" class="form-control" placeholder="{Lang::T('Code Voucher')}"
                            value="{$search}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select class="form-control" id="router" name="router">
                        <option value="">{Lang::T('Location')}</option>
                        {foreach $routers as $r}
                            <option value="{$r}" {if $router eq $r }selected{/if}>{$r}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control" id="plan" name="plan">
                        <option value="">{Lang::T('Plan Name')}</option>
                        {foreach $plans as $p}
                            <option value="{$p['id']}" {if $plan eq $p['id'] }selected{/if}>{$p['name_plan']}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control" id="status" name="status">
                        <option value="-">{Lang::T('Status')}</option>
                        <option value="1" {if $status eq 1 }selected{/if}>Used</option>
                        <option value="0" {if $status eq 0 }selected{/if}>Not Use</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control" id="customer" name="customer">
                        <option value="">{Lang::T('Customer')}</option>
                        {foreach $customers as $c}
                            <option value="{$c['user']}" {if $customer eq $c['user'] }selected{/if}>{$c['user']}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                    <div class="btn-group btn-group-justified" role="group">
                        <div class="btn-group" role="group">
                            <button class="btn btn-success btn-block" type="submit"><span
                                    class="fa fa-search"></span></button>
                        </div>
                        <div class="btn-group" role="group">
                            <a class="btn btn-warning btn-block" title="Clear Search Query"
                                href="{Text::url('')}plan/voucher/"><span
                                    class="glyphicon glyphicon-remove-circle"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive table_mobile">
        <table id="voucherTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>ID</th>
                    <th>{Lang::T('Type')}</th>
                    <th>{Lang::T('Routers')}</th>
                    <th>{Lang::T('Plan Name')}</th>
                    <th>{Lang::T('Code Voucher')}</th>
                    <th>{Lang::T('Status Voucher')}</th>
                    <th>{Lang::T('Customer')}</th>
                    <th>{Lang::T('Create Date')}</th>
                    <th>{Lang::T('Used Date')}</th>
                    <th>{Lang::T('Generated By')}</th>
                    <th>{Lang::T('Manage')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $d as $ds}
                    <tr {if $ds['status'] eq '1' }class="danger" {/if}>
                        <td><input type="checkbox" name="voucher_ids[]" value="{$ds['id']}"></td>
                        <td>{$ds['id']}</td>
                        <td>{$ds['type']}</td>
                        <td>{$ds['routers']}</td>
                        <td>{$ds['name_plan']}</td>
                        <td><span class="voucher-code" onclick="this.select()">{$ds['code']}</span></td>
                        <td>
                            {if $ds['status'] eq '0'}
                                <span class="status-pill status-not-used">{Lang::T('Not Use')}</span>
                            {else}
                                <span class="status-pill status-used">{Lang::T('Used')}</span>
                            {/if}
                        </td>
                        <td>
                            {if $ds['user'] eq '0'}
                                -
                            {else}
                                <a href="{Text::url('')}customers/viewu/{$ds['user']}">{$ds['user']}</a>
                            {/if}
                        </td>
                        <td>{if $ds['created_at']}{Lang::dateTimeFormat($ds['created_at'])}{/if}</td>
                        <td>{if $ds['used_date']}{Lang::dateTimeFormat($ds['used_date'])}{/if}</td>
                        <td>
                            {if $ds['generated_by']}
                                <a href="{Text::url('')}settings/users-view/{$ds['generated_by']}">{$admins[$ds['generated_by']]}</a>
                            {else}
                                -
                            {/if}
                        </td>
                        <td>
                            <div class="action-group">
                                {if $ds['status'] neq '1'}
                                    <a href="{Text::url('')}plan/voucher-view/{$ds['id']}" id="{$ds['id']}"
                                        class="btn-action view">{Lang::T('View')}</a>
                                {/if}
                                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <a href="{Text::url('')}plan/voucher-delete/{$ds['id']}" id="{$ds['id']}"
                                        class="btn-action delete" onclick="return ask(this, '{Lang::T('Delete')}?')"><i
                                            class="glyphicon glyphicon-trash"></i></a>
                                {/if}
                            </div>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
<div class="row" style="padding: 5px">
    <div class="col-lg-3 col-lg-offset-9">
        <div class="btn-group btn-group-justified" role="group">
            <div class="btn-group" role="group">
                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                    <button id="deleteSelectedVouchers" class="btn btn-danger">{Lang::T('Delete
                    Selected')}</button>
                {/if}
            </div>
        </div>
    </div>
</div>
{include file="pagination.tpl"}

<script>
    function deleteVouchers(voucherIds) {
        if (voucherIds.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '{Text::url('')}plan/voucher-delete-many', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);

                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload(); // Reload the page after confirmation
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete vouchers. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    };
                    xhr.send('voucherIds=' + JSON.stringify(voucherIds));
                }
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'No vouchers selected to delete.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    // Example usage for selected vouchers
    document.getElementById('deleteSelectedVouchers').addEventListener('click', function() {
        var selectedVouchers = [];
        document.querySelectorAll('input[name="voucher_ids[]"]:checked').forEach(function(checkbox) {
            selectedVouchers.push(checkbox.value);
        });

        if (selectedVouchers.length > 0) {
            deleteVouchers(selectedVouchers);
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'Please select at least one voucher to delete.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });

    document.querySelectorAll('.delete-voucher').forEach(function(button) {
        button.addEventListener('click', function() {
            var voucherId = this.getAttribute('data-id');
            deleteVouchers([voucherId]);
        });
    });


    // Select or deselect all checkboxes
    document.getElementById('select-all').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="voucher_ids[]"]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>
{include file="sections/footer.tpl"}