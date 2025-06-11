# Writing Agent - WordPress Plugin untuk Pembuatan Artikel Otomatis

Plugin WordPress yang menggunakan teknologi AI (Google Gemini & OpenAI) untuk membuat artikel berkualitas tinggi secara otomatis dengan fokus pada SEO dan konten yang human-like.

## 🚀 Fitur Utama

### ✨ Pembuatan Artikel Otomatis
- **AI-Powered**: Menggunakan Google Gemini atau OpenAI untuk menghasilkan konten berkualitas
- **SEO-Optimized**: Artikel dioptimalkan untuk mesin pencari dengan penempatan kata kunci yang natural
- **Human-like**: Konten terasa natural dan tidak terdeteksi sebagai buatan AI
- **Multiple Lengths**: Dukungan artikel pendek (300-500 kata), sedang (500-800 kata), dan panjang (800-1200+ kata)

### 🎛️ Panel Kontrol Lengkap
- **Dashboard Intuitif**: Interface admin yang bersih dan mudah digunakan
- **Penjadwalan Fleksibel**: Atur frekuensi dan waktu pembuatan artikel
- **Manajemen Kata Kunci**: Input mudah dengan dukungan multiple keywords
- **Status Kontrol**: Pilih status publikasi (Published/Draft/Pending)

### 🖼️ Manajemen Gambar Otomatis
- **Multiple Sources**: Dukungan Unsplash, Pexels, dan WordPress Media Library
- **Auto Attribution**: Atribusi gambar otomatis sesuai sumber
- **SEO-Friendly Alt Text**: Alt text yang deskriptif untuk SEO

### 📊 Monitoring & Analytics
- **Activity Logs**: Log lengkap aktivitas plugin
- **Statistics Dashboard**: Statistik artikel yang dihasilkan
- **Generated Articles Manager**: Kelola artikel yang sudah dibuat

## 📋 Persyaratan

- WordPress 5.0 atau lebih baru
- PHP 7.4 atau lebih baru
- MySQL 5.6 atau lebih baru
- API Key dari Google AI (Gemini) atau OpenAI
- Koneksi internet stabil

## 🔧 Instalasi

### Metode 1: Upload Manual

1. **Download Plugin**
   ```
   Download folder plugin 'auto-nulis' lengkap
   ```

2. **Upload ke WordPress**
   - Kompres folder `auto-nulis` menjadi file ZIP
   - Login ke WordPress Admin
   - Pergi ke `Plugins > Add New > Upload Plugin`
   - Upload file ZIP dan aktifkan

3. **Atau Upload via FTP**
   - Upload folder `auto-nulis` ke `/wp-content/plugins/`
   - Login ke WordPress Admin
   - Pergi ke `Plugins` dan aktifkan "Writing Agent"

### Metode 2: FTP Direct

1. **Upload via FTP**
   ```
   Upload folder auto-nulis ke:
   /wp-content/plugins/auto-nulis/
   ```

2. **Set Permissions**
   ```
   chmod 755 /wp-content/plugins/auto-nulis/
   chmod 644 /wp-content/plugins/auto-nulis/*.php
   ```

3. **Aktifkan Plugin**
   - Login ke WordPress Admin
   - Pergi ke `Plugins` dan aktifkan "Writing Agent"

## ⚙️ Konfigurasi

### 1. Dapatkan API Key

#### Google AI (Gemini)
1. Kunjungi [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Buat API key baru
3. Salin API key untuk digunakan di plugin

#### OpenAI
1. Kunjungi [OpenAI Platform](https://platform.openai.com/api-keys)
2. Buat API key baru
3. Salin API key untuk digunakan di plugin

### 2. Konfigurasi Plugin

1. **Akses Settings**
   - Pergi ke `Writing Agent > Settings` di WordPress Admin

2. **Konfigurasi Dasar**
   - ✅ Enable plugin
   - 📝 Masukkan API Key
   - 🔧 Pilih AI Provider (Gemini/OpenAI)
   - 🎯 Test koneksi API

3. **Pengaturan Artikel**
   ```
   📊 Articles Per Day: 1-10
   ⏰ Schedule Time: 09:00 (contoh)
   📝 Keywords: (satu per baris)
        teknologi AI
        digital marketing
        web development
   📏 Article Length: Medium (500-800 words)
   📊 Post Status: Draft
   ```

4. **Pengaturan Gambar**
   ```
   🖼️ Include Images: ✅ Enable
   🌐 Image Source: Unsplash API
   ```

5. **Pengaturan WordPress**
   ```
   📁 Category: Pilih kategori default
   👤 Author: Pilih penulis artikel
   ```

### 3. Setup API Keys Gambar (Opsional)

#### Untuk Unsplash
1. Daftar di [Unsplash Developers](https://unsplash.com/developers)
2. Tambahkan ke `wp-config.php`:
   ```php
   define('AUTO_NULIS_UNSPLASH_KEY', 'your_unsplash_access_key');
   ```

#### Untuk Pexels
1. Daftar di [Pexels API](https://www.pexels.com/api/)
2. Tambahkan ke `wp-config.php`:
   ```php
   define('AUTO_NULIS_PEXELS_KEY', 'your_pexels_api_key');
   ```

## 🚀 Penggunaan

### Mengaktifkan Auto-Generation

1. **Konfigurasi Lengkap**
   - Pastikan API key sudah diset
   - Test koneksi API berhasil
   - Keywords sudah dimasukkan

2. **Enable Plugin**
   - Toggle "Enable Auto Article Generation" ke ON
   - Save settings

3. **Manual Generation**
   - Klik "Generate Article Now" untuk test
   - Artikel akan muncul di Posts

### Monitoring

1. **Generated Articles**
   - Pergi ke `Writing Agent > Generated Articles`
   - Lihat semua artikel yang sudah dibuat
   - Edit atau publikasikan artikel

2. **Activity Logs**
   - Pergi ke `Writing Agent > Logs`
   - Monitor aktivitas plugin
   - Debug jika ada masalah

## 🎯 Tips Penggunaan

### Keywords yang Efektif
```
✅ BAIK:
cara membuat website
tips digital marketing
panduan SEO 2025
review produk teknologi

❌ HINDARI:
website
marketing
SEO
teknologi
```

### Best Practices

1. **Start Small**
   - Mulai dengan 1 artikel per hari
   - Gunakan status "Draft" terlebih dahulu
   - Review artikel sebelum publish

2. **Quality Keywords**
   - Gunakan long-tail keywords
   - Fokus pada topik spesifik
   - Hindari keyword terlalu umum

3. **Monitor Regularly**
   - Cek logs secara berkala
   - Review artikel yang dihasilkan
   - Adjust settings berdasarkan hasil

## 🔧 Troubleshooting

### Masalah Umum

#### 1. API Connection Failed
```
❌ Masalah: Test API gagal
✅ Solusi:
   - Periksa API key
   - Pastikan koneksi internet stabil
   - Cek quota API
```

#### 2. No Articles Generated
```
❌ Masalah: Tidak ada artikel dibuat
✅ Solusi:
   - Enable plugin
   - Pastikan keywords ada
   - Cek cron job WordPress
```

#### 3. Poor Article Quality
```
❌ Masalah: Kualitas artikel rendah
✅ Solusi:
   - Gunakan keywords yang lebih spesifik
   - Coba provider AI yang berbeda
   - Adjust article length
```

### Debug Mode

Untuk debugging, tambahkan ke `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Log akan tersimpan di `/wp-content/debug.log`

## 🔒 Keamanan

### API Key Protection
- API keys disimpan terenkripsi di database
- Tidak ada API key di frontend
- Validasi NONCE untuk semua action

### Content Safety
- Sanitasi semua input user
- Validasi API responses
- Escape output data

## 📈 Performance

### Optimisasi
- Gambar dioptimalkan otomatis
- Caching untuk API responses
- Efficient database queries
- Background processing untuk generation

### Monitoring
- Track API usage
- Monitor generation time
- Database performance logs

## 🆘 Support

### Self-Help
1. Cek logs di `Writing Agent > Logs`
2. Review settings konfigurasi
3. Test API connection
4. Periksa WordPress debug logs

### Documentation
- README.md (file ini)
- Inline code comments
- WordPress Codex compatibility

### Backup Recommendations
- Backup database sebelum instalasi
- Backup site secara berkala
- Export logs sebelum clear

## 📄 Lisensi

GPL v2 atau yang lebih baru - sama dengan WordPress core.

## 🔄 Updates

Plugin mendukung auto-updates melalui WordPress admin. Pastikan backup site sebelum update major version.

---

**Writing Agent v1.0.1** - Plugin WordPress untuk Pembuatan Artikel Otomatis dengan AI

Dikembangkan dengan ❤️ untuk komunitas WordPress Indonesia
