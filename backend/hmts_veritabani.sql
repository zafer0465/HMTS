-- =============================================
-- HMTS - Hapishane Yönetim ve Takip Sistemi
-- Veritabanı Kurulum SQL - v2.0
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ziyaret_kayitlari;
DROP TABLE IF EXISTS disiplin_arsivi;
DROP TABLE IF EXISTS saglik_kayitlari;
DROP TABLE IF EXISTS mahkum_gorevleri;
DROP TABLE IF EXISTS is_turleri;
DROP TABLE IF EXISTS ziyaretciler;
DROP TABLE IF EXISTS mahkumlar;
DROP TABLE IF EXISTS koguslar;
DROP TABLE IF EXISTS personel;
SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE IF NOT EXISTS hmts_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE hmts_db;

-- 1. İş Türleri
CREATE TABLE IF NOT EXISTS is_turleri (
    is_turu_id  INT AUTO_INCREMENT PRIMARY KEY,
    is_adi      VARCHAR(150) NOT NULL,
    aciklama    TEXT
) ENGINE=InnoDB;

-- 2. Koğuşlar
CREATE TABLE IF NOT EXISTS koguslar (
    kogus_id   INT AUTO_INCREMENT PRIMARY KEY,
    blok_adi   VARCHAR(100) NOT NULL,
    kapasite   INT DEFAULT 10,
    tip        VARCHAR(50) DEFAULT 'Normal'
) ENGINE=InnoDB;

-- 3. Personel
CREATE TABLE IF NOT EXISTS personel (
    personel_id    INT AUTO_INCREMENT PRIMARY KEY,
    ad             VARCHAR(100) NOT NULL,
    soyad          VARCHAR(100) NOT NULL,
    telefon        VARCHAR(20),
    gorev          VARCHAR(50) DEFAULT 'Gardiyan',
    kullanici_adi  VARCHAR(100) UNIQUE,
    sifre          VARCHAR(255)
) ENGINE=InnoDB;

-- 4. Mahkumlar
CREATE TABLE IF NOT EXISTS mahkumlar (
    mahkum_id       INT AUTO_INCREMENT PRIMARY KEY,
    ad              VARCHAR(100) NOT NULL,
    soyad           VARCHAR(100) NOT NULL,
    tc_no           VARCHAR(11),
    suc_turu        VARCHAR(255),
    kayit_tarihi    DATE,
    tahliye_tarihi  DATE,
    kogus_id        INT,
    disiplin_puani  INT DEFAULT 0,
    FOREIGN KEY (kogus_id) REFERENCES koguslar(kogus_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Ziyaretçiler
CREATE TABLE IF NOT EXISTS ziyaretciler (
    ziyaretci_id  INT AUTO_INCREMENT PRIMARY KEY,
    tc_no         VARCHAR(11),
    ad            VARCHAR(100) NOT NULL,
    soyad         VARCHAR(100) NOT NULL,
    telefon       VARCHAR(20)
) ENGINE=InnoDB;

-- 6. Mahkum Görevleri
CREATE TABLE IF NOT EXISTS mahkum_gorevleri (
    gorev_id           INT AUTO_INCREMENT PRIMARY KEY,
    mahkum_id          INT,
    is_turu_id         INT,
    sorumlu_personel_id INT,
    baslangic_saati    DATETIME,
    bitis_saati        DATETIME,
    durum              VARCHAR(50) DEFAULT 'Aktif',
    FOREIGN KEY (mahkum_id)           REFERENCES mahkumlar(mahkum_id) ON DELETE CASCADE,
    FOREIGN KEY (is_turu_id)          REFERENCES is_turleri(is_turu_id) ON DELETE CASCADE,
    FOREIGN KEY (sorumlu_personel_id) REFERENCES personel(personel_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. Sağlık Kayıtları
CREATE TABLE IF NOT EXISTS saglik_kayitlari (
    kayit_id        INT AUTO_INCREMENT PRIMARY KEY,
    mahkum_id       INT,
    muayene_tarihi  DATE,
    doktor_adi      VARCHAR(200),
    teshis          TEXT,
    FOREIGN KEY (mahkum_id) REFERENCES mahkumlar(mahkum_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Disiplin Arşivi
CREATE TABLE IF NOT EXISTS disiplin_arsivi (
    olay_id       INT AUTO_INCREMENT PRIMARY KEY,
    mahkum_id     INT,
    raporlayan_id INT,
    olay_tarihi   DATETIME,
    olay_detayi   TEXT,
    FOREIGN KEY (mahkum_id)     REFERENCES mahkumlar(mahkum_id) ON DELETE CASCADE,
    FOREIGN KEY (raporlayan_id) REFERENCES personel(personel_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Ziyaret Kayıtları
CREATE TABLE IF NOT EXISTS ziyaret_kayitlari (
    ziyaret_id    INT AUTO_INCREMENT PRIMARY KEY,
    mahkum_id     INT,
    ziyaretci_id  INT,
    tarih_saat    DATETIME,
    FOREIGN KEY (mahkum_id)    REFERENCES mahkumlar(mahkum_id) ON DELETE CASCADE,
    FOREIGN KEY (ziyaretci_id) REFERENCES ziyaretciler(ziyaretci_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- ÖRNEK VERİ
-- =============================================

-- İş Türleri
INSERT INTO is_turleri (is_adi, aciklama) VALUES
('Mutfak Görevlisi', 'Yemek hazırlama ve servis görevleri'),
('Temizlik Görevlisi', 'Ortak alanların temizliği'),
('Bahçe Bakım', 'Yeşil alan ve bahçe düzenleme'),
('Çamaşırhane', 'Tekstil yıkama ve bakım'),
('Depo Görevlisi', 'Depo ve malzeme yönetimi');

-- Koğuşlar
INSERT INTO koguslar (blok_adi, kapasite, tip) VALUES
('A Blok', 20, 'Normal'),
('B Blok', 15, 'Normal'),
('C Blok', 10, 'Tecrit'),
('D Blok', 8, 'Kadın'),
('Hastane Koğuşu', 5, 'Hastane');

-- Personel (Şifre: admin123, doktor123, gardiyan123)
INSERT INTO personel (ad, soyad, gorev, telefon, kullanici_adi, sifre) VALUES
('Ahmet', 'Yılmaz', 'Müdür', '0532 111 22 33', 'admin', 'admin123'),
('Fatma', 'Kaya', 'Doktor', '0533 222 33 44', 'doktor', 'doktor123'),
('Mehmet', 'Demir', 'Gardiyan', '0534 333 44 55', 'gardiyan', 'gardiyan123'),
('Zeynep', 'Şahin', 'Psikolog', '0535 444 55 66', 'psikolog', 'psikolog123');

-- Mahkumlar
INSERT INTO mahkumlar (ad, soyad, tc_no, suc_turu, kayit_tarihi, tahliye_tarihi, kogus_id, disiplin_puani) VALUES
('Ali', 'Çelik', '12345678901', 'Hırsızlık', '2023-01-15', '2026-01-15', 1, 10),
('Veli', 'Özkan', '23456789012', 'Dolandırıcılık', '2022-06-10', '2025-06-10', 2, 5),
('Ayşe', 'Aydın', '34567890123', 'Uyuşturucu', '2024-03-20', NULL, 4, 0),
('Hasan', 'Kılıç', '45678901234', 'Silahlı Soygun', '2021-11-05', '2031-11-05', 3, 25);

-- Ziyaretçiler
INSERT INTO ziyaretciler (tc_no, ad, soyad, telefon) VALUES
('11111111111', 'Selin', 'Çelik', '0536 111 22 33'),
('22222222222', 'Mustafa', 'Özkan', '0537 222 33 44'),
('33333333333', 'Hülya', 'Aydın', '0538 333 44 55');

-- Mahkum Görevleri
INSERT INTO mahkum_gorevleri (mahkum_id, is_turu_id, sorumlu_personel_id, baslangic_saati, bitis_saati, durum) VALUES
(1, 1, 3, '2024-01-01 08:00:00', '2024-06-30 17:00:00', 'Tamamlandı'),
(2, 2, 3, '2024-03-01 08:00:00', NULL, 'Aktif'),
(4, 3, 3, '2024-04-01 08:00:00', NULL, 'Aktif');

-- Sağlık Kayıtları
INSERT INTO saglik_kayitlari (mahkum_id, muayene_tarihi, doktor_adi, teshis) VALUES
(1, '2024-02-15', 'Dr. Fatma Kaya', 'Hipertansiyon - İlaç tedavisi başlatıldı'),
(3, '2024-03-20', 'Dr. Fatma Kaya', 'Anksiyete Bozukluğu - Psikolojik destek ve ilaç');

-- Disiplin Arşivi
INSERT INTO disiplin_arsivi (mahkum_id, raporlayan_id, olay_tarihi, olay_detayi) VALUES
(4, 3, '2024-01-10 14:30:00', 'Koğuşta diğer mahkumlarla kavga etti. 3 gün tecrit cezası uygulandı.'),
(2, 3, '2024-02-20 10:00:00', 'Ziyaret saati kurallarını ihlal etti. Uyarı verildi.');

-- Ziyaret Kayıtları
INSERT INTO ziyaret_kayitlari (mahkum_id, ziyaretci_id, tarih_saat) VALUES
(1, 1, '2024-03-15 14:00:00'),
(2, 2, '2024-03-16 10:30:00'),
(3, 3, '2024-03-17 15:00:00');
