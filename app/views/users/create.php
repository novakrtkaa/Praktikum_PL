<!-- File: app/views/users/create.php -->
<?php $title = 'Tambah User'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>➕ Tambah User Baru</h1>
</div>

<div class="content-card">
    <form method="POST" action="<?= $base_url ?>?c=user&a=store">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        
        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" required placeholder="Contoh: john_doe">
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required placeholder="Contoh: john@example.com">
        </div>
        
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required placeholder="Minimal 6 karakter">
        </div>
        
        <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="full_name" required placeholder="Contoh: John Doe">
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>
        </div>
        
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                💾 Simpan
            </button>
            <a href="<?= $base_url ?>?c=user&a=index" 
               style="padding: 10px 20px; background: #999; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
                ❌ Batal
            </a>
        </div>
    </form>
</div>

<style>
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        font-size: 1rem;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
    }
</style>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>