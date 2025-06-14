# Instalasi Writing Agent Plugin - Production Guide

## 📦 File Yang Anda Butuhkan
- `writing-agent-plugin.zip` (40KB) - File plugin siap install

## 🚀 Cara Instalasi di cPanel/CyberPanel

### Metode 1: Upload via WordPress Admin (RECOMMENDED)

1. **Login ke WordPress Admin**
   ```
   Login ke: yourdomain.com/wp-admin
   Username: [admin_username]
   Password: [admin_password]
   ```

2. **Navigate ke Plugins**
   - Klik `Plugins` di sidebar kiri
   - Klik `Add New`
   - Klik `Upload Plugin`

3. **Upload Plugin**
   - Klik `Choose file`
   - Pilih file `writing-agent-plugin.zip`
   - Klik `Install Now`
   - Tunggu hingga proses selesai
   - Klik `Activate Plugin`

### Metode 2: Upload via cPanel File Manager

1. **Login ke cPanel**
   ```
   Login ke: yourdomain.com/cpanel
   ```

2. **Buka File Manager**
   - Klik `File Manager`
   - Navigate ke: `public_html/wp-content/plugins/`

3. **Upload dan Extract**
   - Klik `Upload`
   - Upload file `writing-agent-plugin.zip`
   - Klik kanan pada file ZIP
   - Pilih `Extract`
   - Hapus file ZIP setelah extract

4. **Aktivasi**
   - Login ke WordPress Admin
   - Pergi ke `Plugins`
   - Cari "Writing Agent" dan klik `Activate`

### Metode 3: Upload via FTP/SFTP

1. **Connect via FTP Client**
   ```
   Host: ftp.yourdomain.com
   Username: [ftp_username]
   Password: [ftp_password]
   Port: 21 (FTP) atau 22 (SFTP)
   ```

2. **Navigate dan Upload**
   ```
   Navigate ke: /public_html/wp-content/plugins/
   Extract auto-nulis-plugin.zip di komputer
   Upload folder 'auto-nulis' beserta isinya
   ```

3. **Aktivasi**
   - Login ke WordPress Admin
   - Pergi ke `Plugins`
   - Aktivasi "Auto Nulis"

## ⚙️ Konfigurasi Setelah Instalasi

### 1. Dapatkan API Keys

#### Google AI (Gemini) - GRATIS
```
1. Kunjungi: https://makersuite.google.com/app/apikey
2. Login dengan Google Account
3. Klik "Create API Key"
4. Copy API key yang dihasilkan
```

#### OpenAI - BERBAYAR
```
1. Kunjungi: https://platform.openai.com/api-keys
2. Login atau buat account
3. Klik "Create new secret key"
4. Copy API key yang dihasilkan
```

### 2. Konfigurasi Plugin

1. **Akses Plugin Settings**
   ```
   WordPress Admin → Auto Nulis → Settings
   ```

2. **Konfigurasi Dasar**
   ```
   ✅ Enable Auto Article Generation: ON
   🔑 API Key: [paste_your_api_key]
   🤖 AI Provider: Google AI (Gemini) [RECOMMENDED]
   📡 Test Connection: Klik untuk test
   ```

3. **Pengaturan Artikel**
   ```
   📊 Articles Per Day: 1 (mulai dari sedikit)
   ⏰ Schedule Time: 09:00
   📏 Article Length: Medium (500-800 words)
   📝 Post Status: Draft (untuk review manual)
   ```

4. **Keywords Setup**
   ```
   Tambahkan keywords (satu per baris):
   
   cara membuat website
   tips blogging pemula
   panduan SEO WordPress
   review hosting Indonesia
   tutorial digital marketing
   ```

5. **WordPress Settings**
   ```
   📁 Category: Pilih kategori yang sesuai
   👤 Author: Pilih author
   🖼️ Include Images: ✅ (jika ingin gambar otomatis)
   ```

### 3. Test Generation

1. **Manual Test**
   - Klik `Generate Article Now`
   - Tunggu 30-60 detik
   - Cek di Posts → All Posts

2. **Review Results**
   - Check artikel di Draft
   - Edit jika perlu
   - Publish jika sudah OK

## 🔧 Konfigurasi Advanced (Optional)

### API Keys di wp-config.php (Lebih Aman)

Tambahkan di file `wp-config.php`:
```php
// Auto Nulis API Keys
define('AUTO_NULIS_GEMINI_API_KEY', 'your_actual_api_key_here');
define('AUTO_NULIS_OPENAI_API_KEY', 'your_openai_key_here');

// Image APIs (optional)
define('AUTO_NULIS_UNSPLASH_KEY', 'your_unsplash_key');
define('AUTO_NULIS_PEXELS_KEY', 'your_pexels_key');
```

### Cron Job Monitoring

Check WordPress Cron:
```
WordPress Admin → Tools → Site Health → Info → WordPress Constants
Cari: WP_CRON
```

## 🚨 Troubleshooting

### Plugin Tidak Dapat Diaktifkan
```
❌ Masalah: Fatal error saat aktivasi
✅ Solusi:
   - Pastikan WordPress versi 5.0+
   - Pastikan PHP versi 7.4+
   - Check PHP error logs di cPanel
   - Deactivate plugins lain sementara
```

### API Connection Failed
```
❌ Masalah: Test API gagal
✅ Solusi:
   - Periksa API key yang benar
   - Pastikan ada saldo/quota API
   - Check firewall hosting
   - Coba provider AI yang berbeda
```

### Tidak Ada Artikel Generated
```
❌ Masalah: Plugin aktif tapi tidak generate
✅ Solusi:
   - Enable plugin di settings
   - Pastikan ada keywords
   - Check WordPress cron: WP Crontrol plugin
   - Monitor di Auto Nulis → Logs
```

### Artikel Berkualitas Rendah
```
❌ Masalah: Artikel tidak bagus
✅ Solusi:
   - Gunakan keywords yang lebih spesifik
   - Ganti ke Gemini Pro atau GPT-4
   - Set artikel ke "Long" length
   - Edit prompt via filter hooks
```

## 📊 Monitoring

### Check Generated Articles
```
WordPress Admin → Auto Nulis → Generated Articles
```

### Check Activity Logs
```
WordPress Admin → Auto Nulis → Logs
Filter by: Error, Warning, Success
```

### Performance Stats
```
Auto Nulis → Settings (sidebar)
- Keywords Ready: [count]
- Total Posts: [count]
- Daily Target: [count]
- Next Scheduled Run: [time]
```

## 🔒 Keamanan & Backup

### Backup Recommendations
```
✅ Backup database sebelum install
✅ Backup files setiap minggu
✅ Test restore procedure
✅ Monitor generated content
```

### Security Settings
```
✅ API keys di wp-config.php (bukan database)
✅ Review generated articles sebelum publish
✅ Set post status ke "Draft" untuk review
✅ Monitor plugin logs secara berkala
```

## 📞 Support

### Self-Check List
- [ ] WordPress 5.0+ ✅
- [ ] PHP 7.4+ ✅  
- [ ] Valid API key ✅
- [ ] Keywords configured ✅
- [ ] Plugin enabled ✅
- [ ] Test connection successful ✅

### File Logs
```
WordPress Debug: /wp-content/debug.log
Plugin Logs: Auto Nulis → Logs
cPanel Error Logs: Error Logs section
```

---

## 🎯 Quick Start Checklist

- [ ] 1. Upload dan aktivasi plugin
- [ ] 2. Dapatkan Google AI API key (gratis)
- [ ] 3. Test API connection
- [ ] 4. Tambahkan 5-10 keywords
- [ ] 5. Set ke Draft mode
- [ ] 6. Generate 1 artikel manual test
- [ ] 7. Review dan edit artikel
- [ ] 8. Enable auto-generation
- [ ] 9. Monitor logs harian

**Estimasi waktu setup: 15-30 menit**  
**Biaya operasional: GRATIS (dengan Gemini API)**

---

✅ **Plugin siap digunakan untuk production!**
