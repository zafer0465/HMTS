<?php
session_start();
if (!isset($_SESSION['hmts_logged_in'])) { header("Location: ../../index.php"); exit; }
define('HMTS_INCLUDED', true);
require_once '../../backend/baglanti.php';
require_once '_layout.php';

// ===== MAHKUM GÖREVLERİ – MÜDÜR + GARDİYAN =====
if (!in_array($_SESSION['hmts_user_role'] ?? '', ['Müdür', 'Gardiyan'])) { header("Location: mahkumlar.php"); exit; }
// ERD: gorev_id | mahkum_id | is_turu_id | sorumlu_personel_id | baslangic_saati | bitis_saati | durum

if (isset($_GET['kaydet'])) {
    $mahkum_id  = intval($_GET['mahkum_id'] ?? 0);
    $is_turu_id = intval($_GET['is_turu_id'] ?? 0);
    $sorumlu_id = intval($_GET['sorumlu_personel_id'] ?? 0);
    $baslangic  = mysqli_real_escape_string($baglanti, $_GET['baslangic_saati'] ?? '');
    $bitis      = mysqli_real_escape_string($baglanti, $_GET['bitis_saati'] ?? '');
    $durum      = mysqli_real_escape_string($baglanti, $_GET['durum'] ?? 'Aktif');
    $sor_val    = $sorumlu_id > 0 ? $sorumlu_id : 'NULL';
    $bitis_val  = $bitis !== '' ? "'$bitis'" : 'NULL';
    mysqli_query($baglanti, "INSERT INTO mahkum_gorevleri (mahkum_id, is_turu_id, sorumlu_personel_id, baslangic_saati, bitis_saati, durum)
        VALUES ('$mahkum_id','$is_turu_id',$sor_val,'$baslangic',$bitis_val,'$durum')");
    header("Location: mahkum_gorevleri.php"); exit;
}

if (isset($_GET['guncelle'])) {
    $id         = intval($_GET['gorev_id'] ?? 0);
    $mahkum_id  = intval($_GET['mahkum_id'] ?? 0);
    $is_turu_id = intval($_GET['is_turu_id'] ?? 0);
    $sorumlu_id = intval($_GET['sorumlu_personel_id'] ?? 0);
    $baslangic  = mysqli_real_escape_string($baglanti, $_GET['baslangic_saati'] ?? '');
    $bitis      = mysqli_real_escape_string($baglanti, $_GET['bitis_saati'] ?? '');
    $durum      = mysqli_real_escape_string($baglanti, $_GET['durum'] ?? 'Aktif');
    $sor_val    = $sorumlu_id > 0 ? $sorumlu_id : 'NULL';
    $bitis_val  = $bitis !== '' ? "'$bitis'" : 'NULL';
    mysqli_query($baglanti, "UPDATE mahkum_gorevleri SET mahkum_id='$mahkum_id', is_turu_id='$is_turu_id',
        sorumlu_personel_id=$sor_val, baslangic_saati='$baslangic', bitis_saati=$bitis_val, durum='$durum'
        WHERE gorev_id=$id");
    header("Location: mahkum_gorevleri.php"); exit;
}

if (isset($_GET['sil'])) {
    $id = intval($_GET['gorev_id'] ?? 0);
    if ($id > 0) mysqli_query($baglanti, "DELETE FROM mahkum_gorevleri WHERE gorev_id=$id");
    header("Location: mahkum_gorevleri.php"); exit;
}

$gorevler = mysqli_query($baglanti, "
    SELECT mg.*, m.ad AS m_ad, m.soyad AS m_soyad, it.is_adi, p.ad AS p_ad, p.soyad AS p_soyad
    FROM mahkum_gorevleri mg
    LEFT JOIN mahkumlar m ON mg.mahkum_id = m.mahkum_id
    LEFT JOIN is_turleri it ON mg.is_turu_id = it.is_turu_id
    LEFT JOIN personel p ON mg.sorumlu_personel_id = p.personel_id
    ORDER BY mg.gorev_id DESC
");

render_layout_start('Mahkum Görevleri');
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><span class="card-title">➕ Yeni Görev Ata</span></div>
    <div class="card-body">
        <form action="mahkum_gorevleri.php" method="GET">
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
                    <label class="form-label">İş Türü</label>
                    <select class="form-control" name="is_turu_id" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $it = mysqli_query($baglanti, "SELECT is_turu_id, is_adi FROM is_turleri ORDER BY is_adi");
                        while ($i = mysqli_fetch_assoc($it)):
                        ?>
                        <option value="<?= $i['is_turu_id'] ?>"><?= htmlspecialchars($i['is_adi']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sorumlu Personel</label>
                    <select class="form-control" name="sorumlu_personel_id">
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
                    <label class="form-label">Başlangıç Saati</label>
                    <input class="form-control" type="datetime-local" name="baslangic_saati">
                </div>
                <div class="form-group">
                    <label class="form-label">Bitiş Saati</label>
                    <input class="form-control" type="datetime-local" name="bitis_saati">
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-control" name="durum">
                        <option value="Aktif">Aktif</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                        <option value="İptal">İptal</option>
                    </select>
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
        <span class="card-title">🔧 Görev Listesi</span>
        <span class="badge badge-blue"><?= mysqli_num_rows($gorevler) ?> kayıt</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Mahkum</th><th>İş Türü</th><th>Başlangıç</th><th>Bitiş</th><th>Sorumlu</th><th>Durum</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($gorevler) === 0): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🔧</div><p>Henüz görev kaydı yok.</p></div></td></tr>
            <?php else: while ($g = mysqli_fetch_assoc($gorevler)):
                $durum_badge = match($g['durum']) {
                    'Aktif'      => 'badge-green',
                    'Tamamlandı' => 'badge-blue',
                    default      => 'badge-red'
                };
            ?>
                <tr>
                    <td><span class="badge badge-blue">#<?= $g['gorev_id'] ?></span></td>
                    <td><strong style="color:var(--text)"><?= htmlspecialchars($g['m_ad'] . ' ' . $g['m_soyad']) ?></strong></td>
                    <td><span class="badge badge-purple"><?= htmlspecialchars($g['is_adi'] ?? '–') ?></span></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($g['baslangic_saati'] ?? '–') ?></td>
                    <td style="font-size:12px;"><?= $g['bitis_saati'] ? htmlspecialchars($g['bitis_saati']) : '–' ?></td>
                    <td><?= htmlspecialchars(($g['p_ad'] ?? '') . ' ' . ($g['p_soyad'] ?? '–')) ?></td>
                    <td><span class="badge <?= $durum_badge ?>"><?= htmlspecialchars($g['durum']) ?></span></td>
                    <td style="white-space:nowrap;">
                        <button onclick="doldurDuzenle(<?= htmlspecialchars(json_encode($g)) ?>)" class="btn btn-warning btn-sm">✏️</button>
                        <a href="mahkum_gorevleri.php?sil=1&gorev_id=<?= $g['gorev_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Görevi silmek istiyor musunuz?')">🗑️</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="duzenleModal">
    <div class="modal">
        <div class="modal-title">✏️ Görev Düzenle</div>
        <form action="mahkum_gorevleri.php" method="GET">
            <input type="hidden" name="gorev_id" id="edit_id">
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
                    <label class="form-label">İş Türü</label>
                    <select class="form-control" name="is_turu_id" id="edit_is" required>
                        <option value="">– Seçiniz –</option>
                        <?php
                        $it2 = mysqli_query($baglanti, "SELECT is_turu_id, is_adi FROM is_turleri ORDER BY is_adi");
                        while ($i = mysqli_fetch_assoc($it2)):
                        ?>
                        <option value="<?= $i['is_turu_id'] ?>"><?= htmlspecialchars($i['is_adi']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sorumlu Personel</label>
                    <select class="form-control" name="sorumlu_personel_id" id="edit_sor">
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
                    <label class="form-label">Başlangıç Saati</label>
                    <input class="form-control" type="datetime-local" name="baslangic_saati" id="edit_bas">
                </div>
                <div class="form-group">
                    <label class="form-label">Bitiş Saati</label>
                    <input class="form-control" type="datetime-local" name="bitis_saati" id="edit_bit">
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-control" name="durum" id="edit_durum">
                        <option value="Aktif">Aktif</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                        <option value="İptal">İptal</option>
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
function doldurDuzenle(g) {
    document.getElementById('edit_id').value     = g.gorev_id;
    document.getElementById('edit_mahkum').value = g.mahkum_id;
    document.getElementById('edit_is').value     = g.is_turu_id;
    document.getElementById('edit_sor').value    = g.sorumlu_personel_id || '';
    var bas = g.baslangic_saati ? g.baslangic_saati.replace(' ', 'T').substring(0, 16) : '';
    var bit = g.bitis_saati     ? g.bitis_saati.replace(' ', 'T').substring(0, 16)     : '';
    document.getElementById('edit_bas').value    = bas;
    document.getElementById('edit_bit').value    = bit;
    document.getElementById('edit_durum').value  = g.durum;
    openModal('duzenleModal');
}
</script>

<?php render_layout_end(); ?>
