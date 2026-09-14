<!DOCTYPE html>
<html lang="en" {if !empty($config['font_size'])} class="{$config['font_size']}" {/if}>
<head>
    <meta charset="utf-8">
    <title>
        {$_title}
    </title>
    <meta name="description" content="{$config['CompanyName']}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-32','icon-32x32.png')}" sizes="32x32" />
    <link rel="icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-192','icon-192x192.png')}" sizes="192x192" />
    <link rel="apple-touch-icon" href="{{APP_URL}}/storage/system/{get_or_default($config,'icon-180','icon-180x180.png')}" />
    <meta name="msapplication-TileImage" content="{{APP_URL}}/storage/system/{get_or_default($config,'icon-270','icon-270x270.png')}" />

    {if APP_STAGE == 'Dev'}

        {if $config['rtl'] eq '1'}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app-rtl.min.css?v={{_raid()}}">
        {else}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app.min.css?v={{_raid()}}">
        {/if}

        <link href="{$theme}default/css/themes/{$config['nstyle']}.css?v={{_raid()}}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {else}

        {if $config['rtl'] eq '1'}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app-rtl.min.css?v={$config['version']}">
        {else}
            <link id="css_app" rel="stylesheet" media="screen, print" href="{{APP_URL}}/ui/theme/default/css/app.min.css?v={$config['version']}">
        {/if}

        <link href="{$theme}default/css/themes/{$config['nstyle']}.css?v={$config['version']}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    {/if}


    <style>

        .alert.alert-danger.fade{
            opacity: 1;
        }
        .alert.alert-success.fade{
            opacity: 1;
        }

        #ribbon-container {
            position: absolute;

            right: -15px;

        }


        #ribbon-container:before {
            content: "";
            height: 0;
            width: 0;
            display: block;
            position: absolute;
            top: 3px;
            left: 0;
            border-top: 20px solid rgba(0,0,0,.3);
            border-bottom: 20px solid rgba(0,0,0,.3);
            border-right: 20px solid rgba(0,0,0,.3);
            border-left: 20px solid transparent;
        }
        #ribbon-container a {
            display: block;
            padding: 12px;
            position: relative;
            background: #0089d0;
            overflow: visible;
            height: 40px;
            margin-left: 29px;
            color: #fff;
            text-decoration: none;
        }
        #ribbon-container a:before {
            content: "";
            height: 0;
            width: 0;
            display: block;
            position: absolute;
            top: 0;
            left: -20px;
            border-top: 20px solid #0089d0;
            border-bottom: 20px solid #0089d0;
            border-right: 20px solid transparent;
            border-left: 20px solid transparent;
        }
        #ribbon-container a:after {
            content: "";
            height: 0;
            width: 0;
            display: block;
            position: absolute;
            bottom: -15px;
            right: 0;
            border-top: 15px solid #004a70;
            border-right: 15px solid transparent;
        }


        .alert-danger {
            color: #e7026e;
            background-color: #ffe5f1;
        }


        .alert-success {
            color: #45a197;
            background-color: #f7fdfc;
            border-color: #a3ebe4;
        }



        .pristine-error.text-help {
            color: red;
        }

        .text-info {
            color: #7553F7!important;

        }
        .text-success {
            color: #15CD60!important;

        }
        .text-danger {
            color: #F50057!important;

        }

        .bg-info {
            color: #727DF5!important;

        }

        .table-bordered>thead>tr, .table-bordered>thead>tr>th{
            border-bottom-width: 0;
            border-top-width: 0;
        }
        .table-bordered>tbody>tr:first-child {
            border-top-width: 0;
        }

        .table>:not(:first-child) {
            border-top: none;
        }

        .bg-primary {
            background-color: #7239EA !important;
        }

        .bg-secondary {
            background-color: #f0bc74 !important;
        }

        .bg-tertiary {
            background-color: #31316A !important;
        }

        .bg-success {
            background-color: #35C28F !important;
        }

        .bg-info {
            background-color: #2361ce !important;
        }

        .bg-warning {
            background-color: #f3c78e !important;
        }

        .bg-danger {
            background-color: #E11D48 !important;
        }

        .bg-white {
            background-color: #ffffff !important;
        }

        .bg-indigo {
            background-color: #4F46E5 !important;
        }

        .bg-purple {
            background-color: #7C3AED !important;
        }

        .bg-yellow-100 {
            background-color: #ffeed6 !important;
        }

        .bg-yellow-200 {
            background-color: #f5dbb8 !important;
        }




        .page-content {
            background-color: #f2f4f6;
        }

        .page-content {
                padding: 1rem 2em;
            }

        .text-danger, .text-danger:hover {
            color: #E11D48 !important;
        }
        .text-secondary {
            color: #f0bc74 !important;
        }

        .text-tertiary {
            color: #31316A !important;
        }

        .text-success {
            color: #35C28F !important;
        }



        .text-warning {
            color: #f3c78e !important;
        }

        .text-danger {
            color: #E11D48 !important;
        }

        .text-white {
            color: #ffffff !important;
        }

        .text-indigo {
            color: #4F46E5 !important;
        }
        .text-blue {
            color: #029EF7 !important;
        }

        .text-purple {
            color: #7C3AED !important;
        }
        .symbol.symbol-light-warning .symbol-label {
            background: #FFF7DD!important;
        }

        .symbol.symbol-light-success .symbol-label {
            background: #E9FAEE!important;
        }

        .symbol.symbol-light-danger .symbol-label {
            background: #FFF5F8!important;
        }

        .symbol.symbol-light-primary .symbol-label {
            background: #F8F5FF!important;
        }
        .symbol.symbol-light-info .symbol-label {
            background: #F2FAFD!important;
        }
        .symbol.symbol-info .symbol-label {
            background: #7553F7!important;
        }
        .symbol.symbol-danger .symbol-label {
            background: #E11C48!important;
        }
        .symbol.symbol-success .symbol-label {
            background: #14B981!important;
        }

        .symbol.symbol-blue .symbol-label {
            background: #029EF7!important;
        }
        .symbol.symbol-warning .symbol-label {
            background: #F3C78E!important;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>* {
            --bs-table-accent-bg: rgb(207 206 241 / 5%);
            color: var(--bs-table-striped-color)!important;

        }

        .btn{
            border-radius: 6px;
            transition: all .2s ease-in-out;
        }

        .btn-primary {
            box-shadow: 0 0.125rem 0.25rem 0 rgb(118 118 118 / 40%);
        }

        .table.light-bordered{
            border: .0625rem solid rgba(231,234,243,.7);
        }

        .thead-light th {
            background-color: #f8fafd;
            padding: 12px;
        }


    </style>

    {block name=head_extras_from_layout}{/block}
    {block name=head}{/block}






    <script>
        var base_url = '{$_url}';
        var block_msg = '<div class="md-preloader text-center"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="32" width="32" viewbox="0 0 75 75"><circle cx="37.5" cy="37.5" r="33.5" stroke-width="6"/></svg></div>';
    </script>



    <script>
        window.clx = {
            base_url: '{$_url}',
            i18n: {
                yes: '{$_L['Yes']}',
                no: '{$_L['No']}',
                are_you_sure: '{$_L['are_you_sure']}'
            }
        };

        window.home_currency = {getCurrencyConfigJson($config['home_currency'])};

        var _L = [];
        _L['Save'] = '{$_L['Save']}';
        _L['Submit'] = '{$_L['Submit']}';
        _L['Loading'] = '{$_L['Loading']}';
        _L['OK'] = '{$_L['OK']}';
        _L['Cancel'] = '{$_L['Cancel']}';
        _L['Close'] = '{$_L['Close']}';
        _L['are_you_sure'] = '{$_L['are_you_sure']}';
        _L['Saved Successfully'] = '{$_L['Saved Successfully']}';
        _L['Empty'] = '{$_L['Empty']}';
    </script>

    <style>
        body {
            overflow-x: hidden;
        }
        .show.dropdown-menu-animated {
            transform: scale(1)!important;
            -webkit-transform-origin: 29px -50px;
            transform-origin: 29px -50px;
            opacity: 1;
            visibility: visible;
        }
    </style>

</head>
<body class="mod-bg-1 mod-nav-link  clx-fixed-navigation

{if !empty($config['clx-navigation-type-top'])}
clx-navigation-type-top
{/if}

" id="cloudonex_body">
<script>
    'use strict';

    var classHolder = document.getElementsByTagName("BODY")[0],
        themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) :
            { },
        themeURL = themeSettings.themeURL || '',
        themeOptions = themeSettings.themeOptions || '';
    if (themeSettings.themeOptions)
    {
        classHolder.className = themeSettings.themeOptions;
    }
    else
    {
    }
    var saveSettings = function()
    {
        themeSettings.themeOptions = String(classHolder.className).split(/[^\w-]+/).filter(function(item)
        {
            return /^(nav|header|footer|mod|clx|display)-/i.test(item);
        }).join(' ');
        localStorage.setItem('themeSettings', JSON.stringify(themeSettings));

    }
    var resetSettings = function()
    {
        localStorage.setItem("themeSettings", "");
    }

</script>


<div class="page-wrapper">

    <div class="page-inner">
        {block name="content_body"}{/block}
    </div>

</div>


<p id="js-color-profile" class="d-none">
    <span class="color-primary-50"></span>
    <span class="color-primary-100"></span>
    <span class="color-primary-200"></span>
    <span class="color-primary-300"></span>
    <span class="color-primary-400"></span>
    <span class="color-primary-500"></span>
    <span class="color-primary-600"></span>
    <span class="color-primary-700"></span>
    <span class="color-primary-800"></span>
    <span class="color-primary-900"></span>
    <span class="color-info-50"></span>
    <span class="color-info-100"></span>
    <span class="color-info-200"></span>
    <span class="color-info-300"></span>
    <span class="color-info-400"></span>
    <span class="color-info-500"></span>
    <span class="color-info-600"></span>
    <span class="color-info-700"></span>
    <span class="color-info-800"></span>
    <span class="color-info-900"></span>
    <span class="color-danger-50"></span>
    <span class="color-danger-100"></span>
    <span class="color-danger-200"></span>
    <span class="color-danger-300"></span>
    <span class="color-danger-400"></span>
    <span class="color-danger-500"></span>
    <span class="color-danger-600"></span>
    <span class="color-danger-700"></span>
    <span class="color-danger-800"></span>
    <span class="color-danger-900"></span>
    <span class="color-warning-50"></span>
    <span class="color-warning-100"></span>
    <span class="color-warning-200"></span>
    <span class="color-warning-300"></span>
    <span class="color-warning-400"></span>
    <span class="color-warning-500"></span>
    <span class="color-warning-600"></span>
    <span class="color-warning-700"></span>
    <span class="color-warning-800"></span>
    <span class="color-warning-900"></span>
    <span class="color-success-50"></span>
    <span class="color-success-100"></span>
    <span class="color-success-200"></span>
    <span class="color-success-300"></span>
    <span class="color-success-400"></span>
    <span class="color-success-500"></span>
    <span class="color-success-600"></span>
    <span class="color-success-700"></span>
    <span class="color-success-800"></span>
    <span class="color-success-900"></span>
    <span class="color-fusion-50"></span>
    <span class="color-fusion-100"></span>
    <span class="color-fusion-200"></span>
    <span class="color-fusion-300"></span>
    <span class="color-fusion-400"></span>
    <span class="color-fusion-500"></span>
    <span class="color-fusion-600"></span>
    <span class="color-fusion-700"></span>
    <span class="color-fusion-800"></span>
    <span class="color-fusion-900"></span>
</p>

<input type="hidden" id="_url" name="_url" value="{$_url}">
<input type="hidden" id="_df" name="_df" value="{$config['df']}">

{if APP_STAGE == 'Dev'}
    <script src="{{APP_URL}}/ui/theme/default/js/app.min.js?v={_raid()}"></script>
{else}
    <script src="{{APP_URL}}/ui/theme/default/js/app.min.js?v={$config['version']}"></script>
{/if}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    if (typeof window.ray === 'undefined') {
        window.ray = function() {
            console.log('ray is available only on the dev mode.')
        }
    }
    (function (factory) {
        if (typeof define === "function" && define.amd) {
            define(["jquery"], factory);
        } else {
            factory(jQuery);
        }
    })(function ($) {

        "use strict";

        $.fn.datepicker.setDefaults({
            autoClose: false,
            dateFormat: "mm/dd/yy",
            days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
            months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            showMonthAfterYear: false,
            viewStart: 0, // days
            weekStart: 0, // Sunday
            yearSuffix: ""
        });
    });
</script>

{block name=script}{/block}
{block name=scripts}{/block}

</body>
</html>
