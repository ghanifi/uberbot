# Yeni Dil & Para Birimi Sistemi - Tam Implementasyon

## ✓ Tamamlanan İşler

### 1. Database Migrations
✓ `database/migrations/006-add-language-domains.php`
  - wp_language_domains table oluşturuluyor
  - wp_booking_analytics'e session_currency column ekleniyor
  - Default veri: en_US → airlinel.com

### 2. Helper Classes
✓ `includes/class-language-domains.php`
  - Language-domain mappings yönetimi
  - get_domain_by_language() - Dil kodundan domain bul
  - save_domain(), delete_domain() - CRUD işlemleri
  - get_language_options() - Dropdown için diller listesi

✓ `includes/class-currency-session.php`
  - Session-based currency management
  - ?currency=EUR query string desteği
  - window.airinelCurrency global variable
  - Airlinel_Currency_Session::get_currency() static method

### 3. Admin Interface
✓ `admin/pages/language-domains.php`
  - Language domains CRUD admin sayfası
  - WordPress admin menüsüne entegre
  - Add/Edit form ve domain listesi tablosu

### 4. Functions.php Updates
✓ class-language-domains.php require
✓ class-currency-session.php require
✓ AJAX handler: airlinel_ajax_get_language_domain()
✓ Admin menu: airlinel-language-domains submenu
✓ AJAX nonce: window.airinelNonce

### 5. JavaScript Updates

✓ `assets/js/header-selectors.js`
  - NEW: Domain lookup via AJAX (header-selectors.js → fetch → domain → redirect)
  - NEW: Currency via query string (?currency=EUR)
  - Removed: localStorage usage
  - Removed: Page reload for language
  - Console logging added

✓ `assets/js/booking.js`
  - NEW: Use window.airinelCurrency from session
  - REMOVED: localStorage.getItem('airlinel_currency')
  - REMOVED: Local currency dropdown change handler
  - REMOVED: currencyChanged event listener
  - Updated: updateCurrencyDisplay() uses session currency
  - Keep: Price formatting functions

### 6. Template Updates

✓ `page-booking.php`
  - Updated: selected-currency hidden field now uses session value
  - `Airlinel_Currency_Session::get_currency()`

## 📊 Sistem İş Akışı

### Dil Değişimi
```
User: Language dropdown'dan Türkçe seçer
  ↓
header-selectors.js: switchLanguage('tr_TR')
  ↓
AJAX POST: /wp-admin/admin-ajax.php
  action: 'airlinel_get_language_domain'
  language_code: 'tr_TR'
  nonce: window.airinelNonce
  ↓
Backend: wp_language_domains'den domain bul (havalimanitransfer.com)
  ↓
Response: { domain_url: 'havalimanitransfer.com' }
  ↓
JavaScript: window.location.href = 'https://havalimanitransfer.com/'
  ↓
User: havalimanitransfer.com'da karşılanıyor (tr_TR WordPress kurulumu)
```

### Para Birimi Değişimi
```
User: Currency dropdown'dan EUR seçer
  ↓
header-selectors.js: switchCurrency('EUR')
  ↓
JavaScript: window.location.href = '?currency=EUR'
  ↓
Backend: class-currency-session.php alıyor
  - $_GET['currency'] = 'EUR' kontrol edilir
  - $_SESSION['airlinel_currency'] = 'EUR'
  ↓
Page Load: window.airinelCurrency = 'EUR' (footer script)
  ↓
booking.js: selectedCurrency = window.airinelCurrency
  ↓
Prices: Tüm fiyatlar EUR cinsinden gösterilir
```

## 🔧 Database Schema

### wp_language_domains
```
id            | INT AUTO_INCREMENT PRIMARY KEY
language_code | VARCHAR(10) UNIQUE - tr_TR, de_DE, en_US
language_name | VARCHAR(50) - Türkçe, Deutsch, English
domain_url    | VARCHAR(255) - havalimanitransfer.com
flag          | CHAR(2) - TR, DE, EN
is_active     | TINYINT DEFAULT 1
display_order | INT DEFAULT 0
created_at    | DATETIME
updated_at    | DATETIME
```

### wp_booking_analytics
```
Yeni column:
session_currency | VARCHAR(3) DEFAULT 'GBP'
```

## 🚀 Kurulum Adımları

### Step 1: Migrasyonu Çalıştır
1. WordPress Admin → Airlinel → Theme Settings → Migrations
2. Migrasyonu 006-add-language-domains.php çalıştır
3. Tables oluşturulacak, en_US → airlinel.com default olarak eklenecek

### Step 2: Language Domains Ekle
1. WordPress Admin → Settings → Language Domains
2. Dil ekle:
   - Language Code: tr_TR
   - Language Name: Türkçe
   - Domain URL: havalimanitransfer.com
   - Flag: TR
   - Active: ✓

3. Benzer şekilde diğer dilleri ekle:
   - de_DE → flughafentransfer.com
   - es_ES → traslados-aeropuerto.com
   - vb.

### Step 3: Test Et
1. Ana sitede (airlinel.com) language dropdown'dan Türkçe seç
2. havalimanitransfer.com'ye yönlendirilecek
3. Para birimi dropdown'dan EUR seç
4. ?currency=EUR parametresiyle sayfada reload olacak
5. Browser console'u aç (F12) logging'i gözlemle

## 📋 Test Checklist

- [ ] Migration başarıyla çalıştı
- [ ] wp_language_domains table oluştu
- [ ] Admin panelde Language Domains sayfası var
- [ ] Language ekle/düzenle/sil işleri çalışıyor
- [ ] Language dropdown'dan seçim → domain'e redirect
- [ ] Currency dropdown'dan seçim → ?currency=EUR
- [ ] Sayfa reload olunca prices yeni currency'de gösteriliyor
- [ ] Console'da error yok
- [ ] Session currency booking form'da tutulmuş
- [ ] Analytics'te currency logu var

## ⚠️ Önemli Notlar

1. **Her dil ayrı WordPress:** Türkçe site ayrı WordPress kurulumu olmalı
2. **Domain kaydı:** DNS'de domain'leri pointlemek gerekebilir
3. **Session timeout:** Unlimited olarak ayarlandı, gerekirse düzeltilmeli
4. **Analytics:** Booking table'ında currency kolon var, logu yapılıyor
5. **API Integration:** API'ye currency parameter gönderilmesi gerekiyorsa,  booking form'daki session currency field'ı kullanılmalı

## 📁 Dosya Listesi

```
Created:
  - database/migrations/006-add-language-domains.php
  - includes/class-language-domains.php
  - includes/class-currency-session.php
  - admin/pages/language-domains.php

Modified:
  - functions.php (require + AJAX handlers + admin menu)
  - assets/js/header-selectors.js (yeni sistem)
  - assets/js/booking.js (session-based)
  - page-booking.php (session currency field)
```

## ✅ Hazır mı?

Migrasyonu çalıştırdıktan sonra Language Domains ekle ve test et!
