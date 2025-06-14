# Writing Agent - Scheduling Troubleshooting Guide

## Overview

Plugin sekarang sudah diperbaiki untuk menangani masalah scheduling dengan timezone WordPress yang benar. Berikut panduan untuk memastikan scheduling berfungsi dengan baik.

## ✅ Fitur Perbaikan

### 1. **Timezone WordPress Support**
- Schedule sekarang menggunakan timezone WordPress (`wp_timezone()`)
- Waktu ditampilkan sesuai dengan setting WordPress
- Konversi UTC dilakukan dengan benar untuk WordPress cron

### 2. **Enhanced Scheduler Class**
- Class `Auto_Nulis_Scheduler` baru untuk menangani scheduling
- Verifikasi dan perbaikan schedule otomatis
- Logging yang lebih detail untuk debugging

### 3. **Daily Limit Control**
- Cek jumlah artikel hari ini sebelum generate
- Respects `articles_per_day` setting
- Tidak akan generate lebih dari limit harian

### 4. **Better Status Display**
- Status scheduling yang lebih informatif di admin
- Menampilkan timezone, waktu berikutnya, progress harian
- Indikator visual untuk status enabled/disabled

## 🔧 Cara Menggunakan

### 1. **Setup Awal**
```
1. Buka WordPress Admin → Writing Agent → Settings
2. Pastikan "Enable Auto Article Generation" dicentang
3. Set "Schedule Time" (contoh: 09:00)
4. Set "Articles Per Day" (1-10)
5. Klik "Save Settings"
```

### 2. **Verifikasi Schedule**
Setelah save settings, check di sidebar "Scheduling Status":
- Status: Enabled ✅
- Next Run: [tanggal dan waktu] 
- Today's Progress: 0 / 1 (0 remaining)
- Interval: [interval waktu]
- Timezone: [timezone WordPress]

### 3. **Testing**
```
1. Gunakan scheduler-debug.php untuk testing:
   http://yoursite.com/wp-content/plugins/auto-nulis/scheduler-debug.php

2. Atau test manual generation:
   WordPress Admin → Writing Agent → Generate Now
```

## 🔍 Troubleshooting

### Issue 1: "Not scheduled" muncul terus
**Kemungkinan Penyebab:**
- Plugin disabled
- WordPress cron tidak berfungsi
- Timezone setting bermasalah

**Solusi:**
1. Check plugin enabled di settings
2. Test dengan scheduler-debug.php
3. Klik "Force Schedule" di debug tool
4. Contact hosting provider jika masih bermasalah

### Issue 2: Schedule muncul tapi tidak jalan
**Kemungkinan Penyebab:**
- DISABLE_WP_CRON = true di wp-config.php
- Hosting provider block cron
- Server overload

**Solusi:**
1. Check wp-config.php untuk:
   ```php
   define('DISABLE_WP_CRON', false); // atau hapus line ini
   ```
2. Install plugin "WP Crontrol" untuk monitoring
3. Contact hosting provider

### Issue 3: Timezone salah
**Kemungkinan Penyebab:**
- WordPress timezone setting salah

**Solusi:**
1. WordPress Admin → Settings → General
2. Set "Timezone" ke timezone yang benar
3. Save dan check ulang di Writing Agent

### Issue 4: Generate terlalu sering/jarang
**Kemungkinan Penyebab:**
- Articles per day setting tidak sesuai

**Solusi:**
1. Adjust "Articles Per Day" di settings
2. Save settings (akan recalculate interval)
3. Check di "Scheduling Status" untuk interval baru

## 🛠️ Debug Tools

### 1. **scheduler-debug.php**
File debug khusus untuk scheduler:
```
URL: http://yoursite.com/wp-content/plugins/auto-nulis/scheduler-debug.php
```

**Actions tersedia:**
- **Clear Schedule**: Hapus semua schedule
- **Force Schedule**: Paksa buat schedule baru
- **Test Generation**: Test generate artikel
- **Refresh**: Refresh status

### 2. **WordPress Debug Log**
Enable WordPress debug di wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Log location: `/wp-content/debug.log`

### 3. **Plugin Logs**
Check di WordPress Admin → Writing Agent → Activity Logs
Filter by level: Error, Warning, Info

## 📋 Checklist Untuk Admin

### Before Reporting Issues:
- [ ] Plugin enabled di settings ✅
- [ ] Schedule time diset ✅  
- [ ] Keywords ada dan valid ✅
- [ ] API key valid dan tested ✅
- [ ] WordPress timezone correct ✅
- [ ] Check scheduler-debug.php status ✅
- [ ] Check WordPress debug log ✅
- [ ] Check plugin activity logs ✅

### Common WordPress Settings:
```php
// wp-config.php recommended settings
define('WP_CRON', true);
// define('DISABLE_WP_CRON', false); // atau hapus

// Timezone
date_default_timezone_set('Asia/Jakarta'); // optional
```

## 📞 Support

Jika masih ada masalah setelah mengikuti guide ini:

1. **Kumpulkan info:**
   - Screenshot scheduler-debug.php
   - WordPress debug log (last 50 lines)
   - Plugin activity logs
   - WordPress timezone setting

2. **Common hosting issues:**
   - Beberapa shared hosting disable cron
   - CloudFlare bisa interfere dengan cron
   - Server timezone berbeda dengan WordPress

3. **Contact info:**
   - Sertakan info di atas
   - Jelaskan steps yang sudah dicoba
   - Include hosting provider info

---

**Last Updated:** June 2025  
**Plugin Version:** 1.0.1+  
**Timezone Support:** ✅ Full WordPress timezone support
