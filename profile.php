<?php
// profile.php - Halaman Profil Personal User
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$page_title = 'Profil Saya';
$user_id = $_SESSION['user_id'];

// Ambil data user dari database
$stmt = $conn->prepare("SELECT id, username, nama_lengkap, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    $_SESSION['error'] = 'Data user tidak ditemukan';
    redirect('dashboard.php');
}

// Proses Update Profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // Update Nama Lengkap
    if ($_POST['action'] == 'update_profile') {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        
        if (empty($nama_lengkap)) {
            $_SESSION['error'] = 'Nama lengkap tidak boleh kosong';
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_lengkap, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['success'] = 'Profil berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui profil';
            }
        }
        redirect('profile.php');
    }
    
    // Ganti Password
    if ($_POST['action'] == 'change_password') {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $password_konfirmasi = $_POST['password_konfirmasi'];
        
        // Validasi
        if (empty($password_lama) || empty($password_baru) || empty($password_konfirmasi)) {
            $_SESSION['error'] = 'Semua field password harus diisi';
        } elseif ($password_baru !== $password_konfirmasi) {
            $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok';
        } elseif (strlen($password_baru) < 6) {
            $_SESSION['error'] = 'Password baru minimal 6 karakter';
        } else {
            // Cek password lama
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (password_verify($password_lama, $user['password'])) {
                // Update password
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $password_hash, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Password berhasil diubah';
                } else {
                    $_SESSION['error'] = 'Gagal mengubah password';
                }
            } else {
                $_SESSION['error'] = 'Password lama tidak sesuai';
            }
        }
        redirect('profile.php');
    }
}

include 'header.php';
?>

<style>
    .profile-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    
    .card {
        background: white;
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .card-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .card-icon {
        font-size: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
    }
    
    .form-group input[type="text"]:focus,
    .form-group input[type="password"]:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .form-group input[type="text"]:disabled {
        background: #f3f4f6;
        cursor: not-allowed;
        color: #6b7280;
    }
    
    .info-box {
        background: #f9fafb;
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 14px;
    }
    
    .info-value {
        color: #1f2937;
        font-weight: 500;
        font-size: 14px;
    }
    
    .text-muted {
        color: #6b7280;
        font-size: 13px;
        margin-top: 6px;
        display: block;
    }
    
    .tips-box {
        margin-top: 24px;
        padding: 16px;
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
    }
    
    .tips-box strong {
        color: #92400e;
        font-size: 14px;
        display: block;
        margin-bottom: 8px;
    }
    
    .tips-box ul {
        margin: 0 0 0 20px;
        padding: 0;
        color: #78350f;
        font-size: 13px;
        line-height: 1.8;
    }
    
    @media (max-width: 1024px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .card {
            padding: 20px;
        }
    }
</style>

<div class="profile-container">
    <!-- Card Kiri: Informasi Profil -->
    <div class="card">
        <div class="card-header">
            <span class="card-icon">👤</span>
            <h2>Informasi Profil</h2>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                <span class="text-muted">Username tidak dapat diubah</span>
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" 
                       value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" 
                       required>
            </div>
            
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="info-value">
                        <span class="badge"><?php echo htmlspecialchars($user_data['role']); ?></span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Terdaftar Sejak</span>
                    <span class="info-value">
                        <?php echo date('d F Y', strtotime($user_data['created_at'])); ?>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-success" style="width: 100%;">
                💾 Simpan Perubahan
            </button>
        </form>
    </div>
    
    <!-- Card Kanan: Ganti Password -->
    <div class="card">
        <div class="card-header">
            <span class="card-icon">🔒</span>
            <h2>Keamanan Akun</h2>
        </div>
        
        <form method="POST" onsubmit="return validatePassword()">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label>Password Lama *</label>
                <input type="password" name="password_lama" id="password_lama" required>
            </div>
            
            <div class="form-group">
                <label>Password Baru *</label>
                <input type="password" name="password_baru" id="password_baru" required minlength="6">
                <span class="text-muted">Minimal 6 karakter</span>
            </div>
            
            <div class="form-group">
                <label>Konfirmasi Password Baru *</label>
                <input type="password" name="password_konfirmasi" id="password_konfirmasi" required>
            </div>
            
            <button type="submit" class="btn btn-warning" style="width: 100%;">
                🔑 Ubah Password
            </button>
        </form>
        
        <div class="tips-box">
            <strong>💡 Tips Keamanan:</strong>
            <ul>
                <li>Gunakan kombinasi huruf, angka, dan simbol</li>
                <li>Jangan gunakan password yang mudah ditebak</li>
                <li>Ubah password secara berkala</li>
                <li>Jangan bagikan password ke orang lain</li>
            </ul>
        </div>
    </div>
</div>

<script>
function validatePassword() {
    const passwordBaru = document.getElementById('password_baru').value;
    const passwordKonfirmasi = document.getElementById('password_konfirmasi').value;
    
    if (passwordBaru !== passwordKonfirmasi) {
        alert('Password baru dan konfirmasi password tidak cocok!');
        return false;
    }
    
    if (passwordBaru.length < 6) {
        alert('Password baru minimal 6 karakter!');
        return false;
    }
    
    return confirm('Yakin ingin mengubah password?');
}
</script>