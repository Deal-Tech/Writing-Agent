# Image API Troubleshooting Checklist

## Langkah-langkah untuk mengatasi masalah gambar tidak muncul:

### 1. Verifikasi Konfigurasi Dasar
- [ ] Pastikan "Include Images in Articles" dicentang di Settings
- [ ] Pilih Image Source (Unsplash atau Pexels)
- [ ] Masukkan API Key yang valid
- [ ] Test API connection dengan tombol "Test API"

### 2. Periksa API Keys
**Unsplash API:**
- Daftar di: https://unsplash.com/developers
- Format API key: 32 karakter hexadecimal
- Rate limit: 50 requests/hour (gratis)

**Pexels API:**
- Daftar di: https://www.pexels.com/api/
- Format API key: 40 karakter alphanumeric
- Rate limit: 200 requests/hour, 20,000/month (gratis)

### 3. Debugging Steps

#### A. Cek Settings di Database
Jalankan query ini di phpMyAdmin atau WordPress database:
```sql
SELECT option_value FROM wp_options WHERE option_name = 'auto_nulis_settings';
```

#### B. Cek WordPress Error Log
Lokasi biasanya: `/wp-content/debug.log`
Cari error yang berkaitan dengan "Auto_Nulis_Image"

#### C. Test Manual di WordPress Admin
1. Buka WordPress Admin
2. Pergi ke Auto Nulis → Settings
3. Scroll ke Image Settings
4. Pastikan semua field terisi
5. Klik "Test API" untuk memverifikasi

### 4. Common Issues & Solutions

#### Issue: API Key Invalid
**Solution:** 
- Copy-paste ulang API key dari provider
- Pastikan tidak ada spasi di awal/akhir
- Verifikasi API key masih aktif di dashboard provider

#### Issue: Rate Limit Exceeded
**Solution:**
- Tunggu sampai rate limit reset
- Upgrade ke paid plan jika perlu
- Switch ke provider lain sementara

#### Issue: WordPress Can't Download Images
**Solution:**
- Pastikan WordPress punya permission write ke /wp-content/uploads/
- Cek firewall/security plugin yang mungkin block download
- Test dengan image source "Media Library" sebagai fallback

#### Issue: Images Downloaded but Not Set as Featured
**Solution:**
- Cek theme support featured images
- Pastikan tidak ada plugin conflict
- Test dengan default WordPress theme

### 5. Verification Steps

#### Test Article Generation:
1. Buka Auto Nulis → Settings
2. Pastikan plugin enabled
3. Set keywords (1 per baris)
4. Set "Articles Per Day" = 1
5. Klik "Generate Article Now"
6. Cek post yang dibuat apakah ada featured image

#### Check Logs:
1. Buka Auto Nulis → Logs
2. Filter by date (hari ini)
3. Cari log entry tentang image
4. Lihat error messages jika ada

### 6. Manual Test Code

Tambahkan code ini ke functions.php theme untuk test manual:
```php
function test_auto_nulis_image() {
    if (class_exists('Auto_Nulis_Image')) {
        $image_handler = new Auto_Nulis_Image();
        $result = $image_handler->get_relevant_image('technology', 'unsplash');
        error_log('Test Image Result: ' . print_r($result, true));
    }
}
// Uncomment line di bawah untuk test
// add_action('init', 'test_auto_nulis_image');
```

### 7. Alternative Solutions

Jika API external bermasalah, gunakan fallback:
1. Set Image Source ke "WordPress Media Library"
2. Upload beberapa gambar ke Media Library
3. Plugin akan random pilih dari library yang ada

### 8. Contact Support

Jika masalah masih berlanjut, berikan informasi:
- WordPress version
- PHP version  
- Error messages dari log
- Screenshots konfigurasi
- Test API results
