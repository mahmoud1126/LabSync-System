<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LabSync | Sign In</title>
    <link href="/css/style.css" rel="stylesheet">
    <style>
        /* Page-specific tweaks — keep aligned with the global LabSync palette. */
        body.auth-body {
            background:
                radial-gradient(circle at 15% 20%, rgba(39, 128, 227, 0.08), transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(153, 84, 187, 0.06), transparent 40%),
                #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border: 1px solid var(--ls-border);
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(20, 50, 90, 0.08);
            padding: 36px 36px 32px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .auth-brand .logo-mark {
            width: 44px;
            height: 44px;
            font-size: 16px;
        }
        .auth-brand .logo-text {
            font-size: 1.55rem;
        }

        .auth-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 26px;
        }

        .auth-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #555;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .input-group-text {
            background: #f0f4f9;
            border: 1px solid var(--ls-border);
            border-right: 0;
            color: var(--ls-primary);
        }

        .form-control.auth-input {
            border: 1px solid var(--ls-border);
            border-left: 0;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control.auth-input:focus {
            border-color: var(--ls-primary);
            box-shadow: 0 0 0 0.15rem rgba(39, 128, 227, 0.15);
            background: #fff;
        }
        .input-group:focus-within .input-group-text {
            border-color: var(--ls-primary);
            color: #fff;
            background: var(--ls-primary);
        }

        .btn-auth {
            width: 100%;
            padding: 11px;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0;
            background: var(--ls-primary);
            color: #fff;
            border: none;
        }
        .btn-auth:hover,
        .btn-auth:focus,
        .btn-auth:active {
            background: var(--ls-primary);
            color: #fff;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #aab2bd;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 22px 0 16px;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--ls-border);
        }
        .auth-divider::before { margin-right: 12px; }
        .auth-divider::after  { margin-left: 12px;  }

        .demo-users {
            background: #fafbfc;
            border: 1px dashed var(--ls-border);
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 0.8rem;
            color: #5a6470;
        }
        .demo-users .demo-title {
            font-weight: 600;
            color: var(--ls-dark);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
        }
        .demo-users code {
            background: #eef3f9;
            color: var(--ls-primary);
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        .demo-users .demo-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .demo-users .demo-list span {
            background: #fff;
            border: 1px solid var(--ls-border);
            color: #555;
            padding: 2px 9px;
            border-radius: 12px;
            font-size: 0.74rem;
            font-family: monospace;
        }

        .auth-flash {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }
        .auth-flash.error {
            background: #fdecef;
            border: 1px solid #f5c2c7;
            color: #842029;
        }
        .auth-flash.success {
            background: #e6f6e6;
            border: 1px solid #b7e4b7;
            color: #155724;
        }

        .auth-footer {
            text-align: center;
            color: #9aa3ad;
            font-size: 0.75rem;
            margin-top: 22px;
        }
    </style>
</head>
<body class="auth-body">

    <div class="auth-card">

        <!-- Brand -->
        <div class="auth-brand">
            <div class="logo-mark">LS</div>
            <span class="logo-text">Lab<span>Sync</span></span>
        </div>
        

        <!-- Flash messages -->
        <?php if (!empty($flash)): ?>
            <div class="auth-flash <?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
                <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?>"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="/LabSync-System/login" autocomplete="on">

            <div class="mb-3">
                <div class="auth-label">Username</div>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <input
                        type="text"
                        name="userName"
                        class="form-control auth-input"
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                        autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="auth-label">Password</div>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input
                        type="password"
                        name="userPassword"
                        class="form-control auth-input"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>
                </div>
            </div>

            <button type="submit" class="btn-auth mt-2">
                Sign In
            </button>
        </form>
    </div>

</body>
</html>
