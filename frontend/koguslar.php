<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

$user_role = $_SESSION['hmts_user_role'] ?? '';
$is_mudur  = ($user_role === 'Müdür');

// ===== KOĞUŞ İŞLEMLERİ (SADECE MÜDÜR) =====

if ($is_mudur && isset($_GET['kaydet'])) {
    $blok     = mysqli_real_escape_string($baglanti, $_GET['blok_adi'] ?? '');
    $kapasite = intval($_GET['kapasite'] ?? 10);
    $tip      = mysqli_real_escape_string($baglanti, $_GET['tip'] ?? 'Normal');
    mysqli_query($baglanti, "INSERT INTO koguslar (blok_adi, kapasite, tip) VALUES ('$blok','$kapasite','$tip')");
    header("Location: koguslar.php"); exit;
}

if ($is_mudur && isset($_GET['guncelle'])) {
    $id       = intval($_GET['kogus_id'] ?? 0);
    $blok     = mysqli_real_escape_string($baglanti, $_GET['blok_adi'] ?? '');
    $kapasite = intval($_GET['kapasite'] ?? 10);
    $tip      = mysqli_real_escape_string($baglanti, $_GET['tip'] ?? 'Normal');
    mysqli_query($baglanti, "UPDATE koguslar SET blok_adi='$blok', kapasite='$kapasite', tip='$tip' WHERE kogus_id=$id");
    header("Location: koguslar.php"); exit;
}

if ($is_mudur && isset($_GET['sil'])) {
    $id = intval($_GET['kogus_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM koguslar WHERE kogus_id=$id");
    header("Location: koguslar.php"); exit;
}

$koguslar = mysqli_query($baglanti, "
    SELECT k.*, COUNT(m.mahkum_id) as dolu
    FROM koguslar k
    LEFT JOIN mahkumlar m ON k.kogus_id = m.kogus_id
    GROUP BY k.kogus_id
    ORDER BY k.blok_adi
");

render_layout_start('Koğuş Yönetimi');
?>

<?php if ($is_mudur): ?>
<!-- EKLEME FORMU - SADECE MÜDÜR -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni Koğuş Ekle</span></div>
    <div class="card-body">
        <form action="koguslar.php" method="GET">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Blok Adı</label>
                    <input class="form-control" type="text" name="blok_adi" placeholder="Örn: A Blok" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kapasite</label>
                    <input class="form-control" type="number" name="kapasite" placeholder="Kişi sayısı" min="1" value="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Tip</label>
                    <select class="form-control" name="tip">
                        <option value="Normal">Normal</option>
                        <option value="Tecrit">Tecrit</option>
                        <option value="Kadın">Kadın</option>
                        <option value="Çocuk">Çocuk</option>
                        <option value="Hastane">Hastane</option>
                    </select>
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
    🛡️ Gardiyan yetkinizle koğuş listesini görüntüleyebilirsiniz. Ekleme, düzenleme ve silme işlemleri sadece Müdür tarafından yapılabilir.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏢 Koğuş Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($koguslar) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Blok Adı</th><th>Kapasite</th><th>Doluluk</th><th>Tip</th><th>Doluluk Oranı</th>
                    <?php if ($is_mudur): ?><th>İşlemler</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($koguslar) === 0): ?>
                <tr><td colspan="<?= $is_mudur ? 7 : 6 ?>"><div class="empty-state"><div class="empty-icon">🏢</div><p>Henüz koğuş kaydı yok.</p></div></td></tr>
            <?php else: while ($k = mysqli_fetch_assoc($koguslar)):
                $oran = $k['kapasite'] > 0 ? round(($k['dolu'] / $k['kapasite']) * 100) : 0;
                $oran_renk = $oran >= 90 ? '#ef4444' : ($oran >= 70 ? '#f59e0b' : '#22c55e');
                $tip_badge = match($k['tip']) {
                    'Tecrit'   => 'badge-red',
                    'Kadın'    => 'badge-purple',
                    'Çocuk'    => 'badge-yellow',
                    'Hastane'  => 'badge-green',
                    default    => 'badge-blue'
                };
            ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $k['kogus_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($k['blok_adi']) ?></strong></td>
                    <td><?= $k['kapasite'] ?></td>
                    <td><strong style="color:<?= $oran_renk ?>"><?= $k['dolu'] ?></strong></td>
                    <td><span class="badge <?= $tip_badge ?>"><?= htmlspecialchars($k['tip']) ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:rgba(255,255,255,0.1);border-radius:10px;overflow:hidden;">
                                <div style="width:<?= $oran ?>%;height:100%;background:<?= $oran_renk ?>;border-radius:10px;"></div>
                            </div>
                            <span style="font-size:12px;color:<?= $oran_renk ?>;font-weight:600;white-space:nowrap;">%<?= $oran ?></span>
                        </div>
                    </td>
                    <?php if ($is_mudur): ?>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($k)) ?>)" class="btn btn-warning btn-sm">✏️</button>
                        <a href="koguslar.php?sil=1&kogus_id=<?= $k['kogus_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Koğuşu silmek istediğinizden emin misiniz?')">🗑️</a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($is_mudur): ?>
<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ Koğuş Düzenle</div>
        <form action="koguslar.php" method="GET">
            <input type="hidden" name="kogus_id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Blok Adı</label>
                    <input class="form-control" type="text" name="blok_adi" id="edit_blok" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kapasite</label>
                    <input class="form-control" type="number" name="kapasite" id="edit_kapasite" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Tip</label>
                    <select class="form-control" name="tip" id="edit_tip">
                        <option value="Normal">Normal</option>
                        <option value="Tecrit">Tecrit</option>
                        <option value="Kadın">Kadın</option>
                        <option value="Çocuk">Çocuk</option>
                        <option value="Hastane">Hastane</option>
                    </select>
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
function doldurDuzenle(k) {
    document.getElementById('edit_id').value       = k.kogus_id;
    document.getElementById('edit_blok').value     = k.blok_adi || '';
    document.getElementById('edit_kapasite').value = k.kapasite || 10;
    document.getElementById('edit_tip').value      = k.tip || 'Normal';
    openModal('duzenleModal');
}
</script>
<?php endif; ?>

<?php render_layout_end(); ?>
