# Desktop Wrapper (Windows)

Folder ini berisi wrapper desktop C++ untuk aplikasi web Laravel Anda.

## Cara pakai

1. Pastikan PHP tersedia di PATH.
2. Jalankan aplikasi web biasa melalui Laravel (atau biarkan wrapper otomatis memulai server di port 8000).
3. Build project desktop ini dengan Visual Studio 2022 / MSVC.

## Build dengan Visual Studio

- Buat project baru "Desktop application" (Win32 / Windows desktop).
- Tambahkan file ini sebagai main.cpp.
- Tambahkan dependensi Microsoft.Web.WebView2.
- Build untuk x64.

## Catatan

- Aplikasi inti tetap berbasis web.
- UI desktop hanya membungkus halaman web Anda dalam jendela Windows.
- Jika server Laravel tidak berjalan, wrapper akan mencoba menjalankan:
  php artisan serve --host=127.0.0.1 --port=8000
