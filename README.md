🏢 Hapishane Takip Sistemi (HMTS)HMTS, ceza infaz kurumlarındaki karmaşık operasyonel süreçleri dijitalleştirerek verimliliği artırmak ve veri güvenliğini en üst düzeye çıkarmak amacıyla geliştirilmiş merkezi bir yönetim platformudur.


🚀 Proje HakkındaSistem; mahkum yönetiminden personel takibine, sağlık kayıtlarından ziyaretçi organizasyonuna kadar geniş bir yelpazeyi kapsayan entegre bir mimariye sahiptir. Rol Tabanlı Erişim Kontrolü (RBAC) ile yetkilendirme süreçlerini yönetirken, modern arayüzü sayesinde kullanıcı dostu bir deneyim sunar.



🏗️ Sistem Mimarisi ve Veri YapısıVeritabanı tasarımı, veri bütünlüğünü korumak adına ilişkisel model üzerine inşa edilmiştir. Tüm sistemin merkezinde Mahkumlar tablosu yer alır.


📊 Temel Tablolar ve İlişkilerTablo AdıAçıklamaİlişki / BağlantıMahkumlarSistemin ana veri kümesi.kogus_id ile Koğuşlar tablosuna bağlıdır.KoguslarKapasite ve lokasyon bilgileri.Mahkumlar ile bire-çok ilişki kurar.PersonelÇalışan görev ve kimlik bilgileri.İdari süreç yönetimini sağlar.Saglik_KayitlariTıbbi geçmiş ve muayene verileri.mahkum_id ile mahkuma bağlıdır.Ziyaret_KayitlariZiyaretçi-Mahkum eşleşmeleri.Referans bütünlüğünü sağlayan ara tablodur.Disiplin_ArsiviCeza ve ödül geçmişi.mahkum_id anahtarı ile izlenir.Mahkum_Gorevleriİş yönetimi ve görev takibi.is_turleri tablosu ile entegre çalışır.

✨ Temel Özellikler🔗 Merkezi Entegrasyon: Tüm modüller yabancı anahtarlar (FK) ile birbirine bağlıdır.


🩺 Dijital Sağlık Takibi: Kağıt formlar yerine tamamen dijital tıbbi arşivleme.

👥 Güvenli Ziyaret Yönetimi: Ziyaretçi ve mahkum ilişkileri detaylı raporlanabilir.

🛠️ Görev Yönetimi: Kurum içi iş gücü planlaması ve iş kolları takibi.

📜 Şeffaf Disiplin Arşivi: Tüm disiplin süreçleri tarihçeli olarak saklanır.


⚙️ Teknik StandartlarVeri Bütünlüğü: SQL kısıtlamaları (constraints) ile hatalı girişler engellenir.Güvenlik: RBAC mimarisi ile sadece yetkili personelin veri görmesi sağlanır.Ölçeklenebilirlik: Gelecekteki modül eklemelerine uygun, modüler altyapı.


🔄 İş AkışıKayıt: Mahkum girişi ve koğuş ataması yapılır.Operasyon: Günlük sağlık, iş ve disiplin kayıtları sisteme işlenir.Ziyaret: Randevu ve ziyaretçi kayıtları mahkumla eşleştirilerek tutulur.Denetim: Tüm veriler dashboardlar üzerinden yetkili personelce izlenir.
