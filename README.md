# ESolution Laravel Email (v1.1)

Package Laravel untuk broadcast email multi-provider (SendGrid-ready) dengan template, tracking event, **global suppression list**, dan optimasi deliverability.

## Install
```bash
composer require elgibor-solution/laravel-email
php artisan vendor:publish --tag=laravel-email-config
php artisan vendor:publish --tag=laravel-email-migrations
php artisan migrate
```
Tambahkan ENV:
```env
SENDGRID_API_KEY=SG_xxx
LAREMAIL_STRATEGY=round_robin
LAREMAIL_DEFAULT=sendgrid_1
LAREMAIL_RPM=600
```
Jalankan queue:
```bash
php artisan queue:work
```

## Fitur
- Multi-account provider (round-robin/fixed)
- Driver **SendGrid** (bisa ditambah driver baru via interface)
- Template dengan placeholder: `{{name}}`, `{{email}}`, `{{unsubscribe_url}}`, `{{tracking_pixel}}`
- Broadcast + throttling berdasarkan RPM
- Tracking event via webhook (open, bounce, unsubscribe, spamreport, dropped, ...)
- **Global suppression list** (unsubscribe/bounce/spam/manual)
- Auto `List-Unsubscribe` header + link unsubscribe per penerima

## Endpoint (prefix `/laravel-email`)
- `POST /templates` — buat template
- `GET /templates` — list template
- `POST /broadcasts` — buat broadcast
- `POST /broadcasts/{id}/recipients` — tambah penerima
- `POST /broadcasts/{id}/start` — mulai kirim (queue + throttle)
- `POST /webhook/sendgrid` — endpoint webhook
- `GET /t/{token}` — tracking pixel 1×1
- `GET /u/{token}` — unsubscribe
- `GET /suppressions` — list suppression
- `POST /suppressions` — tambah/update suppression
- `DELETE /suppressions/{id}` — hapus suppression

## Global Suppression List
- Tabel: `le_suppressions (email UNIQUE, reason ENUM)`
- Otomatis terisi dari webhook: `unsubscribe`, `bounce/dropped` → reason=bounce, `spamreport` → reason=spam
- Dicek sebelum pengiriman di `SendEmailJob` → email diskip jika ada di suppression list

## Tips Deliverability
- Setup SPF/DKIM, warm-up domain/IP, kirim ke segment engaged, gunakan custom tracking domain jika ada.
- Hindari spam trigger words, tambah plain-text version, dan pertahankan ratio teks/HTML yang sehat.

## Ekstensi Driver
Implement `ESolution\LaravelEmail\Contracts\MailDriver`, daftarkan di `config/laravel_email.php` → `providers`.

## License
MIT
