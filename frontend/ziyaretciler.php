<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

$user_role = $_SESSION['hmts_user_role'] ?? '';
// Gardiyan ve Müdür erişebilir (layout zaten kontrol ediyor)

// ===== ZİYARETÇİLER =====

if (isset($_GET['kaydet'])) {
    $tc      = mysqli_real_escape_string($baglanti, $_GET['tc_no'] ?? '');
    $ad      = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad   = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $telefon = mysqli_real_escape_string($baglanti, $_GET['telefon'] ?? '');
    mysqli_query($baglanti, "INSERT INTO ziyaretciler (tc_no, ad, soyad, telefon) VALUES ('$tc','$ad','$soyad','$telefon')");
    header("Location: ziyaretciler.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id      = intval($_GET['ziyaretci_id'] ?? 0);
    $tc      = mysqli_real_escape_string($baglanti, $_GET['tc_no'] ?? '');
    $ad      = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad   = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $telefon = mysqli_real_escape_string($baglanti, $_GET['telefon'] ?? '');
    mysqli_query($baglanti, "UPDATE ziyaretciler SET tc_no='$tc', ad='$ad', soyad='$soyad', telefon='$telefon' WHERE ziyaretci_id=$id");
    header("Location: ziyaretciler.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['ziyaretci_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM ziyaretciler WHERE ziyaretci_id=$id");
    header("Location: ziyaretciler.php"); exit;
}

$ziyaretciler = mysqli_query($baglanti, "
    SELECT z.*, COUNT(zk.ziyaret_id) AS ziyaret_sayisi
    FROM ziyaretciler z
    LEFT JOIN ziyaret_kayitlari zk ON z.ziyaretci_id = zk.ziyaretci_id
    GROUP BY z.ziyaretci_id
    ORDER BY z.ziyaretci_id DESC
");

render_layout_start('Ziyaretçi Yönetimi');
?>

<!-- EKLEME FORMU -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">➕ Yeni Ziyaretçi Kaydet</span>
        <?php if ($user_role === 'Gardiyan'): ?>
        <span class="badge badge-yellow">🛡️ Gardiyan Girişi</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form action="ziyaretciler.php" method="GET">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">TC Kimlik No</label>
                    <input class="form-control" type="text" name="tc_no" placeholder="11 haneli TC No" maxlength="11">
                </div>
                <div class="form-group">
                    <label class="form-label">Ad</label>
                    <input class="form-control" type="text" name="ad" placeholder="Ad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Soyad</label>
                    <input class="form-control" type="text" name="soyad" placeholder="Soyad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input class="form-control" type="text" name="telefon" placeholder="0555 000 00 00">
                </div>
            </div>
            <div style="margin-top:16px; display:flex; gap:10px; align-items:center;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Ziyaretçi Ekle</button>
                <a href="ziyaret_kayitlari.php" class="btn btn-primary">📋 Ziyaret Kaydı Oluştur</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">👥 Ziyaretçi Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($ziyaretciler) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>TC No</th><th>Ad Soyad</th><th>Telefon</th><th>Toplam Ziyaret</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($ziyaretciler) === 0): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👥</div><p>Henüz ziyaretçi kaydı yok.</p></div></td></tr>
            <?php else: while ($z = mysqli_fetch_assoc($ziyaretciler)): ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $z['ziyaretci_id'] ?></span></td>
                    <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($z['tc_no'] ?? '–') ?></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($z['ad'] . ' ' . $z['soyad']) ?></strong></td>
                    <td><?= htmlspecialchars($z['telefon'] ?? '–') ?></td>
                    <td>
                        <?php if ($z['ziyaret_sayisi'] > 0): ?>
                        <span class="badge badge-teal"><?= $z['ziyaret_sayisi'] ?> ziyaret</span>
                        <?php else: ?>
                        <span class="badge badge-yellow">Henüz yok</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($z)) ?>)" class="btn btn-warning btn-sm">✏️ Düzenle</button>
                        <a href="ziyaretciler.php?sil=1&ziyaretci_id=<?= $z['ziyaretci_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Ziyaretçiyi ve tüm ziyaret kayıtlarını silmek istiyor musunuz?')">🗑️ Sil</a>
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
        <div class="modal-title">✏️ Ziyaretçi Düzenle</div>
        <form action="ziyaretciler.php" method="GET">
            <input type="hidden" name="ziyaretci_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">TC No</label>
                    <input class="form-control" type="text" name="tc_no" id="edit_tc" maxlength="11">
                </div>
                <div class="form-group">
                    <label class="form-label">Ad</label>
                    <input class="form-control" type="text" name="ad" id="edit_ad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Soyad</label>
                    <input class="form-control" type="text" name="soyad" id="edit_soyad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input class="form-control" type="text" name="telefon" id="edit_tel">
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
function doldurDuzenle(z) {
    document.getElementById('edit_id').value    = z.ziyaretci_id;
    document.getElementById('edit_tc').value    = z.tc_no || '';
    document.getElementById('edit_ad').value    = z.ad || '';
    document.getElementById('edit_soyad').value = z.soyad || '';
    document.getElementById('edit_tel').value   = z.telefon || '';
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
