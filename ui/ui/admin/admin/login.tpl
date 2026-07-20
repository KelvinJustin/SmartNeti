<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{Lang::T('Admin Login')} - {$_c['CompanyName']}</title>
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/smartneti-logo.png" type="image/x-icon" />
    
    <script>
        var appUrl = '{$app_url}';
    </script>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #0d0e15;
            color: #e2e2e9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        /* Branding */
        .brand-container {
            text-align: center;
        }

        .brand-logo {
            height: 60px;
            width: auto;
            margin-bottom: 10px;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .admin-badge {
            display: inline-block;
            background-color: #6764f2;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Card */
        .card {
            background-color: #1b1b23;
            border-radius: 8px;
            padding: 40px;
            border: 1px solid #393841;
            width: 100%;
        }

        /* Typography */
        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 24px;
            color: #ffffff;
            text-align: center;
        }

        .notification {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .notification.error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
        }

        .notification.success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid #22c55e;
            color: #22c55e;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .label {
            display: block;
            font-size: 0.85rem;
            color: #a0a0ab;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            color: #61616b;
            font-size: 14px;
        }

        .input-field {
            width: 100%;
            background-color: #12131a;
            border: 1px solid #393841;
            border-radius: 6px;
            padding: 12px 12px 12px 40px;
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: #6764f2;
        }

        /* Buttons */
        .btn {
            cursor: pointer;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.2s;
            border: none;
            text-decoration: none;
            width: 100%;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background-color: #6764f2;
            color: #ffffff;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #393841;
            color: #e2e2e9;
            margin-top: 16px;
        }

        /* Footer Links */
        .back-link {
            margin-top: 24px;
            text-align: center;
        }

        .footer-link {
            color: #6764f2;
            text-decoration: none;
            font-size: 0.85rem;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .footer-link:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .copyright {
            margin-top: 40px;
            font-size: 0.75rem;
            color: #61616b;
            text-align: center;
        }
    </style>
</head>
<body>
<main class="container">
    <header class="brand-container">
        <div class="brand-name">{$_c['CompanyName']}</div>
        <span class="admin-badge">Admin Panel</span>
    </header>

    <section class="card">
        <h2 class="form-title">{Lang::T('Enter Admin Area')}</h2>
        
        {if isset($notify)}
            <div class="notification {if $notify_t == 's'}success{else}error{/if}">
                {$notify}
            </div>
        {/if}
        
        <form action="{Text::url('admin/post')}" method="post">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            
            <div class="form-group">
                <label class="label" for="username">{Lang::T('Username')}</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input class="input-field" id="username" name="username" placeholder="{Lang::T('Username')}" required type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="label" for="password">{Lang::T('Password')}</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input class="input-field" id="password" name="password" placeholder="{Lang::T('Password')}" required type="password">
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <span>➜</span> {Lang::T('Login')}
            </button>

            <a class="btn btn-outline" href="{Text::url('login')}">
                <span>←</span> {Lang::T('Go Back')}
            </a>
        </form>
    </section>

    <footer class="copyright">
        © 2026 {$_c['CompanyName']} - {Lang::T('Admin Console')}
    </footer>
</main>

<script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
<script>
    {if isset($notify)}
        Swal.fire({
            icon: '{if $notify_t == "s"}success{else}error{/if}',
            title: '{$notify}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    {/if}
</script>
</body>
</html>
