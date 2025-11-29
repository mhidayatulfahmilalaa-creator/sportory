<?php
require_once 'config.php';

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    alert_redirect('Akses ditolak! Hanya admin yang dapat mengakses halaman ini.', 'dashboard.php');
}

// Set page title
$page_title = "Kelola User";

$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Proses tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap_new = clean_input($_POST['nama_lengkap']);
    $role_new = 'user';

    if (empty($username) || empty($password) || empty($nama_lengkap_new)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $check_query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $username, $hashed_password, $nama_lengkap_new, $role_new);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User berhasil ditambahkan!';
                redirect('manage_users.php');
            } else {
                $error = 'Gagal menambahkan user!';
            }
        }
        $stmt->close();
    }
}

// Proses hapus user
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    
    if ($delete_id == $user_id) {
        $_SESSION['error'] = 'Anda tidak bisa menghapus akun sendiri!';
        redirect('manage_users.php');
    } else {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'User berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus user!';
        }
        $stmt->close();
        redirect('manage_users.php');
    }
}

// Ambil semua user
$query_users = "SELECT * FROM users ORDER BY created_at DESC";
$result_users = $conn->query($query_users);

// Include header
include 'header.php';
?>

<style>
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
    }

    .form-container {
        background: white;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .form-container h2 {
        color: #1f2937;
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
        font-size: 20px;
        font-weight: 700;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .required {
        color: #ef4444;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-group small {
        color: #6b7280;
        font-size: 12px;
        display: block;
        margin-top: 6px;
    }

    .password-toggle {
        position: relative;
    }

    .toggle-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6b7280;
        font-size: 18px;
        user-select: none;
    }

    .toggle-icon:hover {
        color: #2563eb;
    }

    .form-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }

    .search-box {
        margin-bottom: 20px;
    }

    .search-box input {
        width: 100%;
        padding: 14px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .search-box input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #2563eb, #1e40af);
    }

    th {
        padding: 16px 20px;
        text-align: left;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    tbody tr:hover {
        background: #f3f4f6;
    }

    td {
        padding: 16px 20px;
        color: #1f2937;
        font-size: 14px;
    }

    .role-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
    }

    .role-badge.admin {
        background: #2563eb;
        color: white;
    }

    .role-badge.user {
        background: #3b82f6;
        color: white;
    }

    .action-btn {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .action-btn.delete {
        background: #ef4444;
        color: white;
    }

    .action-btn.delete:hover {
        background: #dc2626;
    }

    .action-btn:disabled {
        background: #d1d5db;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 968px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 24px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            min-width: 600px;
        }
    }
</style>

<div class="content-grid">
    <div class="form-container">
        <h2>➕ Tambah User Baru</h2>
        <form method="POST" action="" id="addUserForm">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Masukkan username"
                    required
                    autocomplete="off"
                >
                <small>Username harus unik</small>
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="nama_lengkap" 
                    name="nama_lengkap" 
                    placeholder="Masukkan nama lengkap"
                    required
                >
            </div>

            <div class="form-group password-toggle">
                <label for="password">Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Minimal 6 karakter"
                    required
                    autocomplete="new-password"
                >
                <span class="toggle-icon" onclick="togglePassword('password')">👁️</span>
                <small>Minimal 6 karakter</small>
            </div>

            <div class="form-group password-toggle">
                <label for="confirm_password">Konfirmasi Password <span class="required">*</span></label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Ulangi password"
                    required
                    autocomplete="new-password"
                >
                <span class="toggle-icon" onclick="togglePassword('confirm_password')">👁️</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-block" id="submitBtn">
                    <span id="btnText">➕ Tambah User</span>
                </button>
                <button type="reset" class="btn btn-warning btn-block" onclick="return confirm('Reset form?')">
                    🔄 Reset Form
                </button>
            </div>
        </form>
    </div>

    <div class="form-container">
        <h2>📋 User yang Terdaftar</h2>
        
        <div class="search-box">
            <input 
                type="text" 
                id="searchUser" 
                placeholder="🔍 Cari user..."
                onkeyup="searchUsers()"
            >
        </div>

        <div class="table-wrapper">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($user = $result_users->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                        <td>
                            <span class="role-badge <?php echo $user['role']; ?>">
                                <?php echo $user['role'] == 'admin' ? '👑 ' : '👤 '; ?>
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != $user_id): ?>
                            <button 
                                class="action-btn delete" 
                                onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')"
                            >
                                🗑️ Hapus
                            </button>
                            <?php else: ?>
                            <button class="action-btn delete" disabled>
                                🔒 Hapus
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div> <!-- End container -->

<script>
    function deleteUser(id, username) {
        if (confirm('Hapus user "' + username + '"?')) {
            window.location.href = 'manage_users.php?action=delete&id=' + id;
        }
    }

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = field.parentElement.querySelector('.toggle-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.textContent = '🙈';
        } else {
            field.type = 'password';
            icon.textContent = '👁️';
        }
    }

    function searchUsers() {
        const input = document.getElementById('searchUser');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('userTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let found = false;
            const td = tr[i].getElementsByTagName('td');
            
            for (let j = 1; j <= 2; j++) {
                if (td[j]) {
                    const txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            tr[i].style.display = found ? '' : 'none';
        }
    }

    let isSubmitting = false;
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Password tidak sama!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter!');
            return false;
        }

        isSubmitting = true;
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        
        btnText.innerHTML = '<span class="loading"></span> Memproses...';
        submitBtn.disabled = true;
    });

    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword === '') return;
        
        if (password === confirmPassword) {
            this.style.borderColor = '#10b981';
        } else {
            this.style.borderColor = '#ef4444';
        }
    });
</script>

</body>
</html>