<!-- File: app/views/users/index.php -->
<?php $title = 'Kelola User'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>👥 Kelola User</h1>
</div>

<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Daftar User</h3>
        <a href="<?= $base_url ?>?c=user&a=create" 
           style="padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
            ➕ Tambah User Baru
        </a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="7" style="text-align:center;">Tidak ada data user.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u->getId() ?></td>
                    <td><?= htmlspecialchars($u->getUsername()) ?></td>
                    <td><?= htmlspecialchars($u->getEmail()) ?></td>
                    <td><?= htmlspecialchars($u->getFullName()) ?></td>
                    <td>
                        <span class="badge badge-<?= $u->getRole() ?>">
                            <?= ucfirst($u->getRole()) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u->isActive()): ?>
                            <span class="badge badge-active">Aktif</span>
                        <?php else: ?>
                            <span style="color: #999;">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= $base_url ?>?c=user&a=edit&id=<?= $u->getId() ?>" 
                           style="color: #667eea; text-decoration: none; margin-right: 10px;">Edit</a>
                        
                        <?php if ($u->getId() != Auth::getInstance()->id()): ?>
                        <a href="<?= $base_url ?>?c=user&a=delete&id=<?= $u->getId() ?>" 
                           style="color: #dc3545; text-decoration: none;"
                           onclick="return confirm('Yakin ingin menonaktifkan user ini?')">Nonaktifkan</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>