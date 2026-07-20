<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{Lang::T('Log in to Member Panel')} - {$_c['CompanyName']}</title>
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
            max-width: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
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

        /* Cards */
        .card {
            background-color: #1b1b23;
            border-radius: 8px;
            padding: 40px;
            border: 1px solid #393841;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .announcement-card {
            border-left: 4px solid #6764f2;
            min-height: 400px;
        }

        /* Typography */
        .announcement-label {
            color: #6764f2;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-speaker::before {
            content: '📢';
            font-style: normal;
        }

        .announcement-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
        }

        .announcement-content {
            color: #e2e2e9;
            line-height: 1.6;
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 24px;
            color: #ffffff;
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
        .button-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .button-row.single {
            grid-template-columns: 1fr;
        }

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
        }

        /* Footer Links */
        .link-group {
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

        .meta-links {
            margin-top: 16px;
            color: #61616b;
            font-size: 0.8rem;
        }

        .meta-links span {
            margin: 0 8px;
        }

        .copyright {
            margin-top: 40px;
            font-size: 0.75rem;
            color: #61616b;
            text-align: center;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #1b1b23;
            border: 1px solid #393841;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 16px;
            border-bottom: 1px solid #393841;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            color: #e2e2e9;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
            color: #e2e2e9;
        }

        .modal-footer {
            padding: 16px;
            border-top: 1px solid #393841;
            text-align: right;
        }
    </style>
</head>
<body>
<main class="container">
    <header class="brand-container">
        <img alt="{$_c['CompanyName']} Logo" class="brand-logo" src="{$login_logo}"/>
    </header>

    <div class="main-grid">
        <!-- Announcement Panel -->
        <section class="card announcement-card">
            <div class="announcement-label">
                <i class="icon-speaker"></i> {Lang::T('Announcement')}
            </div>
            <div class="announcement-content">
                {$Announcement = "{$PAGES_PATH}/Announcement.html"}
                {if file_exists($Announcement)}
                    {include file=$Announcement}
                {else}
                    <h1 class="announcement-title">{Lang::T('Welcome')}</h1>
                {/if}
            </div>
        </section>

        <!-- Login Panel -->
        <section class="card">
            <h2 class="form-title">{Lang::T('Log in to Member Panel')}</h2>
            <form action="{Text::url('login/post')}" method="post">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                
                <div class="form-group">
                    <label class="label" for="username">
                        {if $_c['registration_username'] == 'phone'}
                            {Lang::T('Phone Number')}
                        {elseif $_c['registration_username'] == 'email'}
                            {Lang::T('Email')}
                        {else}
                            {Lang::T('Username')}
                        {/if}
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            {if $_c['registration_username'] == 'phone'}
                                📞
                            {elseif $_c['registration_username'] == 'email'}
                                ✉️
                            {else}
                                👤
                            {/if}
                        </span>
                        <input class="input-field" id="username" name="username" 
                            placeholder="{if $_c['country_code_phone']!= '' || $_c['registration_username'] == 'phone'}{$_c['country_code_phone']} {Lang::T('Phone Number')}{elseif $_c['registration_username'] == 'email'}{Lang::T('Email')}{else}{Lang::T('Username')}{/if}" 
                            required type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="label" for="password">{Lang::T('Password')}</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input class="input-field" id="password" name="password" placeholder="{Lang::T('Password')}" required type="password">
                    </div>
                </div>

                <div class="button-row {if $_c['disable_registration'] == 'noreg'}single{/if}">
                    {if $_c['disable_registration'] != 'noreg'}
                        <a class="btn btn-outline" href="{Text::url('register')}">
                            <span>👤+</span> {Lang::T('Register')}
                        </a>
                    {/if}
                    <button class="btn btn-primary" type="submit">
                        <span>➜</span> {Lang::T('Login')}
                    </button>
                </div>
            </form>

            <div class="link-group">
                <a class="footer-link" href="{Text::url('forgot')}">{Lang::T('Forgot Password')}</a>
                <div class="meta-links">
                    <a class="footer-link" href="javascript:showPrivacy()">{Lang::T('Privacy')}</a>
                    <span>•</span>
                    <a class="footer-link" href="javascript:showTaC()">{Lang::T('Terms & Conditions')}</a>
                </div>
            </div>
        </section>
    </div>

    <footer class="copyright">
        © 2026 {$_c['CompanyName']} - {Lang::T('High-Performance Network Console')}
    </footer>
</main>

<!-- Modal -->
<div class="modal" id="HTMLModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="HTMLModal_konten"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
<script>
    {if isset($notify)}
        Swal.fire({
            icon: '{if $notify_t == "s"}success{else}warning{/if}',
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

    function showPrivacy() {
        const modal = document.getElementById('HTMLModal');
        const modalBody = document.getElementById('HTMLModal_konten');
        const modalTitle = document.getElementById('modalTitle');
        
        modalTitle.textContent = '{Lang::T('Privacy Policy')}';
        modalBody.innerHTML = '<p>Loading...</p>';
        modal.classList.add('show');
        
        fetch(appUrl + '/pages/Privacy.html')
            .then(response => response.text())
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(() => {
                modalBody.innerHTML = '<p>{Lang::T('Unable to load privacy policy')}</p>';
            });
    }

    function showTaC() {
        const modal = document.getElementById('HTMLModal');
        const modalBody = document.getElementById('HTMLModal_konten');
        const modalTitle = document.getElementById('modalTitle');
        
        modalTitle.textContent = '{Lang::T('Terms & Conditions')}';
        modalBody.innerHTML = '<p>Loading...</p>';
        modal.classList.add('show');
        
        fetch(appUrl + '/pages/Terms.html')
            .then(response => response.text())
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(() => {
                modalBody.innerHTML = '<p>{Lang::T('Unable to load terms and conditions')}</p>';
            });
    }

    function closeModal() {
        document.getElementById('HTMLModal').classList.remove('show');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('HTMLModal');
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }
</script>

{if $_c['tawkto'] != ''}
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src='https://embed.tawk.to/{$_c['tawkto']}';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
{/if}
</body>
</html>