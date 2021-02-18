# 📁 File Management System

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

**Sistem Manajemen File Modern dengan Interface yang Elegan**

[Demo](#demo) • [Fitur](#-fitur-utama) • [Instalasi](#-instalasi) • [Dokumentasi](#-dokumentasi)

</div>

---

## 🌟 Tentang Proyek

File Management System adalah aplikasi web modern yang dirancang untuk mengelola dokumen dan file dengan mudah dan efisien. Sistem ini menyediakan interface yang user-friendly dengan fitur-fitur canggih untuk upload, organize, dan manage file dalam berbagai kategori dan folder.

### ✨ Mengapa Memilih Sistem Ini?

- 🎨 **Interface Modern** - Desain yang clean dan responsive menggunakan TailwindCSS
- 🔐 **Keamanan Tinggi** - Sistem autentikasi yang robust dengan role-based access control
- 📱 **Responsive Design** - Bekerja sempurna di desktop, tablet, dan mobile
- 🌍 **Multi-bahasa** - Mendukung bahasa Indonesia dan Inggris
- ⚡ **Performa Optimal** - Dioptimalkan untuk kecepatan dan efisiensi

## 🚀 Fitur Utama

### 📊 Dashboard Interaktif
- **Statistik Real-time** - Monitor total dokumen, folder, kategori, dan pengguna
- **Recent Activities** - Lacak aktivitas terbaru dalam sistem
- **Quick Actions** - Akses cepat ke fungsi-fungsi utama
- **Visual Analytics** - Grafik dan chart untuk insight yang lebih baik

### 📄 Manajemen Dokumen
- **Upload Multi-format** - Mendukung PDF, DOC, DOCX, TXT, JPG, PNG, GIF, ZIP, RAR
- **Drag & Drop Upload** - Interface upload yang intuitif
- **File Preview** - Preview file sebelum upload
- **Batch Operations** - Operasi massal untuk efisiensi
- **Version Control** - Kelola versi dokumen dengan mudah

### 📁 Sistem Folder & Kategori
- **Hierarchical Folders** - Struktur folder bertingkat
- **Smart Categorization** - Kategorisasi otomatis berdasarkan tipe file
- **Advanced Search** - Pencarian berdasarkan nama, kategori, atau folder
- **Bulk Organization** - Organize multiple files sekaligus

### 👥 User Management
- **Role-based Access** - Admin, User, dan Viewer dengan hak akses berbeda
- **Permission System** - Kontrol akses read, write, delete per dokumen
- **User Activity Logs** - Tracking semua aktivitas pengguna
- **Profile Management** - Kelola profil dan preferensi pengguna

### 🔒 Keamanan & Privasi
- **Secure Authentication** - Password hashing dengan bcrypt
- **CSRF Protection** - Perlindungan dari serangan CSRF
- **File Validation** - Validasi tipe dan ukuran file
- **Access Logging** - Log semua akses dan aktivitas

## 🛠 Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| **Backend** | PHP 7.4+, PDO MySQL |
| **Frontend** | HTML5, TailwindCSS, JavaScript ES6+ |
| **Database** | MySQL 5.7+ / MariaDB |
| **Icons** | Font Awesome 6 |
| **Security** | Password Hashing, CSRF Tokens, Input Sanitization |

## 📋 Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi (atau MariaDB 10.2+)
- **Web Server**: Apache/Nginx dengan mod_rewrite
- **Extensions**: PDO, PDO_MySQL, GD, FileInfo
- **Memory**: Minimum 128MB RAM
- **Storage**: Minimum 100MB disk space

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/username/file-management-system.git
cd file-management-system
```

### 2. Konfigurasi Database
```sql
-- Buat database baru
CREATE DATABASE file_management_db;

-- Import struktur database
mysql -u username -p file_management_db < database/schema.sql
```

### 3. Konfigurasi Aplikasi
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'file_management_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Set Permissions
```bash
# Set permission untuk folder upload
chmod 755 uploads/
chmod 755 uploads/documents/

# Set permission untuk folder config (opsional)
chmod 644 config/
```

### 5. Akses Aplikasi
Buka browser dan akses: `http://localhost/file-management-system`

**Default Admin Account:**
- Email: `admin@example.com`
- Password: `admin123`

## 📖 Dokumentasi

### 🏗 Struktur Proyek
```
file-management-system/
├── 📁 components/          # Komponen UI (header, sidebar, footer)
├── 📁 config/             # Konfigurasi aplikasi dan database
├── 📁 includes/           # File PHP utilities dan functions
├── 📁 languages/          # File bahasa (ID, EN)
├── 📁 pages/              # Halaman-halaman aplikasi
├── 📁 uploads/            # Folder penyimpanan file upload
│   └── 📁 documents/      # Dokumen yang diupload
├── 📄 index.php           # Entry point aplikasi
└── 📄 README.md           # Dokumentasi ini
```

### 🎯 Cara Penggunaan

#### Upload Dokumen
1. Login ke sistem
2. Navigasi ke halaman **Documents**
3. Klik tombol **Upload Document**
4. Pilih file, kategori, dan folder
5. Klik **Upload**

#### Manajemen Folder
1. Buka halaman **Folders**
2. Klik **Create Folder**
3. Masukkan nama folder dan pilih parent folder (opsional)
4. Klik **Save**

#### Manajemen User (Admin Only)
1. Login sebagai Admin
2. Buka halaman **Users**
3. Klik **Add User**
4. Isi form dan pilih role
5. Klik **Save**

### 🔧 Kustomisasi

#### Menambah Tipe File Baru
```php
// config/config.php
define('ALLOWED_EXTENSIONS', [
    'pdf', 'doc', 'docx', 'txt', 
    'jpg', 'jpeg', 'png', 'gif', 
    'zip', 'rar', 'mp4', 'avi'  // Tambahkan tipe baru
]);
```

#### Mengubah Ukuran File Maksimum
```php
// config/config.php
define('MAX_FILE_SIZE', 20971520); // 20MB
```

#### Menambah Bahasa Baru
1. Buat file baru di folder `languages/` (contoh: `fr.php`)
2. Copy struktur dari `en.php` dan translate
3. Update language selector di aplikasi

## 🎨 Screenshots

### Dashboard
![image](https://github.com/user-attachments/assets/f11ae06c-7032-472c-b467-5094e13924a1)


### Document Management
![image](https://github.com/user-attachments/assets/4d6052ac-6ec6-4f99-a6b0-be32640381a0)


## 🤝 Kontribusi

Kami menyambut kontribusi dari komunitas! Berikut cara berkontribusi:

1. **Fork** repository ini
2. **Create** branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. **Commit** perubahan (`git commit -m 'Add some AmazingFeature'`)
4. **Push** ke branch (`git push origin feature/AmazingFeature`)
5. **Open** Pull Request

### 📝 Guidelines Kontribusi
- Ikuti coding standards yang ada
- Tulis komentar yang jelas
- Test fitur sebelum submit PR
- Update dokumentasi jika diperlukan

## 🐛 Bug Reports & Feature Requests

Temukan bug atau punya ide fitur baru? Silakan buat [issue](https://github.com/username/file-management-system/issues) dengan detail:

**Bug Report:**
- Deskripsi bug
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (jika ada)
- Environment details

**Feature Request:**
- Deskripsi fitur
- Use case
- Mockup/wireframe (jika ada)

## 📊 Roadmap

### 🎯 Version 2.0 (Coming Soon)
- [ ] **API REST** - RESTful API untuk integrasi
- [ ] **File Sharing** - Share file dengan link publik
- [ ] **Advanced Search** - Full-text search dalam dokumen
- [ ] **File Versioning** - Version control untuk dokumen
- [ ] **Bulk Operations** - Operasi massal untuk file

### 🎯 Version 2.1
- [ ] **Cloud Storage** - Integrasi dengan Google Drive, Dropbox
- [ ] **Real-time Notifications** - Notifikasi real-time
- [ ] **Advanced Analytics** - Dashboard analytics yang lebih detail
- [ ] **Mobile App** - Aplikasi mobile companion

## 📄 Lisensi

Proyek ini dilisensikan under [MIT License](LICENSE) - lihat file LICENSE untuk detail lengkap.

## 👨‍💻 Tim Pengembang

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/nathanAbrahamSinaga">
        <img src="https://github.com/nathanAbrahamSinaga.png" width="100px;" alt="Nathan's profile picture"/>
        <br />
        <sub><b>Nathan Abraham Sinaga</b></sub>
      </a>
      <br />
      <sub>Lead Developer</sub>
    </td>
    <td align="center">
      <a href="https://github.com/kangzid">
        <img src="https://github.com/kangzid.png" width="100px;" alt="Zidan's profile picture"/>
        <br />
        <sub><b>Zidan Alfian Mubarok</b></sub>
      </a>
      <br />
      <sub>Fullstack Developer</sub>
    </td>
  </tr>
  <tr>
    <td align="center">
      <a href="https://github.com/your-github-username">
        <img src="https://github.com/your-github-username.png" width="100px;" alt="Your profile picture"/>
        <br />
        <sub><b>Rifaldo Candra</b></sub>
      </a>
      <br />
      <sub>Database Engineer</sub>
    </td>
    <td align="center">
      <a href="https://github.com/another-github-user">
        <img src="https://github.com/another-github-user.png" width="100px;" alt="Another profile picture"/>
        <br />
        <sub><b>Brillian Bagus Krisna</b></sub>
      </a>
      <br />
      <sub>UI/UX Designer</sub>
    </td>
  </tr>
</table>


## 🙏 Acknowledgments

- [TailwindCSS](https://tailwindcss.com/) untuk framework CSS yang amazing
- [Font Awesome](https://fontawesome.com/) untuk icon set yang lengkap
- [PHP Community](https://www.php.net/) untuk dokumentasi dan support
- Semua kontributor yang telah membantu pengembangan proyek ini

---

<div align="center">

**⭐ Jika proyek ini membantu Anda, jangan lupa berikan star! ⭐**

[⬆ Kembali ke atas](#-file-management-system)

</div>