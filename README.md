# 🎬 Movie® - Modern Film, Dizi, Anime & Spor Platformu (v1.0.0)

<p align="center">
  <img src="public/images/favicons/icon-192x192.png" alt="Movie® Logo" width="96" height="96" />
</p>

<p align="center">
  <strong>Netflix & Prime Video standartlarında, karanlık temalı (Dark Mode), tam kapsamlı ve açık kaynaklı yeni nesil streaming platformu.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%5E8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+" />
  <img src="https://img.shields.io/badge/Laravel-11%20%2F%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Inertia.js-v2-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="License MIT" />
</p>

---

## 📌 İçindekiler
- [Proje Genel Bakış](#-proje-genel-bakış)
- [Öne Çıkan Özellikler](#-öne-çıkan-özellikler)
  - [Kullanıcı & İzleme Deneyimi](#-kullanıcı--izleme-deneyimi)
  - [Kullanıcı Paneli (Dashboard)](#-kullanıcı-paneli-dashboard)
  - [Gelişmiş Yönetim Paneli (Admin Suite)](#-gelişmiş-yönetim-paneli-admin-suite)
- [Teknoloji Mimarisi](#-teknoloji-mimarisi)
- [Hazır Kullanıcı Giriş Bilgileri](#-hazır-kullanıcı-giriş-bilgileri)
- [Kurulum Adımları (Yerel / Localhost)](#-kurulum-adımları-yerel--localhost)
- [Canlı Sunucu & cPanel Dağıtım Rehberi](#-canlı-sunucu--cpanel-dağıtım-rehberi)
- [Klasör Yapısı](#-klasör-yapısı)
- [Lisans](#-lisans)

---

## 🌟 Proje Genel Bakış

**Movie®**, Laravel 11/12 backend gücü ile Vue 3 (Composition API) ve Inertia.js v2 SPA (Single Page Application) mimarisini birleştiren tam teşekküllü bir video streaming platformudur. 

Geleneksel web sitelerinin aksine sayfalar arası geçişler sayfa yenilenmeden milisaniyeler içinde gerçekleşir. TMDB (The Movie Database) entegrasyonu sayesinde tüm film, dizi, sezon, bölüm, oyuncu, fragman ve poster verilerini otomatik olarak içeri aktarabilir ve yönetebilirsiniz.

---

## ✨ Öne Çıkan Özellikler

### 📺 Kullanıcı & İzleme Deneyimi
* **Single Page Application (SPA):** Inertia.js ve Vue 3 ile sayfa yenilenmeden akıcı geçişler.
* **Progressive Web App (PWA):** Masaüstü ve mobil tarayıcılardan doğrudan telefona/bilgisayara uygulama gibi yüklenebilme (`manifest.json` ve Service Worker altyapısı).
* **Çoklu Dil Desteği (i18n):** Türkçe (`tr`) ve İngilizce (`en`) tam dil desteği, tek tıkla anında dil değiştirme.
* **Çoklu Video Sunucu Desteği:** Her video ve bölüm için birden fazla alternatif kaynak (VidCloud, UpCloud, StreamTape, vb.) ekleme.
* **Kapsamlı Medya Sayfaları:**
  * Sezon ve bölüm seçici akordeon menüsü
  * Detaylı oyuncu ve yapım ekibi kadrosu
  * Fragman modal oynatıcısı (YouTube embed)
  * Yapım bütçesi, hasılatı, süresi ve IMDb / Rotten Tomatoes puanları
  * Tür ve etiket bazlı benzer yapım önerileri
* **Gelişmiş Arama & Filtreleme (Mega Search):** Anlık canlı başlık araması; tür, yapım yılı, IMDb puanı ve popülerliğe göre dinamik filtreleme.
* **"Ne İzlesem?" Kararsız Modu:** Kararsız kalan kullanıcılar için tek tıkla rastgele film veya dizi öneren eğlenceli araç.
* **Kullanıcı Etkileşimi:** Yorum yazma, beğenme, 10 üzerinden puanlama ve çalışmayan video kaynaklarını tek tıkla bildirme (Kırık Link Raporlama).

### 👤 Kullanıcı Paneli (Dashboard)
* **İzleme Geçmişi & Kaldığın Yerden Devam Et:** Kullanıcının hangi dakikada kaldığını tarayıcıda veya hesapta saklar.
* **İzleme Listesi (Watchlist):** Beğenilen veya sonra izlenmek istenen yapımları listeye ekleme/çıkarma.
* **Özel Koleksiyon Listeleri (Letterboxd Tarzı):** Kullanıcıların kendi tematik listelerini (örn: *"En İyi 90'lar Bilimkurguları"*) oluşturup herkese açık paylaşabilmesi.
* **İçerik Talep Sistemi:** Sitede bulunmayan film/diziler için kullanıcı istek formu ve durum takibi.
* **Kullanıcı Tercihleri:** Varsayılan video kalitesi (1080p, 720p), dil ve otomatik sonraki bölüme geçme ayarları.

### ⚙️ Gelişmiş Yönetim Paneli (Admin Suite)
* **TMDB API Otomasyonu:**
  * TMDB ID veya başlık ile film ve dizileri anında arama
  * Tek tıkla afiş, konu, fragman, oyuncular ve tüm sezon/bölümleri otomatik içeri aktarma
* **Toplu İçe Aktarma (Bulk Importer):** Popüler, vizyondaki veya en çok oy alan filmleri tek seferde toplu aktarma.
* **Canlı Spor & TV Kanalları:** Maç yayınları, ligler, canlı skor durumu ve m3u8/HLS yayın linkleri ekleme.
* **Reklam & Monetizasyon Yönetimi:** Header, içerik listesi arası ve yapışkan alt reklam alanları yönetimi.
* **Gelişmiş AdBlock Algılayıcı:** Reklam engelleyici kullanan ziyaretçilere özel uyarı ve katı mod seçenekleri.
* **Hata Bildirimleri Moderasyonu:** Kullanıcıların bildirdiği çalışmayan linkleri inceleme, onarma veya silme.
* **Güvenlik & Denetim Günlükleri (Audit Logs):** Yönetici hareketleri ve sistem aktivitelerini kayıt altına alma.
* **Duyuru & Bildirim Sistemi:** Tüm kullanıcılara anlık duyuru veya site üstü bilgilendirme bandı yayınlama.

---

## 🛠️ Teknoloji Mimarisi

| Katman | Teknoloji | Açıklama |
|---|---|---|
| **Backend** | [Laravel 11 / 12](https://laravel.com) (PHP 8.3+) | Modern MVC mimarisi, REST API, Eloquent ORM |
| **Frontend** | [Vue 3](https://vuejs.org) + [Inertia.js v2](https://inertiajs.com) | Composition API, `<script setup>`, Reactive UI |
| **Stil & Tasarım** | [Tailwind CSS v4](https://tailwindcss.com) | Modern karanlık tema (Netflix / Prime Video tarzı) |
| **Veritabanı** | SQLite / MySQL / PostgreSQL | Esnek veritabanı desteği (varsayılan SQLite hazır) |
| **İçerik API** | [The Movie Database (TMDB)](https://www.themoviedb.org) | Film, dizi, oyuncu ve görsel metadata sağlayıcısı |
| **Gerçek Zamanlı** | Laravel Reverb / Pusher | Canlı bildirimler ve yayın durumu güncellemeleri |

---

## 🔑 Hazır Kullanıcı Giriş Bilgileri

Projeyi `php artisan migrate --seed` ile başlattığınızda aşağıdaki hazır hesaplar otomatik olarak oluşturulur:

### 👑 Yönetici (Admin) Hesabı
* **Giriş Adresi:** `/login` veya `/admin`
* **E-posta:** `admin@movie.com`
* **Şifre:** `password123`
* **Rol:** `admin` *(Yönetim paneline tam yetkili erişim sağlar)*

### 👤 Demo Standart Kullanıcı
* **Giriş Adresi:** `/login`
* **E-posta:** `demo-user@movie.app`
* **Şifre:** `demo123`
* **Rol:** `user` *(İzleme listesi, istekler ve profil testleri için)*

---

## 🚀 Kurulum Adımları (Yerel / Localhost)

### 1. Depoyu Klonlayın
```bash
git clone https://github.com/erogluerdem/movie.git
cd movie
```

### 2. Bağımlılıkları Yükleyin
```bash
# PHP bağımlılıkları
composer install

# Node.js bağımlılıkları
npm install
```

### 3. Ortam Dosyasını Yapılandırın
```bash
cp .env.example .env
php artisan key:generate
```

`.env` dosyanızı açarak temel ayarları kontrol edin:
```env
APP_NAME="Movie®"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# MySQL kullanmak isterseniz:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=movie_db
# DB_USERNAME=root
# DB_PASSWORD=

# TMDB API Anahtarlarınız (İsteğe bağlı - admin panelinden de girilebilir):
TMDB_API_KEY=your_api_key_here
TMDB_READ_TOKEN=your_read_token_here
```

### 4. Veritabanını Oluşturun ve Örnek Verileri Yükleyin
```bash
# SQLite kullanıyorsanız dosyasını oluşturun (mevcut değilse):
touch database/database.sqlite

# Tabloları oluşturup hazır içerikleri (180+ film/dizi/bölüm) aktarın:
php artisan migrate --seed
```

### 5. Depolama Sembolik Bağını Oluşturun
```bash
php artisan storage:link
```

### 6. Geliştirme Sunucularını Başlatın
```bash
# Terminal 1: Vite Frontend Sunucusu
npm run dev

# Terminal 2: Laravel Backend Sunucusu
php artisan serve
```

Tarayıcınızdan **`http://localhost:8000`** adresine giderek platformu kullanmaya başlayabilirsiniz!

---

## 🌐 Canlı Sunucu & cPanel Dağıtım Rehberi

Shared hosting (cPanel/Plesk) ortamına yüklerken güvenlik ve doğru çalışma için şu mimariyi izleyin:

1. **Dosyaları Konumlandırma:**
   * Laravel'in ana dosyalarını (`app`, `bootstrap`, `config`, `database`, `storage`, `vendor`, `.env` vb.) `public_html` dışına, örneğin `/home/kullanici/laravel/` dizinine taşıyın.
   * `public/` klasörünün içindeki tüm dosyaları (`index.php`, `.htaccess`, `build/`, `images/`, `manifest.json` vb.) doğrudan `public_html/` içerisine taşıyın.

2. **`public_html/index.php` Düzenlemesi:**
   ```php
   require __DIR__.'/../laravel/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel/bootstrap/app.php';
   ```

3. **Klasör İzinleri:**
   ```bash
   chmod -R 755 /home/kullanici/laravel
   chmod -R 775 /home/kullanici/laravel/storage /home/kullanici/laravel/bootstrap/cache
   chmod -R 775 /home/kullanici/laravel/database
   ```

4. **PHP Sürümü:**
   * Hosting kontrol panelinizden (cPanel MultiPHP) PHP sürümünü en az **PHP 8.3** olarak ayarlayın.

---

## 📁 Klasör Yapısı

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Web & Admin Controller sınıfları
│   │   └── Middleware/         # Inertia, Admin, Locale middleware'leri
│   ├── Models/                 # Movie, TvShow, Episode, SportMatch, User vb.
│   └── Services/               # TMDB Entegrasyon & Harici Servisler
├── config/                     # Laravel konfigürasyon dosyaları
├── database/
│   ├── migrations/             # Veritabanı tablo şemaları
│   └── seeders/                # Hazır örnek film/dizi verileri (seed_data.json)
├── public/                     # Web kök dizini (index.php, CSS, JS, Medya)
├── resources/
│   ├── css/                    # Tailwind CSS v4 stilleri
│   ├── js/                     # Vue 3 bileşenleri ve Inertia sayfaları
│   │   ├── Components/         # Navbar, Player, Card, Modal vb.
│   │   └── Pages/              # Home, Movies, TvShows, Admin, Dashboard
│   └── views/                  # Ana Blade şablonu (app.blade.php)
├── routes/
│   ├── web.php                 # Tüm web ve admin rotaları
│   └── console.php             # Zamanlanmış komutlar
└── vite.config.js              # Vite & Vue derleme yapılandırması
```

---

## 📄 Lisans

Bu proje **[MIT Lisansı](LICENSE)** kapsamında lisanslanmıştır. Ticari veya kişisel amaçlarla özgürce kullanılabilir, geliştirilebilir ve dağıtılabilir.
