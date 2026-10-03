<div align="center">
  
  # 🎬 Movie® - Advanced Streaming & VOD Platform
  
  **A fully-featured, modern, and high-performance movie/TV show streaming platform built with Laravel 11 and Vue 3.**

  <p align="center">
    <a href="#english-version-gb"><strong>🇬🇧 English</strong></a> ·
    <a href="#türkçe-sürüm-tr"><strong>🇹🇷 Türkçe</strong></a>
  </p>

  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vue.js&logoColor=4FC08D" alt="Vue.js">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/Inertia.js-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
    <img src="https://img.shields.io/badge/TMDB_API-01B4E4?style=for-the-badge&logo=themoviedatabase&logoColor=white" alt="TMDB">
  </p>
</div>

---

## <a id="english-version-gb"></a> 🇬🇧 English Version

Movie® is a comprehensive VOD (Video on Demand) and streaming CMS. It offers an incredible user experience with a Netflix-style dark theme, real-time search, and seamless TMDB (The Movie Database) integration for automatic content fetching.

### ✨ Key Features

#### 👤 User & Frontend Features
* **Continue Watching:** Automatically saves the user's progress and watch history to resume seamlessly across devices.
* **Watchlist & Custom Collections:** Users can add movies/shows to their personal watchlist or create public curated lists (Letterboxd-style).
* **Content Request System:** Users can request movies or TV shows that are not currently available on the platform.
* **Mega Search & Live Autocomplete:** Instant, debounced search modal with category filtering and smart suggestions.
* **Advanced Player Support:** Supports multiple stream embeds and HLS/m3u8 live sports feeds.
* **AdBlock Detection:** Detects adblockers and prompts users to disable them (Strict mode available).

#### ⚙️ Admin Suite & Automation
* **TMDB API Automation:** Fetch posters, backdrops, plot summaries, cast, and trailers instantly by TMDB ID or title.
* **Bulk Importer:** Import popular, trending, or top-rated movies/shows in bulk with a single click.
* **TV Show & Episode Management:** Automated fetching of all seasons and episodes for TV series.
* **Monetization & Ads Management:** Easily manage banner ads, in-feed ads, and sticky footer ads from the dashboard.
* **Security & Audit Logs:** Tracks all administrator actions for security and transparency.
* **Broadcast Announcements:** Push site-wide alerts and announcements instantly to all visitors.
* **Link Moderation:** Users can report dead stream links; admins can review and update them via the dashboard.

### 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 11/12 (PHP 8.3+) |
| **Frontend** | Vue 3 (Composition API) + Inertia.js v2 |
| **Styling** | Tailwind CSS v4 (Modern Dark UI) |
| **Database** | SQLite / MySQL / PostgreSQL |
| **Data Provider** | The Movie Database (TMDB) API |

### 🚀 Installation (Local Development)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/erogluerdem/movie.git
   cd movie
   ```
2. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```
3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` and configure your Database connection and TMDB API keys.*
4. **Database & Seeding:**
   ```bash
   touch database/database.sqlite # If using SQLite
   php artisan migrate --seed
   ```
5. **Storage Link:**
   ```bash
   php artisan storage:link
   ```
6. **Start Servers:**
   ```bash
   npm run dev
   php artisan serve
   ```
   *Visit `http://localhost:8000` in your browser.*

### 🔑 Default Credentials (After Seeding)

| Role | Email | Password |
|---|---|---|
| **Super Admin** | `dev@erdemeroglu.com.tr` | `password123` |
| **Demo Admin** | `admin@movie.com` | `password123` |
| **Standard User**| `demo-user@movie.app` | `demo123` |

### 🌐 Shared Hosting (cPanel) Deployment Guide
1. Place Laravel core files (`app`, `bootstrap`, `vendor`, etc.) outside the `public_html` directory (e.g., `/home/user/laravel/`).
2. Move the contents of the `public/` directory directly into `public_html/`.
3. Edit `public_html/index.php` to point to the new paths:
   ```php
   require __DIR__.'/../laravel/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel/bootstrap/app.php';
   ```
4. Ensure PHP 8.3+ is enabled and file permissions are set correctly (`755` for directories, `644` for files).

---
---

## <a id="türkçe-sürüm-tr"></a> 🇹🇷 Türkçe Sürüm

Movie®, Laravel 11 ve Vue 3 kullanılarak geliştirilmiş, yüksek performanslı, modern bir film, dizi ve canlı yayın izleme platformudur. Netflix tarzı karanlık teması, gelişmiş TMDB otomasyonu ve SPA (Single Page Application) mimarisi ile kusursuz bir kullanıcı deneyimi sunar.

### ✨ Temel Özellikler

#### 👤 Kullanıcı Paneli (Dashboard)
* **Kaldığın Yerden Devam Et (Watch Progress):** Kullanıcının hangi yapımı yüzde kaç oranında izlediğini hatırlar ve kaldığı yerden devam etmesini sağlar.
* **İzleme Listesi (Watchlist):** Beğenilen veya sonra izlenmek istenen yapımları listeye ekleme.
* **Özel Koleksiyon Listeleri (Letterboxd Tarzı):** Kullanıcıların kendi tematik listelerini oluşturup herkese açık paylaşabilmesi.
* **İçerik Talep Sistemi:** Sitede bulunmayan film/diziler için istek formu.
* **Gelişmiş Arama (Mega Search):** Anlık çalışan (debounce destekli) kategori ve filtre tabanlı gelişmiş arama sistemi.
* **Kullanıcı Tercihleri:** Varsayılan video kalitesi, dil seçimi ve otomatik sonraki bölüme geçme ayarları.

#### ⚙️ Gelişmiş Yönetim Paneli (Admin Suite)
* **TMDB API Otomasyonu:** TMDB ID veya başlık ile film ve dizileri anında arama. Afiş, konu, fragman, oyuncular ve tüm sezon/bölümleri tek tıkla içeri aktarma.
* **Toplu İçe Aktarma (Bulk Importer):** Popüler, vizyondaki veya en çok oy alan içerikleri tek seferde toplu aktarma.
* **Canlı Spor & TV Kanalları:** Maç yayınları, ligler ve m3u8/HLS yayın linkleri ekleme.
* **Reklam & Monetizasyon Yönetimi:** Header, in-feed (içerik listesi arası) ve sticky (yapışkan) reklam alanlarını yönetme.
* **Gelişmiş AdBlock Algılayıcı:** Reklam engelleyici kullanan ziyaretçilere özel uyarı ve erişimi engelleme (katı mod) seçenekleri.
* **Hata Bildirimleri Moderasyonu:** Kırık veya çalışmayan yayın linkleri için kullanıcı raporlarını inceleme ve onarma.
* **Güvenlik & Denetim Günlükleri (Audit Logs):** Yönetici hareketlerini kayıt altına alma.
* **Duyuru & Bildirim Sistemi:** Tüm kullanıcılara anlık duyuru bandı yayınlama.

### 🛠️ Teknoloji Mimarisi

| Katman | Teknoloji | Açıklama |
|---|---|---|
| **Backend** | Laravel 11/12 (PHP 8.3+) | Modern MVC mimarisi, REST API, Eloquent ORM |
| **Frontend** | Vue 3 + Inertia.js v2 | Composition API, Reactive UI, SPA Deneyimi |
| **Stil & Tasarım** | Tailwind CSS v4 | Modern karanlık tema (Netflix / Prime Video tarzı) |
| **Veritabanı** | SQLite / MySQL / PostgreSQL | Esnek veritabanı desteği |
| **İçerik API** | TMDB (The Movie Database) | Film, dizi, oyuncu metadata sağlayıcısı |

### 🚀 Kurulum Adımları (Yerel / Localhost)

1. **Depoyu Klonlayın:**
   ```bash
   git clone https://github.com/erogluerdem/movie.git
   cd movie
   ```
2. **Bağımlılıkları Yükleyin:**
   ```bash
   composer install
   npm install
   ```
3. **Ortam Dosyasını Yapılandırın:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *`.env` dosyanızı açarak veritabanı ve TMDB API ayarlarınızı yapılandırın.*
4. **Veritabanını Oluşturun ve Örnek Verileri Yükleyin:**
   ```bash
   touch database/database.sqlite # SQLite için
   php artisan migrate --seed
   ```
5. **Depolama Sembolik Bağını Oluşturun:**
   ```bash
   php artisan storage:link
   ```
6. **Geliştirme Sunucularını Başlatın:**
   ```bash
   # Terminal 1
   npm run dev
   # Terminal 2
   php artisan serve
   ```
   *Tarayıcınızdan `http://localhost:8000` adresine gidebilirsiniz.*

### 🔑 Hazır Kullanıcı Giriş Bilgileri (Seeder Sonrası)

| Rol | E-Posta | Şifre | Yetki |
|---|---|---|---|
| **Kurucu Admin** | `dev@erdemeroglu.com.tr` | `password123` | Tam Yetkili |
| **Demo Admin** | `admin@movie.com` | `password123` | Yönetim Paneli |
| **Standart Kullanıcı**| `demo-user@movie.app` | `demo123` | Sadece Site İçi |

### 🌐 Canlı Sunucu (cPanel / Plesk) Dağıtım Rehberi
1. Laravel'in ana dosyalarını (`app`, `bootstrap`, `vendor`, vb.) `public_html` **dışına**, örneğin `/home/kullanici/laravel/` dizinine taşıyın.
2. `public/` klasörünün içindeki tüm dosyaları doğrudan `public_html/` içerisine taşıyın.
3. `public_html/index.php` dosyasını şu şekilde güncelleyin:
   ```php
   require __DIR__.'/../laravel/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel/bootstrap/app.php';
   ```
4. Hosting panelinden **PHP 8.3** veya üstünün seçili olduğundan emin olun. Gerekli klasör izinlerini (storage ve bootstrap/cache için `775`) ayarlayın.

---

### 👨‍💻 Geliştirici & İletişim
* **Geliştirici:** Erdem Eroğlu
* **Kişisel Web Sitesi:** [erdemeroglu.com.tr](https://erdemeroglu.com.tr)
* **İletişim & Destek:** dev@erdemeroglu.com.tr
* **GitHub:** [@erogluerdem](https://github.com/erogluerdem)

### 📄 Lisans
Bu proje **[MIT Lisansı](LICENSE)** kapsamında lisanslanmıştır. Açık kaynaklıdır, ticari veya kişisel amaçlarla özgürce kullanılabilir ve geliştirilebilir.
