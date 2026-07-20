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

    /* Modern Customer Table Styling */
    .table_mobile {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        background-color: transparent;
    }

    #customerTable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        white-space: nowrap;
    }

    #customerTable thead th {
        position: sticky;
        top: 0;
        background-color: rgba(39, 42, 53, 0.95);
        backdrop-filter: blur(4px);
        color: #9ca3af;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 16px;
        border-bottom: 1px solid #2d3342;
        z-index: 10;
    }

    #customerTable tbody td {
        padding: 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid #2d3342;
        vertical-align: middle;
        transition: background-color 0.15s ease;
        color: #f3f4f6;
    }

    #customerTable tbody tr:last-child td {
        border-bottom: none;
    }

    #customerTable tbody tr:hover td {
        background-color: #272a35;
    }

    #customerTable tbody tr.danger td {
        background-color: rgba(239, 68, 68, 0.1);
    }

    #customerTable tbody tr.danger:hover td {
        background-color: rgba(239, 68, 68, 0.2);
    }

    /* Checkbox styling */
    #customerTable input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #3b82f6;
        cursor: pointer;
    }

    /* Circular avatars */
    #customerTable .customer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #2d3342;
        display: inline-block;
        border: 2px solid #2d3342;
    }

    /* Circular contact icons */
    .contact-icons {
        display: flex;
        gap: 8px;
    }

    .btn-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #272a35;
        border: 1px solid #2d3342;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        padding: 0;
    }

    .btn-circle:hover {
        color: #f3f4f6;
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.1);
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
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .status-suspended {
        background-color: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .status-disabled {
        background-color: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* Modernized Action Buttons (Button Group) */
    .action-group {
        display: inline-flex;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #2d3342;
    }

    .btn-action {
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background-color: transparent;
        color: #f3f4f6;
        border-right: 1px solid #2d3342;
        transition: background-color 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-action:last-child {
        border-right: none;
    }

    .btn-action:hover {
        background-color: #272a35;
    }

    .btn-action.view { color: #14b8a6; }
    .btn-action.edit { color: #06b6d4; }
    .btn-action.sync { color: #14b8a6; }
    .btn-action.recharge { color: #3b82f6; }

    .btn-action:hover.view { background-color: rgba(20, 184, 166, 0.1); }
    .btn-action:hover.edit { background-color: rgba(6, 182, 212, 0.1); }
    .btn-action:hover.sync { background-color: rgba(20, 184, 166, 0.1); }
    .btn-action:hover.recharge { background-color: rgba(59, 130, 246, 0.1); }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">
                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" title="save"
                        href="{Text::url('customers/csv&token=', $csrf_token)}"
                        onclick="return ask(this, '{Lang::T("This will export to CSV")}?')"><span
                            class="glyphicon glyphicon-download" aria-hidden="true"></span> CSV</a>
                </div>
                {/if}
                {Lang::T('Manage Contact')}
            </div>
            <div class="panel-body">
                <form id="site-search" method="post" action="{Text::url('customers')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <div class="md-whiteframe-z1 mb20 text-center" style="padding: 15px">
                        <div class="col-lg-4">
                            <div class="input-group">
                                <span class="input-group-addon">{Lang::T('Order ')}&nbsp;&nbsp;</span>
                                <div class="row row-no-gutters">
                                    <div class="col-xs-8">
                                        <select class="form-control" id="order" name="order">
                                            <option value="username" {if $order eq 'username' }selected{/if}>
                                                {Lang::T('Username')}</option>
                                            <option value="fullname" {if $order eq 'fullname' }selected{/if}>
                                                {Lang::T('First Name')}</option>
                                            <option value="lastname" {if $order eq 'lastname' }selected{/if}>
                                                {Lang::T('Last Name')}</option>
                                            <option value="created_at" {if $order eq 'created_at' }selected{/if}>
                                                {Lang::T('Created Date')}</option>
                                            <option value="balance" {if $order eq 'balance' }selected{/if}>
                                                {Lang::T('Balance')}</option>
                                            <option value="status" {if $order eq 'status' }selected{/if}>
                                                {Lang::T('Status')}</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-4">
                                        <select class="form-control" id="orderby" name="orderby">
                                            <option value="asc" {if $orderby eq 'asc' }selected{/if}>
                                                {Lang::T('Ascending')}</option>
                                            <option value="desc" {if $orderby eq 'desc' }selected{/if}>
                                                {Lang::T('Descending')}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <span class="input-group-addon">{Lang::T('Status')}</span>
                                <select class="form-control" id="filter" name="filter">
                                    {foreach $statuses as $status}
                                    <option value="{$status}" {if $filter eq $status }selected{/if}>{Lang::T($status)}
                                    </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="{Lang::T('Search')}..." value="{$search}">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary" type="submit"><span class="fa fa-search"></span>
                                        {Lang::T('Search')}</button>
                                    <button class="btn btn-info" type="submit" name="export" value="csv">
                                        <span class="glyphicon glyphicon-download" aria-hidden="true"></span> CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <a href="{Text::url('customers/add')}" class="btn btn-success text-black btn-block"
                                title="{Lang::T('Add')}">
                                <i class="ion ion-android-add"></i><i class="glyphicon glyphicon-user"></i>
                            </a>
                        </div>
                    </div>
                </form>
                <br>&nbsp;
                <div class="table-responsive table_mobile">
                    <table id="customerTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                                <th>{Lang::T('Username')}</th>
                                <th>Photo</th>
                                <th>{Lang::T('Account Type')}</th>
                                <th>{Lang::T('Full Name')}</th>
                                <th>{Lang::T('Balance')}</th>
                                <th>{Lang::T('Contact')}</th>
                                <th>{Lang::T('Package')}</th>
                                <th>{Lang::T('Service Type')}</th>
                                <th>PPPOE</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created On')}</th>
                                <th style="text-align: right;">{Lang::T('Manage')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $d as $ds}
                            <tr {if $ds['status'] !='Active' }class="danger" {/if}>
                                <td><input type="checkbox" name="customer_ids[]" value="{$ds['id']}"></td>
                                <td onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                    style="cursor:pointer; font-weight: 500;">{$ds['username']}</td>
                                <td>
                                    <a href="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}" target="photo">
                                        <img src="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}.thumb.jpg" class="customer-avatar" alt="">
                                    </a>
                                </td>
                                <td>{$ds['account_type']}</td>
                                <td onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                    style="cursor: pointer;">{$ds['fullname']}</td>
                                <td>{Lang::moneyFormat($ds['balance'])}</td>
                                <td>
                                    <div class="contact-icons">
                                        {if $ds['phonenumber']}
                                        <a href="tel:{$ds['phonenumber']}" class="btn-circle" title="{$ds['phonenumber']}">
                                            <i class="glyphicon glyphicon-earphone"></i>
                                        </a>
                                        {/if}
                                        {if $ds['email']}
                                        <a href="mailto:{$ds['email']}" class="btn-circle" title="{$ds['email']}">
                                            <i class="glyphicon glyphicon-envelope"></i>
                                        </a>
                                        {/if}
                                        {if $ds['coordinates']}
                                        <a href="https://www.google.com/maps/dir//{$ds['coordinates']}/" target="_blank"
                                            class="btn-circle" title="{$ds['coordinates']}">
                                            <i class="glyphicon glyphicon-map-marker"></i>
                                        </a>
                                        {/if}
                                    </div>
                                </td>
                                <td api-get-text="{Text::url('autoload/plan_is_active/')}{$ds['id']}">
                                    <span class="label label-default">&bull;</span>
                                </td>
                                <td>{$ds['service_type']}</td>
                                <td>
                                    {$ds['pppoe_username']}
                                    {if !empty($ds['pppoe_username']) && !empty($ds['pppoe_ip'])}:{/if}
                                    {$ds['pppoe_ip']}
                                </td>
                                <td>
                                    {if $ds['status'] == 'Active'}
                                    <span class="status-pill status-active">{Lang::T($ds['status'])}</span>
                                    {elseif $ds['status'] == 'Suspended'}
                                    <span class="status-pill status-suspended">{Lang::T($ds['status'])}</span>
                                    {elseif $ds['status'] == 'Disabled'}
                                    <span class="status-pill status-disabled">{Lang::T($ds['status'])}</span>
                                    {else}
                                    <span class="status-pill status-active">{Lang::T($ds['status'])}</span>
                                    {/if}
                                </td>
                                <td style="color: #9ca3af;">{Lang::dateTimeFormat($ds['created_at'])}</td>
                                <td style="text-align: right;">
                                    <div class="action-group">
                                        <a href="{Text::url('customers/view/')}{$ds['id']}" id="{$ds['id']}"
                                            class="btn-action view">{Lang::T('View')}</a>
                                        <a href="{Text::url('customers/edit/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}" class="btn-action edit">{Lang::T('Edit')}</a>
                                        <a href="{Text::url('customers/sync/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}" class="btn-action sync">{Lang::T('Sync')}</a>
                                        <a href="{Text::url('plan/recharge/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}" class="btn-action recharge">{Lang::T('Recharge')}</a>
                                    </div>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <div class="row" style="padding: 16px 5px 5px;">
                        <div class="col-lg-3 col-lg-offset-9">
                            <div class="btn-group btn-group-justified" role="group">
                                <!-- <div class="btn-group" role="group">
                                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <button id="deleteSelectedTokens" class="btn btn-danger">{Lang::T('Delete
                                        Selected')}</button>
                                    {/if}
                                </div> -->
                                <div class="btn-group" role="group">
                                    <button id="sendMessageToSelected" class="btn btn-success" style="background-color: #14b8a6; border: none; border-radius: 6px; padding: 10px 24px; font-weight: 500;">{Lang::T('Send
                                        Message')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {include file="pagination.tpl"}
            </div>
        </div>
    </div>
</div>
<!-- Modal for Sending Messages -->
<div id="sendMessageModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="sendMessageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendMessageModalLabel">{Lang::T('Send Message')}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select id="messageType" class="form-control">
                    <option value="all">{Lang::T('All')}</option>
                    <option value="email">{Lang::T('Email')}</option>
                    <option value="inbox">{Lang::T('Inbox')}</option>
                    <option value="sms">{Lang::T('SMS')}</option>
                    <option value="wa">{Lang::T('WhatsApp')}</option>
                </select>
                <br>
                <textarea id="messageContent" class="form-control" rows="4"
                    placeholder="{Lang::T('Enter your message here...')}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{Lang::T('Close')}</button>
                <button type="button" id="sendMessageButton" class="btn btn-primary">{Lang::T('Send Message')}</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Select or deselect all checkboxes
    document.getElementById('select-all').addEventListener('change', function () {
        var checkboxes = document.querySelectorAll('input[name="customer_ids[]"]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    $(document).ready(function () {
        let selectedCustomerIds = [];

        // Collect selected customer IDs when the button is clicked
        $('#sendMessageToSelected').on('click', function () {
            selectedCustomerIds = $('input[name="customer_ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedCustomerIds.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: "{Lang::T('Please select at least one customer to send a message.')}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Open the modal
            $('#sendMessageModal').modal('show');
        });

        // Handle sending the message
        $('#sendMessageButton').on('click', function () {
            const message = $('#messageContent').val().trim();
            const messageType = $('#messageType').val();

            if (!message) {
                Swal.fire({
                    title: 'Error!',
                    text: "{Lang::T('Please enter a message to send.')}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Disable the button and show loading text
            $(this).prop('disabled', true).text('{Lang::T('Sending...')}');

            $.ajax({
                url: '?_route=message/send_bulk_selected',
                method: 'POST',
                data: {
                    customer_ids: selectedCustomerIds,
                    message_type: messageType,
                    message: message
                },
                dataType: 'json',
                success: function (response) {
                    // Handle success response
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: "{Lang::T('Message sent successfully.')}",
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: "{Lang::T('Error sending message: ')}" + response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    $('#sendMessageModal').modal('hide');
                    $('#messageContent').val(''); // Clear the message content
                },
                error: function () {
                    Swal.fire({
                        title: 'Error!',
                        text: "{Lang::T('Failed to send the message. Please try again.')}",
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function () {
                    // Re-enable the button and reset text
                    $('#sendMessageButton').prop('disabled', false).text('{Lang::T('Send Message')}');
                }
            });
        });
    });

    $(document).ready(function () {
        $('#sendMessageModal').on('show.bs.modal', function () {
            $(this).attr('inert', 'true');
        });
        $('#sendMessageModal').on('shown.bs.modal', function () {
            $('#messageContent').focus();
            $(this).removeAttr('inert');
        });
        $('#sendMessageModal').on('hidden.bs.modal', function () {
            // $('#button').focus();
        });
    });
</script>
{include file = "sections/footer.tpl" }