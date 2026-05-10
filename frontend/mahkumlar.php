<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

$user_role = $_SESSION['hmts_user_role'] ?? '';
$is_mudur  = ($user_role === 'Müdür');

// ===== MAHKUM İŞLEMLERİ (SADECE MÜDÜR) =====

if ($is_mudur && isset($_GET['kaydet'])) {
    $ad    = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $tc    = mysqli_real_escape_string($baglanti, $_GET['tc_no'] ?? '');
    $suc   = mysqli_real_escape_string($baglanti, $_GET['suc_turu'] ?? '');
    $giris = mysqli_real_escape_string($baglanti, $_GET['kayit_tarihi'] ?? '');
    $cikis = mysqli_real_escape_string($baglanti, $_GET['tahliye_tarihi'] ?? '');
    $kogus = intval($_GET['kogus_id'] ?? 0);
    $disip = intval($_GET['disiplin_puani'] ?? 0);
    $kogus_val = $kogus > 0 ? $kogus : 'NULL';
    $cikis_val = $cikis !== '' ? "'$cikis'" : 'NULL';
    mysqli_query($baglanti, "INSERT INTO mahkumlar (ad, soyad, tc_no, suc_turu, kayit_tarihi, tahliye_tarihi, kogus_id, disiplin_puani)
        VALUES ('$ad','$soyad','$tc','$suc','$giris',$cikis_val,$kogus_val,$disip)");
    header("Location: mahkumlar.php"); exit;
}

if ($is_mudur && isset($_GET['guncelle'])) {
    $id    = intval($_GET['mahkum_id'] ?? 0);
    $ad    = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $tc    = mysqli_real_escape_string($baglanti, $_GET['tc_no'] ?? '');
    $suc   = mysqli_real_escape_string($baglanti, $_GET['suc_turu'] ?? '');
    $giris = mysqli_real_escape_string($baglanti, $_GET['kayit_tarihi'] ?? '');
    $cikis = mysqli_real_escape_string($baglanti, $_GET['tahliye_tarihi'] ?? '');
    $kogus = intval($_GET['kogus_id'] ?? 0);
    $disip = intval($_GET['disiplin_puani'] ?? 0);
    $kogus_val = $kogus > 0 ? $kogus : 'NULL';
    $cikis_val = $cikis !== '' ? "'$cikis'" : 'NULL';
    mysqli_query($baglanti, "UPDATE mahkumlar SET ad='$ad', soyad='$soyad', tc_no='$tc', suc_turu='$suc',
        kayit_tarihi='$giris', tahliye_tarihi=$cikis_val, kogus_id=$kogus_val, disiplin_puani=$disip
        WHERE mahkum_id=$id");
    header("Location: mahkumlar.php"); exit;
}

if ($is_mudur && isset($_GET['sil'])) {
    $id = intval($_GET['mahkum_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM mahkumlar WHERE mahkum_id=$id");
    header("Location: mahkumlar.php"); exit;
}

$mahkumlar = mysqli_query($baglanti, "SELECT m.*, k.blok_adi FROM mahkumlar m LEFT JOIN koguslar k ON m.kogus_id = k.kogus_id ORDER BY m.mahkum_id DESC");

render_layout_start('Mahkum Yönetimi');
?>

<?php if ($is_mudur): ?>
<!-- EKLEME FORMU - SADECE MÜDÜR -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni Mahkum Ekle</span></div>
    <div class="card-body">
        <form action="mahkumlar.php" method="GET">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Ad</label>
                    <input class="form-control" type="text" name="ad" placeholder="Ad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Soyad</label>
                    <input class="form-control" type="text" name="soyad" placeholder="Soyad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">TC Kimlik No</label>
                    <input class="form-control" type="text" name="tc_no" placeholder="11 haneli TC" maxlength="11">
                </div>
                <div class="form-group">
                    <label class="form-label">Suç Türü</label>
                    <input class="form-control" type="text" name="suc_turu" placeholder="Suç türü">
                </div>
                <div class="form-group">
                    <label class="form-label">Kayıt Tarihi</label>
                    <input class="form-control" type="date" name="kayit_tarihi">
                </div>
                <div class="form-group">
                    <label class="form-label">Tahliye Tarihi</label>
                    <input class="form-control" type="date" name="tahliye_tarihi">
                </div>
                <div class="form-group">
                    <label class="form-label">Koğuş</label>
                    <select class="form-control" name="kogus_id">
                        <option value="">– Seçiniz –</option>
                        <?php
                        $k1 = mysqli_query($baglanti, "SELECT * FROM koguslar ORDER BY blok_adi");
                        while ($k = mysqli_fetch_assoc($k1)):
                        ?>
                        <option value="<?= $k['kogus_id'] ?>"><?= htmlspecialchars($k['blok_adi']) ?> (Kap: <?= $k['kapasite'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Disiplin Puanı</label>
                    <input class="form-control" type="number" name="disiplin_puani" value="0" min="0">
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Kaydet</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="readonly-notice">
    🛡️ Gardiyan yetkinizle mahkum listesini görüntüleyebilirsiniz. Ekleme, düzenleme ve silme işlemleri sadece Müdür tarafından yapılabilir.
</div>
<?php endif; ?>

<!-- LİSTE -->
<div class="card">
    <div class="card-header">
        <span class="card-title">🔒 Mahkum Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($mahkumlar) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Ad Soyad</th><th>TC No</th><th>Suç Türü</th>
                    <th>Koğuş</th><th>Kayıt Tarihi</th><th>Tahliye</th><th>Disiplin</th>
                    <?php if ($is_mudur): ?><th>İşlemler</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($mahkumlar) === 0): ?>
                <tr><td colspan="<?= $is_mudur ? 9 : 8 ?>"><div class="empty-state"><div class="empty-icon">🔒</div><p>Henüz mahkum kaydı yok.</p></div></td></tr>
            <?php else: while ($m = mysqli_fetch_assoc($mahkumlar)):
                $dp = intval($m['disiplin_puani'] ?? 0);
                $dp_cls = $dp === 0 ? 'badge-green' : ($dp <= 10 ? 'badge-yellow' : 'badge-red');
            ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $m['mahkum_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($m['ad'] . ' ' . $m['soyad']) ?></strong></td>
                    <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($m['tc_no'] ?? '–') ?></td>
                    <td><?= htmlspecialchars($m['suc_turu'] ?? '–') ?></td>
                    <td><span class="badge badge-teal"><?= htmlspecialchars($m['blok_adi'] ?? '–') ?></span></td>
                    <td><?= htmlspecialchars($m['kayit_tarihi'] ?? '–') ?></td>
                    <td><?= $m['tahliye_tarihi'] ? htmlspecialchars($m['tahliye_tarihi']) : '<span class="badge badge-green">Devam ediyor</span>' ?></td>
                    <td><span class="badge <?= $dp_cls ?>"><?= $dp ?></span></td>
                    <?php if ($is_mudur): ?>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($m)) ?>)" class="btn btn-warning btn-sm">✏️ Düzenle</button>
                        <a href="mahkumlar.php?sil=1&mahkum_id=<?= $m['mahkum_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu mahkumu silmek istediğinizden emin misiniz?')">🗑️ Sil</a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($is_mudur): ?>
<!-- DÜZENLEME MODALI - SADECE MÜDÜR -->
<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ Mahkum Düzenle</div>
        <form action="mahkumlar.php" method="GET">
            <input type="hidden" name="mahkum_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Ad</label>
                    <input class="form-control" type="text" name="ad" id="edit_ad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Soyad</label>
                    <input class="form-control" type="text" name="soyad" id="edit_soyad" required>
                </div>
                <div class="form-group">
                    <label class="form-label">TC No</label>
                    <input class="form-control" type="text" name="tc_no" id="edit_tc" maxlength="11">
                </div>
                <div class="form-group">
                    <label class="form-label">Suç Türü</label>
                    <input class="form-control" type="text" name="suc_turu" id="edit_suc">
                </div>
                <div class="form-group">
                    <label class="form-label">Kayıt Tarihi</label>
                    <input class="form-control" type="date" name="kayit_tarihi" id="edit_giris">
                </div>
                <div class="form-group">
                    <label class="form-label">Tahliye Tarihi</label>
                    <input class="form-control" type="date" name="tahliye_tarihi" id="edit_cikis">
                </div>
                <div class="form-group">
                    <label class="form-label">Koğuş</label>
                    <select class="form-control" name="kogus_id" id="edit_kogus">
                        <option value="">– Seçiniz –</option>
                        <?php
                        $k2 = mysqli_query($baglanti, "SELECT * FROM koguslar ORDER BY blok_adi");
                        while ($k = mysqli_fetch_assoc($k2)):
                        ?>
                        <option value="<?= $k['kogus_id'] ?>"><?= htmlspecialchars($k['blok_adi']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Disiplin Puanı</label>
                    <input class="form-control" type="number" name="disiplin_puani" id="edit_disip" min="0">
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
function doldurDuzenle(m) {
    document.getElementById('edit_id').value    = m.mahkum_id;
    document.getElementById('edit_ad').value    = m.ad || '';
    document.getElementById('edit_soyad').value = m.soyad || '';
    document.getElementById('edit_tc').value    = m.tc_no || '';
    document.getElementById('edit_suc').value   = m.suc_turu || '';
    document.getElementById('edit_giris').value = m.kayit_tarihi || '';
    document.getElementById('edit_cikis').value = m.tahliye_tarihi || '';
    document.getElementById('edit_kogus').value = m.kogus_id || '';
    document.getElementById('edit_disip').value = m.disiplin_puani || 0;
    openModal('duzenleModal');
}
</script>
<?php endif; ?>

<?php render_layout_end(); ?>
