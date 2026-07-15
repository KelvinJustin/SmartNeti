<!DOCTYPE html>
<html lang="en" class="has-aside-left has-aside-mobile-transition has-navbar-fixed-top has-aside-expanded">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>

    <script>
        var appUrl = '{$app_url}';
    </script>

    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
    <script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill.customer.css?2025.2.4" />

    <style>

    </style>

    {if isset($xheader)}
        {$xheader}
    {/if}

</head>

<body class="hold-transition modern-skin-dark sidebar-mini">
    <div class="wrapper">
        <header class="main-header" style="position:fixed; width: 100%">
            <a href="{Text::url('home')}" class="logo">
                <span class="logo-mini"><b>{$_c['CompanyName']|substr:0:1}</b></span>
                <span class="logo-lg">{$_c['CompanyName']}</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <label class="switch">
                                <span class="sun"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="#ffd43b"><circle r="5" cy="12" cx="12"></circle><path d="m21 13h-1a1 1 0 0 1 0-2h1a1 1 0 0 1 0 2zm-17 0h-1a1 1 0 0 1 0-2h1a1 1 0 0 1 0 2zm13.66-5.66a1 1 0 0 1 -.66-.29 1 1 0 0 1 0-1.41l.71-.71a1 1 0 1 1 1.41 1.41l-.71.71a1 1 0 0 1 -.75.29zm-12.02 12.02a1 1 0 0 1 -.71-.29 1 1 0 0 1 0-1.41l.71-.66a1 1 0 0 1 1.41 1.41l-.71.71a1 1 0 0 1 -.7.24zm6.36-14.36a1 1 0 0 1 -1-1v-1a1 1 0 0 1 2 0v1a1 1 0 0 1 -1 1zm0 17a1 1 0 0 1 -1-1v-1a1 1 0 0 1 2 0v1a1 1 0 0 1 -1 1zm-5.66-14.66a1 1 0 0 1 -.7-.29l-.71-.71a1 1 0 0 1 1.41-1.41l.71.71a1 1 0 0 1 0 1.41 1 1 0 0 1 -.71.29zm12.02 12.02a1 1 0 0 1 -.7-.29l-.66-.71a1 1 0 0 1 1.36-1.36l.71.71a1 1 0 0 1 0 1.41 1 1 0 0 1 -.71.24z"></path></g></svg></span>
                                <span class="moon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="m223.5 32c-123.5 0-223.5 100.3-223.5 224s100 224 223.5 224c60.6 0 115.5-24.2 155.8-63.4 5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6-96.9 0-175.5-78.8-175.5-176 0-65.8 36-123.1 89.3-153.3 6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z"></path></svg></span>   
                                <input type="checkbox" class="input" id="themeToggle">
                                <span class="slider"></span>
                            </label>
                        </li>
                        <li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <i class="fa fa-flag-o"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu" api-get-text="{Text::url('autoload_user/language&select=',$user_language)}"></ul>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-envelope-o"></i>
                                <span class="label label-warning"
                                    api-get-text="{Text::url('autoload_user/inbox_unread')}"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu" api-get-text="{Text::url('autoload_user/inbox')}"></ul>
                                </li>
                                <li class="footer"><a href="{Text::url('mail')}">{Lang::T('Inbox')}</a></li>
                            </ul>
                        </li>
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                {if $_c['enable_balance'] == 'yes'}
                                    <span
                                        style="color: whitesmoke;">&nbsp;{Lang::moneyFormat($_user['balance'])}&nbsp;</span>
                                {else}
                                    <span>{$_user['fullname']}</span>
                                {/if}
                                <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                    onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" class="user-image"
                                    alt="User Image">
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header">
                                    <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                        onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" class="img-circle"
                                        alt="User Image">

                                    <p>
                                        {$_user['fullname']}
                                        <small>{$_user['phonenumber']}<br>
                                            {$_user['email']}</small>
                                    </p>
                                </li>
                                <li class="user-body">
                                    <div class="row">
                                        <div class="col-xs-7 text-center text-sm">
                                            <a href="{Text::url('accounts/change-password')}"><i class="ion ion-settings"></i>
                                                {Lang::T('Change Password')}</a>
                                        </div>
                                        <div class="col-xs-5 text-center text-sm">
                                            <a href="{Text::url('accounts/profile')}"><i class="ion ion-person"></i>
                                                {Lang::T('My Account')}</a>
                                        </div>
                                    </div>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-right">
                                        <a href="{Text::url('logout')}" class="btn btn-default btn-flat"><i
                                                class="ion ion-power"></i> {Lang::T('Logout')}</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <aside class="main-sidebar" style="position:fixed;">
            <section class="sidebar">
                <ul class="sidebar-menu" data-widget="tree">
                    <li {if $_system_menu eq 'home'}class="active" {/if}>
                        <a href="{Text::url('home')}">
                            <i class="ion ion-monitor"></i>
                            <span>{Lang::T('Dashboard')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_DASHBOARD}
                    <li {if $_system_menu eq 'inbox'}class="active" {/if}>
                        <a href="{Text::url('mail')}">
                            <i class="fa fa-envelope"></i>
                            <span>{Lang::T('Inbox')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_INBOX}
                    {if $_c['disable_voucher'] != 'yes'}
                        <li {if $_system_menu eq 'voucher'}class="active" {/if}>
                            <a href="{Text::url('voucher/activation')}">
                                <i class="fa fa-ticket"></i>
                                <span>Voucher</span>
                            </a>
                        </li>
                    {/if}
                    {if $_c['payment_gateway'] != 'none' or $_c['payment_gateway'] == '' }
                        {if $_c['enable_balance'] == 'yes'}
                            <li {if $_system_menu eq 'balance'}class="active" {/if}>
                                <a href="{Text::url('order/balance')}">
                                    <i class="ion ion-ios-cart"></i>
                                    <span>{Lang::T('Buy Balance')}</span>
                                </a>
                            </li>
                        {/if}
                        <li {if $_system_menu eq 'package'}class="active" {/if}>
                            <a href="{Text::url('order/package')}">
                                <i class="ion ion-ios-cart"></i>
                                <span>{Lang::T('Buy Package')}</span>
                            </a>
                        </li>
                        <li {if $_system_menu eq 'history'}class="active" {/if}>
                            <a href="{Text::url('order/history')}">
                                <i class="fa fa-file-text"></i>
                                <span>{Lang::T('Payment History')}</span>
                            </a>
                        </li>
                    {/if}
                    {$_MENU_AFTER_ORDER}
                    <li {if $_system_menu eq 'list-activated'}class="active" {/if}>
                        <a href="{Text::url('voucher/list-activated')}">
                            <i class="fa fa-list-alt"></i>
                            <span>{Lang::T('Activation History')}</span>
                        </a>
                    </li>
                    {$_MENU_AFTER_HISTORY}
                </ul>
            </section>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    {$_title}
                </h1>
            </section>
            <section class="content">


                {if isset($notify)}
                    <script>
                        // Display SweetAlert toast notification
                        Swal.fire({
                            icon: '{if $notify_t == "s"}success{else}warning{/if}',
                            title: '{$notify}',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                    </script>
{/if}
