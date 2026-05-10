<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

// ===== DİSİPLİN ARŞİVİ – MÜDÜR + GARDİYAN =====
if (!in_array($_SESSION['hmts_user_role'] ?? '', ['Müdür', 'Gardiyan'])) { header("Location: mahkumlar.php"); exit; }
// ERD: olay_id | mahkum_id | raporlayan_id | olay_tarihi (datetime) | olay_detayi (text)

if (isset($_GET['kaydet'])) {
    $mahkum_id    = intval($_GET['mahkum_id'] ?? 0);
    $raporlayan_id = intval($_GET['raporlayan_id'] ?? 0);
    $olay_tarihi  = mysqli_real_escape_string($baglanti, $_GET['olay_tarihi'] ?? '');
    $olay_detayi  = mysqli_real_escape_string($baglanti, $_GET['olay_detayi'] ?? '');
    $rap_val = $raporlayan_id > 0 ? $raporlayan_id : 'NULL';
    mysqli_query($baglanti, "INSERT INTO disiplin_arsivi (mahkum_id, raporlayan_id, olay_tarihi, olay_detayi)
        VALUES ('$mahkum_id',$rap_val,'$olay_tarihi','$olay_detayi')");
    header("Location: disiplin_arsivi.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id           = intval($_GET['olay_id'] ?? 0);
    $mahkum_id    = intval($_GET['mahkum_id'] ?? 0);
    $raporlayan_id = intval($_GET['raporlayan_id'] ?? 0);
    $olay_tarihi  = mysqli_real_escape_string($baglanti, $_GET['olay_tarihi'] ?? '');
    $olay_detayi  = mysqli_real_escape_string($baglanti, $_GET['olay_detayi'] ?? '');
    $rap_val = $raporlayan_id > 0 ? $raporlayan_id : 'NULL';
    mysqli_query($baglanti, "UPDATE disiplin_arsivi SET mahkum_id='$mahkum_id', raporlayan_id=$rap_val,
        olay_tarihi='$olay_tarihi', olay_detayi='$olay_detayi' WHERE olay_id=$id");
    header("Location: disiplin_arsivi.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['olay_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM disiplin_arsivi WHERE olay_id=$id");
    header("Location: disiplin_arsivi.php"); exit;
}

$kayitlar = mysqli_query($baglanti, "
    SELECT d.*, m.ad AS m_ad, m.soyad AS m_soyad, p.ad AS p_ad, p.soyad AS p_soyad
    FROM disiplin_arsivi d
    LEFT JOIN mahkumlar m ON d.mahkum_id = m.mahkum_id
    LEFT JOIN personel p ON d.raporlayan_id = p.personel_id
    ORDER BY d.olay_id DESC
");

render_layout_start('Disiplin Arşivi');
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni Disiplin Kaydı Ekle</span></div>
    <div class="card-body">
        <form action="disiplin_arsivi.php" method="GET">
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
                    <label class="form-label">Raporlayan Personel</label>
                    <select class="form-control" name="raporlayan_id">
                        <option value="">– Seçiniz –</option>
                        <?php
                        $pt = mysqli_query($baglanti, "SELECT personel_id, ad, soyad, gorev FROM personel ORDER BY ad");
                        while ($p = mysqli_fetch_assoc($pt)):
                        ?>
                        <option value="<?= $p['personel_id'] ?>"><?= htmlspecialchars($p['ad'] . ' ' . $p['soyad'] . ' (' . $p['gorev'] . ')') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Olay Tarihi / Saati</label>
                    <input class="form-control" type="datetime-local" name="olay_tarihi">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Olay Detayı</label>
                    <textarea class="form-control" name="olay_detayi" rows="3" placeholder="Olayın detaylı açıklaması, uygulanan yaptırım..."></textarea>
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
        <span class="card-title">⚠️ Disiplin Kayıtları</span>
        <span class="badge badge-red"><?= mysqli_num_rows($kayitlar) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Mahkum</th><th>Olay Tarihi</th><th>Raporlayan</th><th>Olay Detayı</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($kayitlar) === 0): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">⚠️</div><p>Henüz disiplin kaydı yok.</p></div></td></tr>
            <?php else: while ($r = mysqli_fetch_assoc($kayitlar)): ?>
                <tr>
                    <td><span class="badge badge-red">#<?= $r['olay_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($r['m_ad'] . ' ' . $r['m_soyad']) ?></strong></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($r['olay_tarihi'] ?? '–') ?></td>
                    <td><?= htmlspecialchars(($r['p_ad'] ?? '') . ' ' . ($r['p_soyad'] ?? '–')) ?></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['olay_detayi'] ?? '') ?>">
                        <?= htmlspecialchars($r['olay_detayi'] ?? '–') ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($r)) ?>)" class="btn btn-warning btn-sm">✏️</button>
                        <a href="disiplin_arsivi.php?sil=1&olay_id=<?= $r['olay_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Disiplin kaydını silmek istiyor musunuz?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ Disiplin Kaydı Düzenle</div>
        <form action="disiplin_arsivi.php" method="GET">
            <input type="hidden" name="olay_id" id="edit_id">
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
                    <label class="form-label">Raporlayan Personel</label>
                    <select class="form-control" name="raporlayan_id" id="edit_raporlayan">
                        <option value="">– Seçiniz –</option>
                        <?php
                        $pt2 = mysqli_query($baglanti, "SELECT personel_id, ad, soyad, gorev FROM personel ORDER BY ad");
                        while ($p = mysqli_fetch_assoc($pt2)):
                        ?>
                        <option value="<?= $p['personel_id'] ?>"><?= htmlspecialchars($p['ad'] . ' ' . $p['soyad'] . ' (' . $p['gorev'] . ')') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Olay Tarihi / Saati</label>
                    <input class="form-control" type="datetime-local" name="olay_tarihi" id="edit_tarih">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Olay Detayı</label>
                    <textarea class="form-control" name="olay_detayi" id="edit_detay" rows="3"></textarea>
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
    document.getElementById('edit_id').value         = r.olay_id;
    document.getElementById('edit_mahkum').value     = r.mahkum_id;
    document.getElementById('edit_raporlayan').value = r.raporlayan_id || '';
    var dt = r.olay_tarihi ? r.olay_tarihi.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('edit_tarih').value      = dt;
    document.getElementById('edit_detay').value      = r.olay_detayi || '';
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
