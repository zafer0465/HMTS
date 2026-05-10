<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

// ===== İŞ TÜRLERİ – SADECE MÜDÜR =====
if ($_SESSION['hmts_user_role'] !== 'Müdür') { header("Location: mahkumlar.php"); exit; }
// ERD: is_turu_id | is_adi (varchar) | aciklama (text)

if (isset($_GET['kaydet'])) {
    $isim = mysqli_real_escape_string($baglanti, $_GET['is_adi'] ?? '');
    $acik = mysqli_real_escape_string($baglanti, $_GET['aciklama'] ?? '');
    mysqli_query($baglanti, "INSERT INTO is_turleri (is_adi, aciklama) VALUES ('$isim','$acik')");
    header("Location: is_turleri.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id   = intval($_GET['is_turu_id'] ?? 0);
    $isim = mysqli_real_escape_string($baglanti, $_GET['is_adi'] ?? '');
    $acik = mysqli_real_escape_string($baglanti, $_GET['aciklama'] ?? '');
    mysqli_query($baglanti, "UPDATE is_turleri SET is_adi='$isim', aciklama='$acik' WHERE is_turu_id=$id");
    header("Location: is_turleri.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['is_turu_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM is_turleri WHERE is_turu_id=$id");
    header("Location: is_turleri.php"); exit;
}

$is_turleri = mysqli_query($baglanti, "
    SELECT it.*, COUNT(mg.gorev_id) as gorev_sayisi
    FROM is_turleri it
    LEFT JOIN mahkum_gorevleri mg ON it.is_turu_id = mg.is_turu_id
    GROUP BY it.is_turu_id
    ORDER BY it.is_turu_id DESC
");

render_layout_start('İş Türleri Yönetimi');
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni İş Türü Ekle</span></div>
    <div class="card-body">
        <form action="is_turleri.php" method="GET">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">İş Adı</label>
                    <input class="form-control" type="text" name="is_adi" placeholder="Örn: Mutfak Görevlisi" required>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Açıklama</label>
                    <textarea class="form-control" name="aciklama" placeholder="İş türü hakkında kısa açıklama" rows="2"></textarea>
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Kaydet</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">⚙️ İş Türleri Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($is_turleri) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>İş Adı</th><th>Açıklama</th><th>Atanan Görev</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($is_turleri) === 0): ?>
                <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">⚙️</div><p>Henüz iş türü kaydı yok.</p></div></td></tr>
            <?php else: while ($i = mysqli_fetch_assoc($is_turleri)): ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $i['is_turu_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($i['is_adi']) ?></strong></td>
                    <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($i['aciklama'] ?? '') ?>">
                        <?= htmlspecialchars($i['aciklama'] ?? '–') ?>
                    </td>
                    <td><span class="badge badge-teal"><?= $i['gorev_sayisi'] ?> görev</span></td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($i)) ?>)" class="btn btn-warning btn-sm">✏️</button>
                        <a href="is_turleri.php?sil=1&is_turu_id=<?= $i['is_turu_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('İş türünü silmek istiyor musunuz?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ İş Türü Düzenle</div>
        <form action="is_turleri.php" method="GET">
            <input type="hidden" name="is_turu_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">İş Adı</label>
                    <input class="form-control" type="text" name="is_adi" id="edit_adi" required>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Açıklama</label>
                    <textarea class="form-control" name="aciklama" id="edit_acik" rows="2"></textarea>
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
function doldurDuzenle(i) {
    document.getElementById('edit_id').value   = i.is_turu_id;
    document.getElementById('edit_adi').value  = i.is_adi || '';
    document.getElementById('edit_acik').value = i.aciklama || '';
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
