# Öğrenci Takip ve Koçluk Sistemi

Sınava hazırlanan öğrencileri takip eden koçlar için geliştirilmiş kapsamlı bir öğrenci yönetim sistemi.

## Özellikler

### 🎯 3 Farklı Panel

#### 1. Admin Paneli
- Tüm koçları listeleme ve yönetim
- Koçların öğrenci sayılarını görüntüleme
- Alan yönetimi (TYT, AYT, DGS, KPSS)
- Ders, konu ve alt konu yönetimi (hiyerarşik yapı)
- Abonelik ve ödeme takibi
- Sistem geneli istatistikler

#### 2. Koç Paneli
- Öğrenci ekleme ve yönetimi
- Öğrencilere ders/konu/alt konu atama
- Özel ders şablonları oluşturma (sözel/sayısal paketler)
- Şablonları öğrencilere atama
- Öğrenci konu tamamlama durumlarını takip
- Öğrencilerin soru çözüm raporlarını görüntüleme
- Deneme sonuçlarını takip

#### 3. Öğrenci Paneli
- Günlük soru çözüm kaydı (otomatik boş hesaplama)
- Konu çalışma takibi (video, kaynak vb.)
- Deneme sonuçları girişi (otomatik net hesaplama: 4 yanlış = 1 doğru düşer)
- Atanan ders ve konuları görüntüleme
- İlerleme takibi

## Teknik Özellikler

### Teknolojiler
- **Backend:** Laravel 11.x
- **Frontend:** Livewire 3.x
- **Styling:** Tailwind CSS (Açık ton, göz yormayan renk paleti)
- **Database:** SQLite (development) / MySQL (production)
- **Authentication:** Laravel Sanctum + Multi-guard
- **E-posta:** Laravel Notifications + Queue
- **Dil Desteği:** Türkçe & İngilizce

### Renk Paleti
- **Primary:** Açık bej tonları (#F5F3EF, #E8E4DC)
- **Secondary:** Açık gri (#F8F9FA, #E9ECEF)
- **Accent:** Yumuşak mavi (#7C9CBF) ve yeşil (#8FA998)
- **Modern, premium, göz yormayan tasarım**

### Abonelik Paketleri
- **Başlangıç:** 10 öğrenci - ₺199/ay
- **Standart:** 25 öğrenci - ₺399/ay
- **Premium:** 50 öğrenci - ₺699/ay
- **Sınırsız:** Sınırsız öğrenci - ₺999/ay
- 14 gün ücretsiz deneme

## Kurulum

### Gereksinimler
- PHP 8.2+
- Composer
- Node.js 18+ ve npm
- SQLite veya MySQL

### Adımlar

1. Bağımlılıkları yükleyin:
```bash
composer install
npm install
```

2. Veritabanını oluşturun ve seed edin:
```bash
php artisan migrate:fresh --seed
```

3. Assets'leri build edin:
```bash
npm run build
# veya development için
npm run dev
```

4. Uygulamayı başlatın:
```bash
php artisan serve
```

## Demo Hesaplar

Sistem demo verilerle birlikte gelir:

- **Admin:** admin@ogrenci.com / password
- **Koç 1:** coach1@ogrenci.com / password
- **Koç 2:** coach2@ogrenci.com / password
- **Öğrenci 1-6:** student1@ogrenci.com - student6@ogrenci.com / password

## Veritabanı Yapısı

### Ana Tablolar
- `users` - Kullanıcılar (admin, koç, öğrenci)
- `roles` - Rol yönetimi
- `subscriptions` - Koç abonelikleri
- `subscription_plans` - Abonelik paketleri
- `fields` - Sınav alanları (TYT, AYT, DGS, KPSS)
- `courses` - Dersler
- `topics` - Konular
- `sub_topics` - Alt konular
- `coach_students` - Koç-öğrenci ilişkileri
- `student_assignments` - Öğrenci atamaları
- `assignment_progress` - Konu tamamlanma durumları
- `course_templates` - Koç ders şablonları
- `template_items` - Şablon içerikleri
- `question_logs` - Soru çözüm kayıtları
- `study_logs` - Çalışma kayıtları
- `exam_results` - Deneme sonuçları

## E-posta Bildirimleri

Sistem aşağıdaki durumlarda otomatik e-posta gönderir:
- Öğrenci ekleme (hoş geldin maili)
- Abonelik bitiş uyarısı (7 gün önceden)
- Ödeme hatırlatmaları

## Güvenlik

- CSRF koruması (Laravel default)
- XSS koruması
- Rate limiting
- Rol bazlı erişim kontrolü
- Password hashing

## Performans

- Eager loading ile N+1 query optimizasyonu
- Database indexleme
- Asset minification ve compression

## Lisans

Bu proje özel kullanım için geliştirilmiştir.

## Destek

Sorularınız için: info@ogrenci.com

---

**Geliştirici Notları:**

Sistemde kullanılan özellikler:
- ✅ Rol bazlı authentication (admin, coach, student)
- ✅ Hiyerarşik ders yapısı (alan > ders > konu > alt konu)
- ✅ Özel ders şablonları
- ✅ Otomatik hesaplamalar (boş soru, net hesaplama)
- ✅ Çoklu dil desteği (TR/EN)
- ✅ Responsive tasarım
- ✅ Modern UI/UX
- ✅ E-posta bildirimleri
- ✅ Abonelik yönetimi
# ogrenci
