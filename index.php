<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
mb_internal_encoding('UTF-8');
mb_language('Turkish');

// Zaten giriş yapılmışsa admin'e yönlendir
if (isset($_SESSION['hmts_logged_in'])) {
    header("Location: frontend/admin/index.php");
    exit;
}

require_once 'backend/baglanti.php';

$hata = '';

// GİRİŞ İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['giris'])) {
    $kullanici = mysqli_real_escape_string($baglanti, trim($_POST['kullanici_adi'] ?? ''));
    $sifre     = trim($_POST['sifre'] ?? '');

    if ($kullanici !== '' && $sifre !== '') {
        $sorgu = mysqli_query($baglanti, "SELECT * FROM personel WHERE kullanici_adi = '$kullanici' LIMIT 1");
        $kisi  = mysqli_fetch_assoc($sorgu);

        if ($kisi && $kisi['sifre'] === $sifre) {
            $_SESSION['hmts_logged_in'] = true;
            $_SESSION['hmts_user_id']   = $kisi['personel_id'];
            $_SESSION['hmts_user_name'] = $kisi['ad'] . ' ' . $kisi['soyad'];
            $_SESSION['hmts_user_role'] = $kisi['gorev'];
            header("Location: frontend/admin/index.php");
            exit;
        } else {
            $hata = 'Kullanıcı adı veya şifre hatalı!';
        }
    } else {
        $hata = 'Lütfen tüm alanları doldurunuz.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMTS – Hapishane Yönetim Sistemi | Giriş</title>
    <meta name="description" content="HMTS Hapishane Yönetim ve Takip Sistemi – Güvenli personel giriş paneli.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:    #3b82f6;
            --primary-d:  #1d4ed8;
            --accent:     #06b6d4;
            --danger:     #ef4444;
            --bg-deep:    #030712;
            --bg-card:    rgba(15,23,42,0.85);
            --border:     rgba(59,130,246,0.25);
            --text:       #f1f5f9;
            --text-muted: #94a3b8;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background: var(--bg-deep);
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove {
            0%   { transform: translateY(0); }
            100% { transform: translateY(40px); }
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }
        .orb-1 { width:400px;height:400px;background:#1d4ed8;top:-120px;left:-120px;animation-delay:0s; }
        .orb-2 { width:300px;height:300px;background:#0e7490;bottom:-80px;right:-80px;animation-delay:3s; }
        .orb-3 { width:200px;height:200px;background:#7c3aed;top:50%;left:50%;animation-delay:1.5s; }
        @keyframes orbFloat {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(30px,20px) scale(1.1); }
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 40px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.6), 0 0 0 1px rgba(59,130,246,0.1);
            animation: cardIn 0.6s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(30px) scale(0.95); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #1d4ed8, #0e7490);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
            box-shadow: 0 0 30px rgba(59,130,246,0.4);
        }
        .logo-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
        }
        .logo-sub {
            font-size: 12px;
            color: var(--accent);
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .form-input {
            width: 100%;
            padding: 13px 16px;
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 10px;
            color: var(--text);
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: all 0.25s;
        }
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
            background: rgba(15,23,42,0.8);
        }
        .form-input::placeholder { color: rgba(148,163,184,0.5); }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1d4ed8, #0e7490);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.25s;
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(29,78,216,0.5);
        }
        .btn-login:active { transform: translateY(0); }

        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .demo-accounts {
            margin-top: 20px;
            padding: 14px;
            background: rgba(59,130,246,0.05);
            border: 1px solid rgba(59,130,246,0.15);
            border-radius: 10px;
        }
        .demo-title { font-size: 11px; color: var(--text-muted); font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
        .demo-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); padding: 3px 0; cursor: pointer; transition: color 0.15s; }
        .demo-row:hover { color: var(--primary); }
        .demo-label { font-weight: 600; }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: rgba(148,163,184,0.5);
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-card">
        <div class="logo-wrap">
            <div class="logo-icon">🏛️</div>
            <div class="logo-title">HMTS</div>
            <div class="logo-sub">Hapishane Takip Sistemi</div>
        </div>

        <?php if ($hata): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="kullanici_adi">KULLANICI ADI</label>
                <input class="form-input" type="text" id="kullanici_adi" name="kullanici_adi"
                       placeholder="Kullanıcı adınızı girin" required autocomplete="off"
                       value="<?= htmlspecialchars($_POST['kullanici_adi'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="sifre">ŞİFRE</label>
                <input class="form-input" type="password" id="sifre" name="sifre"
                       placeholder="Şifrenizi girin" required>
            </div>
            <button type="submit" name="giris" class="btn-login">🔐 Sisteme Giriş Yap</button>
        </form>

        <div class="demo-accounts">
            <div class="demo-title">📋 Demo Hesaplar</div>
            <div class="demo-row" onclick="fillLogin('admin','admin123')">
                <span class="demo-label">👑 Müdür</span>
                <span>admin / admin123</span>
            </div>
            <div class="demo-row" onclick="fillLogin('doktor','doktor123')">
                <span class="demo-label">🏥 Doktor</span>
                <span>doktor / doktor123</span>
            </div>
            <div class="demo-row" onclick="fillLogin('gardiyan','gardiyan123')">
                <span class="demo-label">🛡️ Gardiyan</span>
                <span>gardiyan / gardiyan123</span>
            </div>
        </div>

        <div class="login-footer">
            © 2026 HMTS – Tüm hakları saklıdır
        </div>
    </div>

    <script>
    function fillLogin(user, pass) {
        document.getElementById('kullanici_adi').value = user;
        document.getElementById('sifre').value = pass;
    }
    </script>
</body>
</html>
