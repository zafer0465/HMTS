<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

$user_role = $_SESSION['hmts_user_role'] ?? '';
// Gardiyan ve Müdür erişebilir (layout zaten kontrol ediyor)

// ===== ZİYARET KAYITLARI =====
// Gardiyan: tam CRUD, Müdür: tam CRUD

if (isset($_GET['kaydet'])) {
    $mahkum_id    = intval($_GET['mahkum_id'] ?? 0);
    $ziyaretci_id = intval($_GET['ziyaretci_id'] ?? 0);
    $tarih_saat   = mysqli_real_escape_string($baglanti, $_GET['tarih_saat'] ?? '');
    mysqli_query($baglanti, "INSERT INTO ziyaret_kayitlari (mahkum_id, ziyaretci_id, tarih_saat)
        VALUES ('$mahkum_id','$ziyaretci_id','$tarih_saat')");
    header("Location: ziyaret_kayitlari.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id           = intval($_GET['ziyaret_id'] ?? 0);
    $mahkum_id    = intval($_GET['mahkum_id'] ?? 0);
    $ziyaretci_id = intval($_GET['ziyaretci_id'] ?? 0);
    $tarih_saat   = mysqli_real_escape_string($baglanti, $_GET['tarih_saat'] ?? '');
    mysqli_query($baglanti, "UPDATE ziyaret_kayitlari SET mahkum_id='$mahkum_id', ziyaretci_id='$ziyaretci_id',
        tarih_saat='$tarih_saat' WHERE ziyaret_id=$id");
    header("Location: ziyaret_kayitlari.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['ziyaret_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM ziyaret_kayitlari WHERE ziyaret_id=$id");
    header("Location: ziyaret_kayitlari.php"); exit;
}

$kayitlar = mysqli_query($baglanti, "
    SELECT zk.*,
           m.ad AS m_ad, m.soyad AS m_soyad,
           z.ad AS z_ad, z.soyad AS z_soyad, z.tc_no AS z_tc, z.telefon AS z_tel
    FROM ziyaret_kayitlari zk
    LEFT JOIN mahkumlar m ON zk.mahkum_id = m.mahkum_id
    LEFT JOIN ziyaretciler z ON zk.ziyaretci_id = z.ziyaretci_id
    ORDER BY zk.ziyaret_id DESC
");

render_layout_start('Ziyaret Kayıtları');
?>

<!-- EKLEME FORMU -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">➕ Yeni Ziyaret Kaydı Ekle</span>
        <?php if ($user_role === 'Gardiyan'): ?>
        <span class="badge badge-yellow">🛡️ Gardiyan Girişi</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form action="ziyaret_kayitlari.php" method="GET">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Mahkum</label>
                    <select class="form-control" name="mahkum_id" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $mt = mysqli_query($baglanti, "SELECT mahkum_id, ad, soyad FROM mahkumlar ORDER BY ad");
                        while ($m = mysqli_fetch_assoc($mt)):
                        ?>
                        <option value="<?= $m['mahkum_id'] ?>"><?= htmlspecialchars($m['ad'] . ' ' . $m['soyad']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ziyaretçi</label>
                    <select class="form-control" name="ziyaretci_id" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $zt = mysqli_query($baglanti, "SELECT ziyaretci_id, tc_no, ad, soyad FROM ziyaretciler ORDER BY ad");
                        while ($z = mysqli_fetch_assoc($zt)):
                        ?>
                        <option value="<?= $z['ziyaretci_id'] ?>"><?= htmlspecialchars($z['ad'] . ' ' . $z['soyad'] . ' (TC: ' . ($z['tc_no'] ?: '–') . ')') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ziyaret Tarihi / Saati</label>
                    <input class="form-control" type="datetime-local" name="tarih_saat" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
            </div>
            <div style="margin-top:16px; display:flex; gap:10px; align-items:center;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Ziyaret Kaydı Ekle</button>
                <a href="ziyaretciler.php" class="btn btn-primary">👥 Ziyaretçi Yönetimi</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Ziyaret Kayıtları</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($kayitlar) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Mahkum</th><th>Ziyaretçi</th><th>Ziyaretçi TC</th><th>Ziyaretçi Tel</th><th>Tarih / Saat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($kayitlar) === 0): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📋</div><p>Henüz ziyaret kaydı yok.</p></div></td></tr>
            <?php else: while ($r = mysqli_fetch_assoc($kayitlar)): ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $r['ziyaret_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($r['m_ad'] . ' ' . $r['m_soyad']) ?></strong></td>
                    <td><?= htmlspecialchars($r['z_ad'] . ' ' . $r['z_soyad']) ?></td>
                    <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($r['z_tc'] ?? '–') ?></td>
                    <td><?= htmlspecialchars($r['z_tel'] ?? '–') ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($r['tarih_saat'] ?? '–') ?></td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($r)) ?>)" class="btn btn-warning btn-sm">✏️ Düzenle</button>
                        <a href="ziyaret_kayitlari.php?sil=1&ziyaret_id=<?= $r['ziyaret_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Ziyaret kaydını silmek istiyor musunuz?')">🗑️ Sil</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DÜZENLEME MODALI -->
<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ Ziyaret Kaydı Düzenle</div>
        <form action="ziyaret_kayitlari.php" method="GET">
            <input type="hidden" name="ziyaret_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Mahkum</label>
                    <select class="form-control" name="mahkum_id" id="edit_mahkum" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $mt2 = mysqli_query($baglanti, "SELECT mahkum_id, ad, soyad FROM mahkumlar ORDER BY ad");
                        while ($m = mysqli_fetch_assoc($mt2)):
                        ?>
                        <option value="<?= $m['mahkum_id'] ?>"><?= htmlspecialchars($m['ad'] . ' ' . $m['soyad']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ziyaretçi</label>
                    <select class="form-control" name="ziyaretci_id" id="edit_ziyaretci" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $zt2 = mysqli_query($baglanti, "SELECT ziyaretci_id, tc_no, ad, soyad FROM ziyaretciler ORDER BY ad");
                        while ($z = mysqli_fetch_assoc($zt2)):
                        ?>
                        <option value="<?= $z['ziyaretci_id'] ?>"><?= htmlspecialchars($z['ad'] . ' ' . $z['soyad'] . ' (TC: ' . ($z['tc_no'] ?: '–') . ')') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tarih / Saat</label>
                    <input class="form-control" type="datetime-local" name="tarih_saat" id="edit_tarih">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('duzenleModal')" class="btn btn-danger">İptal</button>
                <button type="submit" name="guncelle" value="1" class="btn btn-success">💾 Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
function doldurDuzenle(r) {
    document.getElementById('edit_id').value        = r.ziyaret_id;
    document.getElementById('edit_mahkum').value    = r.mahkum_id;
    document.getElementById('edit_ziyaretci').value = r.ziyaretci_id;
    var dt = r.tarih_saat ? r.tarih_saat.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('edit_tarih').value     = dt;
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
