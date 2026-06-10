## Project Control Manager

Aplikasi ini dibangun dengan Laravel untuk memantau progress project, master flow, dan proses delivery project industrial.

## Fitur Dashboard

### 1. **Project Overview Dashboard** ✨
Dashboard utama menampilkan daftar semua project dengan fitur-fitur:

- **Auto-Scroll Otomatis** 🔄
  - Project list scroll otomatis dari kiri ke kanan
  - Scroll berhenti otomatis ketika mouse mendekati container
  - Resume scroll setelah mouse meninggalkan area
  - Support manual scroll dengan 3 detik delay sebelum auto-resume

- **Project Cards Interaktif** 
  - Clickable cards untuk navigasi ke detail project
  - Menampilkan WO Number, Project Name, Customer, Status
  - Visual progress bar per project
  - Status badges: AT RISK, ON TRACK, DELAY

- **Header Statistics**
  - Overall Progress percentage
  - Total Project count
  - Status breakdown (On Track, At Risk, Delay)
  - Last update timestamp

- **Statistics Visualization**
  - Progress Summary (Pie Chart)
  - Progress by Stage (Bar Chart)
  - Department metrics (Engineering, Procurement, Production, Testing)

### 2. **Project Detail Dashboard** 📊
Halaman detail untuk setiap project dengan informasi komprehensif:

- **Project Header** dengan informasi dasar dan status
- **Overall Progress** dengan visual progress bar besar
- **Breakdown by Department**
  - Engineering (Drawing Approval, CTP, BOM Release)
  - Procurement (Material Progress, SCC/PO Follow Up, Outstanding PO)
  - Production (Fabrication, Assembly, Wiring)
  - Testing & Delivery (Testing/FAT, Packing, Delivery)

- **Progress by Stage Chart** untuk tracking tahap produksi
- **Issues & Risk Management Table**

## Setup Lokal

1. Clone repository ini.
2. Install dependency:

```bash
composer install
npm install
```

3. Buat file environment:

```bash
cp .env.example .env
```

4. Generate app key:

```bash
php artisan key:generate
```

5. Pilih salah satu sumber database:

- Gunakan database contoh yang sudah ikut repo di `database/database.sqlite`
- Atau bangun ulang database dari migration dan seeder:

```bash
php artisan migrate:fresh --seed
```

6. Jalankan asset front-end:

```bash
npm run build
```

7. Jalankan aplikasi:

```bash
composer run dev
```

## Setup Cepat

Kalau ingin langsung menyiapkan project dari nol dengan data demo, cukup jalankan:

```bash
composer run setup
```

Perintah ini akan:
- install dependency PHP dan Node
- membuat `.env` bila belum ada
- membuat file SQLite bila belum ada
- generate app key
- rebuild database + seed akun demo
- build asset front-end

## Akun Login Default

- Admin: `admin@project-control.local` / `admin12345`
- User: `user@project-control.local` / `user12345`

## Routes

```php
GET  /dashboard                    # Project Overview
GET  /dashboard/projects           # Project Overview (alias)
GET  /dashboard/projects/{id}      # Project Detail
```

## Struktur File

```
project/
├── app/
│   └── Http/
│       └── Controllers/
│           └── DashboardController.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── dashboard/
│           ├── project-overview.blade.php
│           └── project-detail.blade.php
├── routes/
│   └── web.php
└── README.md
```

## Catatan Database

- File SQLite contoh tersedia di `database/database.sqlite`
- Struktur database tetap tersedia lewat migration di `database/migrations`
- Data awal tetap tersedia lewat seeder di `database/seeders/DatabaseSeeder.php`
- Bila memakai `composer run setup`, database akan dibangun ulang dari migration dan seeder

## Kustomisasi

### Update Data Projects
Di `DashboardController.php`, ganti array projects dengan query database:

```php
// Dari dummy data:
$projects = [...];

// Menjadi database query:
$projects = Project::all();
```

### Customize Colors
Edit CSS variables di view files:
- Primary: `#001a4d` (Dark Navy)
- Success: `#28a745` (Green)
- Warning: `#ffc107` (Yellow)
- Danger: `#dc3545` (Red)

### Adjust Auto-Scroll Speed
Di `project-overview.blade.php`, ubah `scrollBy` value:

```javascript
projectsList.scrollBy({
    left: 4,  // Change this value (pixels per scroll)
    behavior: 'auto'
});
```

## Dependencies

- Laravel Framework 11+
- Chart.js (via CDN)
- CSS Grid & Flexbox (modern browsers)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Fitur Tambahan yang Bisa Diimplementasikan

1. **Filter dan Search** untuk project list
2. **Export ke PDF** untuk report
3. **Real-time notifications** untuk perubahan status
4. **Gantt chart** untuk timeline project
5. **User roles dan permissions**
6. **Audit trail** untuk perubahan project
7. **Mobile responsive optimization**
8. **Dark mode support**
9. **WebSocket integration** untuk real-time updates
10. **Dashboard customization** per user

## Troubleshooting

### Auto-scroll tidak jalan
- Pastikan JavaScript enabled
- Check browser console untuk errors
- Verify element IDs match di HTML dan JS

### Project tidak bisa diklik
- Pastikan route `projects.detail` terdefinisi
- Check `href` attribute pada project cards
- Verify `$project->id` memiliki value

### Charts tidak tampil
- Pastikan Chart.js sudah di-load
- Check console untuk JS errors
- Verify canvas elements memiliki correct IDs

### Styling issues
- Clear browser cache
- Run `npm run build` untuk rebuild assets
- Check CSS media queries untuk responsive design

## Performance Tips

1. **Database Optimization**
   - Implement pagination untuk project list
   - Use eager loading dengan `with()` untuk relasi
   - Add database indexes untuk frequently queried columns

2. **Frontend Optimization**
   - Minify CSS dan JavaScript
   - Lazy load images
   - Use service workers untuk offline support

3. **Caching**
   - Implement Redis caching untuk project statistics
   - Cache expensive database queries
   - Use browser caching headers

## Contributing

Contributions welcome! Silakan fork repository dan submit pull request.

## License

MIT License - lihat LICENSE file untuk detail

## Support

Untuk pertanyaan atau issue, silakan create issue di repository ini atau hubungi developer.

---

Made with ❤️ for PM Project Tracker
