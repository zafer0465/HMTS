<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

// İstatistikler
$r1 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM mahkumlar"));        $c_mahkum    = $r1[0];
$r2 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM koguslar"));         $c_kogus     = $r2[0];
$r3 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM personel"));         $c_personel  = $r3[0];
$r4 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM saglik_kayitlari")); $c_saglik    = $r4[0];
$r5 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM ziyaretciler"));     $c_ziyaretci = $r5[0];
$r6 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM disiplin_arsivi"));  $c_disiplin  = $r6[0];
$r7 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM mahkum_gorevleri")); $c_gorev    = $r7[0];
$r8 = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM ziyaret_kayitlari")); $c_ziyaret  = $r8[0];

// Aktif görevler
$r_aktif = mysqli_fetch_row(mysqli_query($baglanti, "SELECT COUNT(*) FROM mahkum_gorevleri WHERE durum='Aktif'")); $c_aktif = $r_aktif[0];

// Son mahkumlar
$son_mahkumlar = mysqli_query($baglanti, "SELECT m.ad, m.soyad, m.suc_turu, m.kayit_tarihi, k.blok_adi
    FROM mahkumlar m LEFT JOIN koguslar k ON m.kogus_id = k.kogus_id
    ORDER BY m.mahkum_id DESC LIMIT 5");

// Son disiplin kayıtları
$son_disiplin = mysqli_query($baglanti, "SELECT d.olay_tarihi, m.ad, m.soyad, d.olay_detayi
    FROM disiplin_arsivi d LEFT JOIN mahkumlar m ON d.mahkum_id = m.mahkum_id
    ORDER BY d.olay_id DESC LIMIT 5");

// Koğuş doluluk durumu
$kogus_doluluk = mysqli_query($baglanti, "SELECT k.blok_adi, k.kapasite, COUNT(m.mahkum_id) as dolu
    FROM koguslar k LEFT JOIN mahkumlar m ON k.kogus_id = m.kogus_id
    GROUP BY k.kogus_id ORDER BY k.blok_adi");

render_layout_start('Dashboard – Genel Bakış');
?>

<!-- İSTATİSTİK KARTLARI -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🔒</div>
        <div class="stat-val"><?= $c_mahkum ?></div>
        <div class="stat-label">Toplam Mahkum</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-val"><?= $c_kogus ?></div>
        <div class="stat-label">Koğuş</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-val"><?= $c_personel ?></div>
        <div class="stat-label">Personel</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏥</div>
        <div class="stat-val"><?= $c_saglik ?></div>
        <div class="stat-label">Sağlık Kaydı</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-val"><?= $c_ziyaretci ?></div>
        <div class="stat-label">Ziyaretçi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-val"><?= $c_ziyaret ?></div>
        <div class="stat-label">Ziyaret Kaydı</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🔧</div>
        <div class="stat-val"><?= $c_aktif ?></div>
        <div class="stat-label">Aktif Görev</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-val"><?= $c_disiplin ?></div>
        <div class="stat-label">Disiplin Kaydı</div>
    </div>
</div>

<!-- ALT TABLOLAR -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <!-- Son Mahkumlar -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🔒 Son Eklenen Mahkumlar</span>
            <a href="mahkumlar.php" class="btn btn-primary btn-sm">Tümünü Gör</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ad Soyad</th><th>Suç Türü</th><th>Koğuş</th><th>Kayıt</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($son_mahkumlar) === 0): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;">Kayıt yok</td></tr>
                <?php else: while ($r = mysqli_fetch_assoc($son_mahkumlar)): ?>
                    <tr>
                        <td><strong style="color:var(--text)"><?= htmlspecialchars($r['ad'] . ' ' . $r['soyad']) ?></strong></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['suc_turu'] ?? '–') ?></td>
                        <td><span class="badge badge-blue"><?= htmlspecialchars($r['blok_adi'] ?? '–') ?></span></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['kayit_tarihi'] ?? '–') ?></td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Son Disiplin Kayıtları -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Son Disiplin Olayları</span>
            <a href="disiplin_arsivi.php" class="btn btn-warning btn-sm">Tümünü Gör</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Mahkum</th><th>Tarih</th><th>Detay</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($son_disiplin) === 0): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">Kayıt yok</td></tr>
                <?php else: while ($r = mysqli_fetch_assoc($son_disiplin)): ?>
                    <tr>
                        <td><strong style="color:var(--text)"><?= htmlspecialchars($r['ad'] . ' ' . $r['soyad']) ?></strong></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['olay_tarihi'] ?? '–') ?></td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;" title="<?= htmlspecialchars($r['olay_detayi'] ?? '') ?>"><?= htmlspecialchars($r['olay_detayi'] ?? '–') ?></td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Koğuş Doluluk -->
<div class="card">
    <div class="card-header">
        <span class="card-title">🏢 Koğuş Doluluk Durumu</span>
        <a href="koguslar.php" class="btn btn-primary btn-sm">Yönet</a>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
        <?php while ($k = mysqli_fetch_assoc($kogus_doluluk)):
            $oran = $k['kapasite'] > 0 ? round(($k['dolu'] / $k['kapasite']) * 100) : 0;
            $renk = $oran >= 90 ? '#ef4444' : ($oran >= 70 ? '#f59e0b' : '#22c55e');
        ?>
        <div style="background:var(--bg-card2);border:1px solid var(--border);border-radius:10px;padding:16px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-weight:600;color:var(--text);font-size:14px;"><?= htmlspecialchars($k['blok_adi']) ?></span>
                <span style="font-size:12px;color:<?= $renk ?>;font-weight:700;"><?= $k['dolu'] ?>/<?= $k['kapasite'] ?></span>
            </div>
            <div style="height:6px;background:rgba(255,255,255,0.08);border-radius:10px;overflow:hidden;">
                <div style="width:<?= $oran ?>%;height:100%;background:<?= $renk ?>;border-radius:10px;transition:width 0.5s;"></div>
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">%<?= $oran ?> dolu</div>
        </div>
        <?php endwhile; ?>
        </div>
    </div>
</div>

<?php render_layout_end(); ?>
