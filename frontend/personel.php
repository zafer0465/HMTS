<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

// ===== PERSONEL İŞLEMLERİ (SADECE MÜDÜR) =====
// Müdür olmayan kullanıcı buraya erişemez (_layout.php zaten engelliyor)
// Ek güvenlik katmanı:
if ($_SESSION['hmts_user_role'] !== 'Müdür') {
    header("Location: mahkumlar.php"); exit;
}

if (isset($_GET['kaydet'])) {
    $ad    = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $gorev = mysqli_real_escape_string($baglanti, $_GET['gorev'] ?? 'Gardiyan');
    $tel   = mysqli_real_escape_string($baglanti, $_GET['telefon'] ?? '');
    $ku    = mysqli_real_escape_string($baglanti, $_GET['kullanici_adi'] ?? '');
    $si    = mysqli_real_escape_string($baglanti, $_GET['sifre'] ?? '');
    mysqli_query($baglanti, "INSERT INTO personel (ad, soyad, gorev, telefon, kullanici_adi, sifre) VALUES ('$ad','$soyad','$gorev','$tel','$ku','$si')");
    header("Location: personel.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id    = intval($_GET['personel_id'] ?? 0);
    $ad    = mysqli_real_escape_string($baglanti, $_GET['ad'] ?? '');
    $soyad = mysqli_real_escape_string($baglanti, $_GET['soyad'] ?? '');
    $gorev = mysqli_real_escape_string($baglanti, $_GET['gorev'] ?? 'Gardiyan');
    $tel   = mysqli_real_escape_string($baglanti, $_GET['telefon'] ?? '');
    $ku    = mysqli_real_escape_string($baglanti, $_GET['kullanici_adi'] ?? '');
    $si    = mysqli_real_escape_string($baglanti, $_GET['sifre'] ?? '');
    mysqli_query($baglanti, "UPDATE personel SET ad='$ad', soyad='$soyad', gorev='$gorev', telefon='$tel', kullanici_adi='$ku', sifre='$si' WHERE personel_id=$id");
    header("Location: personel.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['personel_id'] ?? 0);
    // Kendini silemesin
    if ($id > 0 && $id !== intval($_SESSION['hmts_user_id'] ?? 0)) {
        mysqli_query($baglanti, "DELETE FROM personel WHERE personel_id=$id");
    }
    header("Location: personel.php"); exit;
}

$personeller = mysqli_query($baglanti, "SELECT * FROM personel ORDER BY gorev, ad");

render_layout_start('Personel Yönetimi');
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni Personel Ekle</span></div>
    <div class="card-body">
        <form action="personel.php" method="GET">
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
                    <label class="form-label">Görev / Rol</label>
                    <select class="form-control" name="gorev">
                        <option value="Müdür">👑 Müdür</option>
                        <option value="Gardiyan" selected>🛡️ Gardiyan</option>
                        <option value="Doktor">🏥 Doktor</option>
                        <option value="Psikolog">🧠 Psikolog</option>
                        <option value="Memur">📋 Memur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input class="form-control" type="text" name="telefon" placeholder="0555 000 00 00">
                </div>
                <div class="form-group">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input class="form-control" type="text" name="kullanici_adi" placeholder="Sistem giriş adı">
                </div>
                <div class="form-group">
                    <label class="form-label">Şifre</label>
                    <input class="form-control" type="text" name="sifre" placeholder="Şifre">
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" name="kaydet" value="1" class="btn btn-success">💾 Personel Ekle</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">👤 Personel Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($personeller) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Ad Soyad</th><th>Görev / Rol</th><th>Telefon</th><th>Kullanıcı Adı</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($personeller) === 0): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👤</div><p>Henüz personel kaydı yok.</p></div></td></tr>
            <?php else: while ($p = mysqli_fetch_assoc($personeller)):
                $gorev_renk = match($p['gorev'] ?? '') {
                    'Müdür'    => 'badge-blue',
                    'Doktor'   => 'badge-green',
                    'Gardiyan' => 'badge-yellow',
                    'Psikolog' => 'badge-purple',
                    default    => 'badge-teal'
                };
                $gorev_ikon = match($p['gorev'] ?? '') {
                    'Müdür'    => '👑',
                    'Doktor'   => '🏥',
                    'Gardiyan' => '🛡️',
                    'Psikolog' => '🧠',
                    default    => '📋'
                };
                $is_self = ($p['personel_id'] == ($_SESSION['hmts_user_id'] ?? 0));
            ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $p['personel_id'] ?></span></td>
                    <td>
                        <strong style="color:var(--text)"><?= htmlspecialchars($p['ad'] . ' ' . $p['soyad']) ?></strong>
                        <?php if ($is_self): ?><span class="badge badge-teal" style="margin-left:6px;">Sen</span><?php endif; ?>
                    </td>
                    <td><span class="badge <?= $gorev_renk ?>"><?= $gorev_ikon ?> <?= htmlspecialchars($p['gorev'] ?? '–') ?></span></td>
                    <td><?= htmlspecialchars($p['telefon'] ?? '–') ?></td>
                    <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($p['kullanici_adi'] ?? '–') ?></td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-warning btn-sm">✏️ Düzenle</button>
                        <?php if (!$is_self): ?>
                        <a href="personel.php?sil=1&personel_id=<?= $p['personel_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu personeli silmek istediğinizden emin misiniz?')">🗑️ Sil</a>
                        <?php else: ?>
                        <span class="badge badge-yellow" style="font-size:11px;">Aktif hesap</span>
                        <?php endif; ?>
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
        <div class="modal-title">✏️ Personel Düzenle</div>
        <form action="personel.php" method="GET">
            <input type="hidden" name="personel_id" id="edit_id">
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
                    <label class="form-label">Görev / Rol</label>
                    <select class="form-control" name="gorev" id="edit_gorev">
                        <option value="Müdür">👑 Müdür</option>
                        <option value="Gardiyan">🛡️ Gardiyan</option>
                        <option value="Doktor">🏥 Doktor</option>
                        <option value="Psikolog">🧠 Psikolog</option>
                        <option value="Memur">📋 Memur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input class="form-control" type="text" name="telefon" id="edit_tel">
                </div>
                <div class="form-group">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input class="form-control" type="text" name="kullanici_adi" id="edit_ku">
                </div>
                <div class="form-group">
                    <label class="form-label">Şifre</label>
                    <input class="form-control" type="text" name="sifre" id="edit_si">
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
function doldurDuzenle(p) {
    document.getElementById('edit_id').value    = p.personel_id;
    document.getElementById('edit_ad').value    = p.ad || '';
    document.getElementById('edit_soyad').value = p.soyad || '';
    document.getElementById('edit_gorev').value = p.gorev || 'Gardiyan';
    document.getElementById('edit_tel').value   = p.telefon || '';
    document.getElementById('edit_ku').value    = p.kullanici_adi || '';
    document.getElementById('edit_si').value    = p.sifre || '';
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
