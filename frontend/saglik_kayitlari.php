<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

$user_role = $_SESSION['hmts_user_role'] ?? '';
// Doktor ve Müdür erişebilir (layout zaten kontrol ediyor)
// Doktor: tam CRUD, Müdür: tam CRUD
$can_edit = in_array($user_role, ['Doktor', 'Müdür']);

// ===== SAĞLIK KAYITLARI =====
// Doktor kendi adını otomatik doldurabilir
$logged_user_name = $_SESSION['hmts_user_name'] ?? '';

if ($can_edit && isset($_GET['kaydet'])) {
    $mahkum_id      = intval($_GET['mahkum_id'] ?? 0);
    $muayene_tarihi = mysqli_real_escape_string($baglanti, $_GET['muayene_tarihi'] ?? '');
    $doktor_adi     = mysqli_real_escape_string($baglanti, $_GET['doktor_adi'] ?? '');
    $teshis         = mysqli_real_escape_string($baglanti, $_GET['teshis'] ?? '');
    mysqli_query($baglanti, "INSERT INTO saglik_kayitlari (mahkum_id, muayene_tarihi, doktor_adi, teshis)
        VALUES ('$mahkum_id','$muayene_tarihi','$doktor_adi','$teshis')");
    header("Location: saglik_kayitlari.php"); exit;
}

if ($can_edit && isset($_GET['guncelle'])) {
    $id             = intval($_GET['kayit_id'] ?? 0);
    $mahkum_id      = intval($_GET['mahkum_id'] ?? 0);
    $muayene_tarihi = mysqli_real_escape_string($baglanti, $_GET['muayene_tarihi'] ?? '');
    $doktor_adi     = mysqli_real_escape_string($baglanti, $_GET['doktor_adi'] ?? '');
    $teshis         = mysqli_real_escape_string($baglanti, $_GET['teshis'] ?? '');
    mysqli_query($baglanti, "UPDATE saglik_kayitlari SET mahkum_id='$mahkum_id', muayene_tarihi='$muayene_tarihi',
        doktor_adi='$doktor_adi', teshis='$teshis' WHERE kayit_id=$id");
    header("Location: saglik_kayitlari.php"); exit;
}

if ($can_edit && isset($_GET['sil'])) {
    $id = intval($_GET['kayit_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM saglik_kayitlari WHERE kayit_id=$id");
    header("Location: saglik_kayitlari.php"); exit;
}

$kayitlar = mysqli_query($baglanti, "
    SELECT sk.*, m.ad AS m_ad, m.soyad AS m_soyad
    FROM saglik_kayitlari sk
    LEFT JOIN mahkumlar m ON sk.mahkum_id = m.mahkum_id
    ORDER BY sk.kayit_id DESC
");

render_layout_start('Sağlık Kayıtları');
?>

<!-- EKLEME FORMU -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">➕ Yeni Sağlık Kaydı Ekle</span>
        <?php if ($user_role === 'Doktor'): ?>
        <span class="badge badge-green">🏥 Doktor Girişi</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form action="saglik_kayitlari.php" method="GET">
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
                    <label class="form-label">Muayene Tarihi</label>
                    <input class="form-control" type="date" name="muayene_tarihi" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Doktor Adı</label>
                    <input class="form-control" type="text" name="doktor_adi" placeholder="Dr. Ad Soyad"
                        value="<?= $user_role === 'Doktor' ? htmlspecialchars('Dr. ' . $logged_user_name) : '' ?>">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Teşhis & Tedavi Notları</label>
                    <textarea class="form-control" name="teshis" rows="3" placeholder="Muayene sonucu, teşhis ve tedavi bilgileri..."></textarea>
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Sağlık Kaydı Ekle</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏥 Sağlık Kayıtları</span>
        <span class="badge badge-green"><?= mysqli_num_rows($kayitlar) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Mahkum</th><th>Muayene Tarihi</th><th>Doktor</th><th>Teşhis</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($kayitlar) === 0): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏥</div><p>Henüz sağlık kaydı yok.</p></div></td></tr>
            <?php else: while ($r = mysqli_fetch_assoc($kayitlar)): ?>
                <tr>
                    <td><span class="badge badge-green">#<?= $r['kayit_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($r['m_ad'] . ' ' . $r['m_soyad']) ?></strong></td>
                    <td><?= htmlspecialchars($r['muayene_tarihi'] ?? '–') ?></td>
                    <td><span class="badge badge-teal"><?= htmlspecialchars($r['doktor_adi'] ?? '–') ?></span></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['teshis'] ?? '') ?>">
                        <?= htmlspecialchars($r['teshis'] ?? '–') ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($r)) ?>)" class="btn btn-warning btn-sm">✏️ Düzenle</button>
                        <a href="saglik_kayitlari.php?sil=1&kayit_id=<?= $r['kayit_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sağlık kaydını silmek istiyor musunuz?')">🗑️ Sil</a>
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
        <div class="modal-title">✏️ Sağlık Kaydı Düzenle</div>
        <form action="saglik_kayitlari.php" method="GET">
            <input type="hidden" name="kayit_id" id="edit_id">
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
                    <label class="form-label">Muayene Tarihi</label>
                    <input class="form-control" type="date" name="muayene_tarihi" id="edit_tarih">
                </div>
                <div class="form-group">
                    <label class="form-label">Doktor Adı</label>
                    <input class="form-control" type="text" name="doktor_adi" id="edit_doktor">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Teşhis</label>
                    <textarea class="form-control" name="teshis" id="edit_teshis" rows="3"></textarea>
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
    document.getElementById('edit_id').value     = r.kayit_id;
    document.getElementById('edit_mahkum').value = r.mahkum_id;
    document.getElementById('edit_tarih').value  = r.muayene_tarihi || '';
    document.getElementById('edit_doktor').value = r.doktor_adi || '';
    document.getElementById('edit_teshis').value = r.teshis || '';
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
