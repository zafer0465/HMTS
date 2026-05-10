<?php
// Bu dosya doğrudan erişilemez
if (!defined('HMTS_INCLUDED')) {
    header("Location: ../../index.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_name = $_SESSION['hmts_user_name'] ?? 'Kullanıcı';
$user_role = $_SESSION['hmts_user_role'] ?? 'Personel';

// ================================================================
// ROL BAZLI ERİŞİM KURALLARI
// ================================================================
// Müdür    : Tüm sayfalar - tam CRUD (personel, koğuş, mahkum, tümü)
// Doktor   : SADECE sağlık kayıtları - tam CRUD
// Gardiyan : Ziyaretçi + Ziyaret Kayıtları - tam CRUD,
//            Mahkumlar + Koğuşlar - SADECE görüntüleme (okuma)
// ================================================================

$izinli_roller = ['Müdür', 'Gardiyan', 'Doktor', 'Psikolog', 'Memur'];

// Bilinmeyen rol → oturumu sonlandır
if (!in_array($user_role, $izinli_roller)) {
    session_destroy();
    header("Location: ../../index.php"); exit;
}

// Navigasyon menüsü - rol bazlı görünürlük
$nav_items = [
    ['file' => 'index.php',             'icon' => '📊', 'label' => 'Dashboard',         'roles' => ['Müdür']],
    ['file' => 'mahkumlar.php',         'icon' => '🔒', 'label' => 'Mahkumlar',          'roles' => ['Müdür', 'Gardiyan']],
    ['file' => 'koguslar.php',          'icon' => '🏢', 'label' => 'Koğuşlar',           'roles' => ['Müdür', 'Gardiyan']],
    ['file' => 'personel.php',          'icon' => '👤', 'label' => 'Personel',           'roles' => ['Müdür']],
    ['file' => 'saglik_kayitlari.php',  'icon' => '🏥', 'label' => 'Sağlık Kayıtları',  'roles' => ['Müdür', 'Doktor']],
    ['file' => 'ziyaretciler.php',      'icon' => '👥', 'label' => 'Ziyaretçiler',      'roles' => ['Müdür', 'Gardiyan']],
    ['file' => 'ziyaret_kayitlari.php', 'icon' => '📋', 'label' => 'Ziyaret Kayıtları', 'roles' => ['Müdür', 'Gardiyan']],
    ['file' => 'is_turleri.php',        'icon' => '⚙️', 'label' => 'İş Türleri',        'roles' => ['Müdür']],
    ['file' => 'mahkum_gorevleri.php',  'icon' => '🔧', 'label' => 'Mahkum Görevleri',  'roles' => ['Müdür', 'Gardiyan']],
    ['file' => 'disiplin_arsivi.php',   'icon' => '⚠️', 'label' => 'Disiplin Arşivi',   'roles' => ['Müdür', 'Gardiyan']],
];

// Rol → yönlendirme hedefi
function rol_ana_sayfa($rol) {
    return match($rol) {
        'Doktor'   => 'saglik_kayitlari.php',
        'Gardiyan' => 'ziyaret_kayitlari.php',
        default    => 'mahkumlar.php',
    };
}

// Dashboard sadece Müdür
if ($user_role !== 'Müdür' && $current_page === 'index.php') {
    header("Location: " . rol_ana_sayfa($user_role)); exit;
}

// Sayfa izin tablosu
$sayfa_izinleri = [];
foreach ($nav_items as $item) {
    $sayfa_izinleri[$item['file']] = $item['roles'];
}
$sayfa_izinleri['logout.php'] = $izinli_roller;

// Erişim kontrolü
if ($current_page !== 'logout.php' && isset($sayfa_izinleri[$current_page])) {
    if (!in_array($user_role, $sayfa_izinleri[$current_page])) {
        header("Location: " . rol_ana_sayfa($user_role)); exit;
    }
}

function render_layout_start($page_title) {
    global $current_page, $user_name, $user_role, $nav_items;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> – HMTS</title>
    <meta name="description" content="HMTS – Hapishane Yönetim Sistemi <?= htmlspecialchars($page_title) ?> modülü.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w:    240px;
            --primary:      #3b82f6;
            --primary-d:    #1d4ed8;
            --accent:       #06b6d4;
            --success:      #22c55e;
            --warning:      #f59e0b;
            --danger:       #ef4444;
            --bg-deep:      #030712;
            --bg-sidebar:   #070d1a;
            --bg-card:      #0d1629;
            --bg-card2:     #111d35;
            --border:       rgba(59,130,246,0.15);
            --border-h:     rgba(59,130,246,0.4);
            --text:         #f1f5f9;
            --text-muted:   #64748b;
            --text-sub:     #94a3b8;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-deep);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
        }
        .brand-logo {
            display: flex; align-items: center; gap: 12px;
        }
        .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #1d4ed8, #0e7490);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .brand-text { font-size: 16px; font-weight: 800; color: var(--text); }
        .brand-sub  { font-size: 10px; color: var(--accent); letter-spacing: 1.5px; text-transform: uppercase; }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
        }
        .user-name  { font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role  {
            font-size: 10px; font-weight: 600;
            padding: 2px 7px; border-radius: 20px;
            display: inline-block; margin-top: 2px;
        }
        .role-mudur    { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .role-doktor   { background: rgba(34,197,94,0.2);  color: #86efac; }
        .role-gardiyan { background: rgba(245,158,11,0.2); color: #fcd34d; }
        .role-psikolog { background: rgba(168,85,247,0.2); color: #d8b4fe; }
        .role-memur    { background: rgba(20,184,166,0.2); color: #5eead4; }

        .sidebar-nav { flex: 1; padding: 12px 0; }
        .nav-section-label {
            font-size: 10px; font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 8px 20px 4px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px;
            color: var(--text-sub);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            position: relative;
        }
        .nav-link:hover {
            color: var(--text);
            background: rgba(59,130,246,0.07);
        }
        .nav-link.active {
            color: var(--primary);
            background: rgba(59,130,246,0.12);
            border-left-color: var(--primary);
            font-weight: 600;
        }
        .nav-icon { font-size: 16px; width: 22px; text-align: center; }
        .nav-logout {
            border-top: 1px solid var(--border);
            padding: 12px 0;
        }
        .nav-logout .nav-link { color: #f87171; }
        .nav-logout .nav-link:hover { background: rgba(239,68,68,0.07); color: #fca5a5; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            padding: 0 32px;
            height: 64px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(7,13,26,0.8);
            backdrop-filter: blur(10px);
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 20px; font-weight: 700; color: var(--text); }
        .topbar-right  { display: flex; align-items: center; gap: 12px; }
        .topbar-time   { font-size: 12px; color: var(--text-muted); }

        .page-body { padding: 28px 32px; flex: 1; }

        /* ===== CARDS ===== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); }
        .card-body  { padding: 22px; }

        /* ===== STAT CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px,1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            transition: all 0.2s;
            cursor: default;
        }
        .stat-card:hover {
            border-color: var(--border-h);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .stat-icon  { font-size: 28px; margin-bottom: 12px; }
        .stat-val   { font-size: 32px; font-weight: 800; color: var(--text); line-height: 1; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ===== FORMS ===== */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 12px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px; text-transform: uppercase; }
        .form-control {
            padding: 10px 13px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            background: rgba(255,255,255,0.07);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .form-control option { background: #0d1629; color: var(--text); }
        textarea.form-control { resize: vertical; min-height: 80px; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            border: none; border-radius: 8px;
            font-size: 13.5px; font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: linear-gradient(135deg,#1d4ed8,#0e7490); color:#fff; }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(29,78,216,0.4); }
        .btn-success { background: linear-gradient(135deg,#15803d,#0e9488); color:#fff; }
        .btn-success:hover { box-shadow: 0 6px 20px rgba(21,128,61,0.4); }
        .btn-danger  { background: rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover  { background: rgba(239,68,68,0.25); box-shadow:0 4px 12px rgba(239,68,68,0.2); }
        .btn-warning { background: rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
        .btn-warning:hover { background: rgba(245,158,11,0.25); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* ===== TABLE ===== */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: rgba(59,130,246,0.08); }
        th {
            padding: 12px 14px;
            font-size: 11px; font-weight: 700;
            color: var(--text-muted);
            text-align: left;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding: 13px 14px;
            font-size: 13.5px;
            color: var(--text-sub);
            border-bottom: 1px solid rgba(59,130,246,0.06);
        }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: rgba(59,130,246,0.04); }
        tbody tr:last-child td { border-bottom: none; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .badge-blue   { background:rgba(59,130,246,0.2);  color:#93c5fd; }
        .badge-green  { background:rgba(34,197,94,0.2);   color:#86efac; }
        .badge-red    { background:rgba(239,68,68,0.2);   color:#fca5a5; }
        .badge-yellow { background:rgba(245,158,11,0.2);  color:#fcd34d; }
        .badge-purple { background:rgba(168,85,247,0.2);  color:#d8b4fe; }
        .badge-teal   { background:rgba(20,184,166,0.2);  color:#5eead4; }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px; border-radius: 8px;
            font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-success { background:rgba(34,197,94,0.1);  border:1px solid rgba(34,197,94,0.3);  color:#86efac; }
        .alert-danger  { background:rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.3);  color:#fca5a5; }
        .alert-info    { background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.3); color:#93c5fd; }
        .alert-warning { background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); color:#fcd34d; }

        /* ===== MODAL ===== */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.7); z-index:1000;
            align-items:center; justify-content:center;
            backdrop-filter:blur(4px);
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:var(--bg-card2);
            border:1px solid var(--border-h);
            border-radius:16px;
            padding:28px 32px;
            width:640px; max-width:95vw;
            max-height:90vh; overflow-y:auto;
            animation: modalIn 0.25s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes modalIn {
            from { opacity:0; transform:scale(0.93) translateY(10px); }
            to   { opacity:1; transform:scale(1) translateY(0); }
        }
        .modal-title { font-size:17px; font-weight:700; margin-bottom:20px; color:var(--text); }
        .modal-footer { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }

        /* ===== EMPTY STATE ===== */
        .empty-state { text-align:center; padding:48px; color:var(--text-muted); }
        .empty-state .empty-icon { font-size:48px; margin-bottom:12px; }
        .empty-state p { font-size:14px; }

        /* ===== READ ONLY NOTICE ===== */
        .readonly-notice {
            display:flex; align-items:center; gap:10px;
            padding:10px 16px; border-radius:8px; margin-bottom:20px;
            background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.25);
            color:#fcd34d; font-size:13px; font-weight:500;
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:rgba(59,130,246,0.3); border-radius:10px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon">🏛️</div>
            <div>
                <div class="brand-text">HMTS</div>
                <div class="brand-sub">Hapishane Takip</div>
            </div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(mb_substr($user_name, 0, 1)) ?></div>
        <div>
            <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
            <?php
            $role_slug = mb_strtolower(str_replace(['ü','ö','ı','ş','ğ','ç'], ['u','o','i','s','g','c'], $user_role));
            ?>
            <span class="user-role role-<?= $role_slug ?>"><?= htmlspecialchars($user_role) ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menü</div>
        <?php foreach ($nav_items as $item):
            if (!in_array($user_role, $item['roles'])) continue;
            $is_active = ($current_page === $item['file']);
        ?>
        <a href="<?= $item['file'] ?>" class="nav-link <?= $is_active ? 'active' : '' ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="nav-logout">
        <a href="logout.php" class="nav-link">
            <span class="nav-icon">🚪</span>
            Çıkış Yap
        </a>
    </div>
</aside>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-title"><?= htmlspecialchars($page_title) ?></div>
        <div class="topbar-right">
            <span class="topbar-time" id="clock"></span>
            <?php
            $rol_badge_config = [
                'Müdür'    => ['label'=>'Müdür',    'bg'=>'rgba(59,130,246,0.15)',  'color'=>'#93c5fd',  'icon'=>'&#128081;'],
                'Gardiyan' => ['label'=>'Gardiyan', 'bg'=>'rgba(245,158,11,0.15)', 'color'=>'#fcd34d',  'icon'=>'&#128737;'],
                'Doktor'   => ['label'=>'Doktor',   'bg'=>'rgba(34,197,94,0.15)',  'color'=>'#86efac',  'icon'=>'&#127973;'],
                'Psikolog' => ['label'=>'Psikolog', 'bg'=>'rgba(168,85,247,0.15)', 'color'=>'#d8b4fe',  'icon'=>'&#129504;'],
                'Memur'    => ['label'=>'Memur',    'bg'=>'rgba(20,184,166,0.15)', 'color'=>'#5eead4',  'icon'=>'&#128203;'],
            ];
            $rb = $rol_badge_config[$user_role] ?? ['label'=>$user_role,'bg'=>'rgba(100,116,139,0.15)','color'=>'#94a3b8','icon'=>'&#128100;'];
            ?>
            <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;background:<?= $rb['bg'] ?>;color:<?= $rb['color'] ?>;border:1px solid <?= $rb['color'] ?>33;">
                <?= $rb['icon'] ?> <?= htmlspecialchars($rb['label']) ?>
            </span>
            <a href="logout.php" style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.2);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">🚪 Çıkış</a>
        </div>
    </div>
    <div class="page-body">
<?php
}

function render_layout_end() {
?>
    </div><!-- /.page-body -->
</div><!-- /.main-content -->

<script>
// Clock
function updateClock() {
    const now = new Date();
    const el = document.getElementById('clock');
    if(el) el.textContent =
        now.toLocaleDateString('tr-TR') + ' ' +
        now.toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
}
updateClock();
setInterval(updateClock, 1000);

// Modal helpers
function openModal(id)  { const el=document.getElementById(id); if(el) el.classList.add('open'); }
function closeModal(id) { const el=document.getElementById(id); if(el) el.classList.remove('open'); }

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    });
});

// ESC tuşu ile modal kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
});
</script>
</body>
</html>
<?php
}
