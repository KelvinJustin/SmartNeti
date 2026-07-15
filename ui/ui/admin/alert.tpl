<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{ucwords(Lang::T($type))} - {$_c['CompanyName']}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <meta http-equiv="refresh" content="{$time}; url={$url}">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #0d0e15;
            color: #e3e1ec;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
        }

        .card {
            background-color: #1b1b23;
            border-radius: 1.5rem;
            padding: 32px;
            border: 1px solid #393841;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .icon-container {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .icon-circle.success {
            background-color: rgba(103, 100, 242, 0.1);
        }

        .icon-circle.error {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .icon-circle.warning {
            background-color: rgba(251, 191, 36, 0.1);
        }

        .icon-circle.info {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            text-align: center;
            margin-bottom: 16px;
        }

        .message {
            font-size: 16px;
            color: #c7c4d7;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .button {
            width: 100%;
            padding: 12px 24px;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            border: none;
            transition: opacity 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .button:hover {
            opacity: 0.9;
        }

        .button.success {
            background-color: #6764f2;
            color: #ffffff;
        }

        .button.error {
            background-color: #ef4444;
            color: #ffffff;
        }

        .button.warning {
            background-color: #fbbf24;
            color: #0d0e15;
        }

        .button.info {
            background-color: #3b82f6;
            color: #ffffff;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
            color: #61616b;
            text-align: center;
        }

        @media (max-width: 768px) {
            .card {
                padding: 24px;
            }

            .icon-circle {
                width: 64px;
                height: 64px;
                font-size: 32px;
            }

            .title {
                font-size: 20px;
            }

            .message {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="icon-container">
                <div class="icon-circle {$type}">
                    {if $type == 'success'}✓{elseif $type == 'error'}✕{elseif $type == 'warning'}⚠{else}ℹ{/if}
                </div>
            </div>
            <h1 class="title">{ucwords(Lang::T($type))}</h1>
            <p class="message">{$text}</p>
            <a href="{$url}" id="button" class="button {$type}">
                {Lang::T('Click Here')} <span id="timer">({$time})</span>
            </a>
        </div>
        <div class="footer">
            {$_c['CompanyName']}
        </div>
    </div>

    <script>
        var time = {$time};
        timer();

        function timer() {
            setTimeout(() => {
                time--;
                if (time > -1) {
                    document.getElementById("timer").textContent = "(" + time + ")";
                    timer();
                }
            }, 1000);
        }
    </script>
</body>

</html>