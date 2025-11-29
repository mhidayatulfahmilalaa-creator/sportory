<?php
require_once 'config.php';
// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Proses form ketika di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_alat = $_POST['nama_alat'];
    $kategori = $_POST['kategori'];
    $jumlah_diminta = $_POST['jumlah_diminta'];
    $alasan = $_POST['alasan'];
    $perkiraan_harga = $_POST['perkiraan_harga'];
    $prioritas = $_POST['prioritas'];
    
    $query = "INSERT INTO rekomendasi_alat (user_id, nama_alat, kategori, jumlah_diminta, alasan, perkiraan_harga, prioritas) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issisds", $user_id, $nama_alat, $kategori, $jumlah_diminta, $alasan, $perkiraan_harga, $prioritas);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Rekomendasi alat berhasil dikirim!";
        redirect('daftar_rekomendasi.php');
    } else {
        $error = "Gagal mengirim rekomendasi alat!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Alat Baru - Sportory</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #2563eb;
            --primary-blue-dark: #1e40af;
            --accent-purple: #8b5cf6;
            --accent-purple-dark: #7c3aed;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --accent-red: #ef4444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-wrapper {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-purple-dark) 100%);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .form-header-content {
            position: relative;
            z-index: 1;
        }

        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.15s;
        }

        .back-link:hover {
            color: var(--primary-blue-dark);
            gap: 0.75rem;
        }

        .form-content {
            padding: 2rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .info-box {
            background: rgba(139, 92, 246, 0.05);
            border-left: 4px solid var(--accent-purple);
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            margin: 0;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 0.875rem;
        }

        .required {
            color: var(--accent-red);
            margin-left: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.15s;
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-500);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 140px;
        }

        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem;
            appearance: none;
        }

        .form-help {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .priority-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .priority-option {
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
            text-align: center;
        }

        .priority-option:hover {
            border-color: var(--accent-purple);
        }

        .priority-option.selected {
            border-color: var(--accent-purple);
            background: rgba(139, 92, 246, 0.1);
        }

        .priority-option input[type="radio"] {
            display: none;
        }

        .priority-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .priority-desc {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--accent-purple);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--accent-purple-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-header {
                padding: 1.5rem;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }

            .form-content {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .priority-options {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="form-header">
                <div class="form-header-content">
                    <h1>💡 Rekomendasi Alat Baru</h1>
                    <p>Ajukan rekomendasi pembelian alat olahraga baru</p>
                </div>
            </div>

            <div class="form-content">
                <a href="daftar_rekomendasi.php" class="back-link">
                    ← Kembali ke Daftar Rekomendasi
                </a>

                <div class="info-box">
                    <p><strong>📋 Petunjuk:</strong> Isi form dengan lengkap dan detail. Admin akan meninjau rekomendasi Anda dan memberikan keputusan.</p>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <span>✗</span>
                    <div><?php echo $error; ?></div>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">
                                Nama Alat <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nama_alat" 
                                class="form-control" 
                                placeholder="Contoh: Bola Futsal Nike Premier"
                                required
                            >
                            <span class="form-help">Masukkan nama lengkap dan spesifik alat yang direkomendasikan</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Kategori <span class="required">*</span>
                            </label>
                            <select name="kategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Bola">🏀 Bola</option>
                                <option value="Raket">🏸 Raket</option>
                                <option value="Matras">🧘 Matras</option>
                                <option value="Jaring">🥅 Jaring</option>
                                <option value="Lainnya">📦 Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Jumlah yang Diminta <span class="required">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="jumlah_diminta" 
                                class="form-control" 
                                min="1" 
                                placeholder="Masukkan jumlah"
                                required
                            >
                            <span class="form-help">Berapa banyak unit yang dibutuhkan?</span>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                Perkiraan Harga (Rp)
                            </label>
                            <input 
                                type="number" 
                                name="perkiraan_harga" 
                                class="form-control" 
                                min="0" 
                                step="1000" 
                                placeholder="Contoh: 500000"
                            >
                            <span class="form-help">Perkiraan harga per unit (opsional)</span>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                Prioritas <span class="required">*</span>
                            </label>
                            <input type="hidden" name="prioritas" id="prioritas_input" required>
                            <div class="priority-options">
                                <label class="priority-option" onclick="selectPriority('Rendah', this)">
                                    <input type="radio" name="priority_radio" value="Rendah">
                                    <div class="priority-label">🟢 Rendah</div>
                                    <div class="priority-desc">Bisa ditunda</div>
                                </label>
                                <label class="priority-option" onclick="selectPriority('Sedang', this)">
                                    <input type="radio" name="priority_radio" value="Sedang">
                                    <div class="priority-label">🟡 Sedang</div>
                                    <div class="priority-desc">Normal</div>
                                </label>
                                <label class="priority-option" onclick="selectPriority('Tinggi', this)">
                                    <input type="radio" name="priority_radio" value="Tinggi">
                                    <div class="priority-label">🔴 Tinggi</div>
                                    <div class="priority-desc">Penting</div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                Alasan & Deskripsi <span class="required">*</span>
                            </label>
                            <textarea 
                                name="alasan" 
                                class="form-control" 
                                placeholder="Jelaskan mengapa alat ini dibutuhkan:
- Untuk kegiatan apa?
- Apa manfaatnya?
- Mengapa prioritasnya tinggi/sedang/rendah?
- Informasi lain yang relevan..."
                                required
                            ></textarea>
                            <span class="form-help">Berikan penjelasan detail agar admin dapat mempertimbangkan dengan baik</span>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            📤 Kirim Rekomendasi
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='daftar_rekomendasi.php'">
                            ✗ Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectPriority(value, element) {
            // Remove selected class from all options
            document.querySelectorAll('.priority-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('prioritas_input').value = value;
        }
    </script>
</body>
</html>