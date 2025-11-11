<!-- File: app/views/dashboard/admin.php (COMPLETE) -->
<?php $title = 'Admin Dashboard'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>👑 Admin Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars($user->getFullName()) ?>! Anda memiliki akses penuh ke sistem.</p>
</div>

<!-- 🔔 NOTIFIKASI SECTION (ADMIN) -->
<?php if (!empty($notifications) && $unread_count > 0): ?>
<div class="content-card" style="background: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%); border-left: 4px solid #5e35b1;">
    <h3>🔔 Notifikasi Baru (<?= $unread_count ?>)</h3>
    
    <div style="max-height: 300px; overflow-y: auto;">
        <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
        <div style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 3px solid <?= 
            match($notif->getType()) {
                'new_reservation' => '#2196F3',
                'reservation_approved' => '#4CAF50',
                'rescheduled' => '#FF9800',
                'cancel_requested' => '#F44336',
                'reservation_rejected' => '#F44336',
                default => '#9E9E9E'
            } 
        ?>;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                        <?= htmlspecialchars($notif->getTitle()) ?>
                    </div>
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">
                        <?= htmlspecialchars($notif->getMessage()) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #999;">
                        <?= DateHelper::diffHuman($notif->getCreatedAt()) ?>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <?php if ($notif->getLink()): ?>
                    <a href="<?= $base_url . $notif->getLink() ?>" 
                       style="padding: 6px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem;">
                        👁️ Lihat
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= $base_url ?>?c=notification&a=markAsRead&id=<?= $notif->getId() ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                       style="padding: 6px 12px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem;">
                        ✓
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px; text-align: right;">
        <a href="<?= $base_url ?>?c=notification&a=index" 
           style="color: #5e35b1; text-decoration: none; font-weight: 600;">
            Lihat Semua Notifikasi →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ⏳ PENDING APPROVALS (ADMIN) -->
<?php if ($pending_manager > 0): ?>
<div class="content-card" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #FF9800;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>⏳ Reservasi Menunggu Approval Manager (<?= $pending_manager ?>)</h3>
        <a href="<?= $base_url ?>?c=reservation&a=managerPending" 
           style="padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;">
            📋 Review Sekarang
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 🗑️ PENDING CANCELLATIONS (ADMIN) -->
<?php if ($pending_cancel > 0): ?>
<div class="content-card" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-left: 4px solid #F44336;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>🗑️ Permintaan Pembatalan (<?= $pending_cancel ?>)</h3>
        <a href="<?= $base_url ?>?c=reservation&a=pendingCancel" 
           style="padding: 10px 20px; background: #F44336; color: white; text-decoration: none; border-radius: 5px;">
            📋 Review Sekarang
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 📅 PENDING RESCHEDULE (ADMIN) -->
<?php if ($pending_reschedule > 0): ?>
<div class="content-card" style="background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%); border-left: 4px solid #009688;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3>📅 Perlu Reschedule (<?= $pending_reschedule ?>)</h3>
        <a href="<?= $base_url ?>?c=reservation&a=index" 
           style="padding: 10px 20px; background: #009688; color: white; text-decoration: none; border-radius: 5px;">
            📋 Lihat Data
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 📊 STATISTICS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon">📅</div>
        <div class="label">Total Reservasi</div>
        <div class="value"><?= $stats['reservations'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">🏟️</div>
        <div class="label">Total Lapangan</div>
        <div class="value"><?= $stats['courts'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">👥</div>
        <div class="label">Total Users</div>
        <div class="value"><?= $stats['users'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">🗑️</div>
        <div class="label">Recycle Bin</div>
        <div class="value"><?= $stats['trashed'] ?></div>
    </div>
</div>

<!-- 👥 USER STATISTICS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon">👑</div>
        <div class="label">Admin</div>
        <div class="value"><?= $stats['admins'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">📊</div>
        <div class="label">Manager</div>
        <div class="value"><?= $stats['managers'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">👤</div>
        <div class="label">Staff</div>
        <div class="value"><?= $stats['staff'] ?></div>
    </div>
</div>

<!-- 📈 WORKFLOW STATS -->
<div class="content-card">
    <h3>📈 Status Workflow Reservasi</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php 
        $workflowLabels = [
            'pending_manager' => ['label' => 'Pending Manager', 'color' => '#FFC107', 'icon' => '⏳'],
            'approved' => ['label' => 'Approved', 'color' => '#4CAF50', 'icon' => '✅'],
            'pending_reschedule' => ['label' => 'Pending Reschedule', 'color' => '#FF9800', 'icon' => '📅'],
            'pending_cancel' => ['label' => 'Pending Cancel', 'color' => '#F44336', 'icon' => '🗑️'],
            'rejected' => ['label' => 'Rejected', 'color' => '#9E9E9E', 'icon' => '❌'],
            'canceled' => ['label' => 'Canceled', 'color' => '#607D8B', 'icon' => '🚫']
        ];
        
        foreach ($workflowLabels as $status => $info):
            $count = $workflow_stats[$status] ?? 0;
        ?>
        <div style="background: <?= $info['color'] ?>20; padding: 15px; border-radius: 8px; border-left: 4px solid <?= $info['color'] ?>;">
            <div style="font-size: 1.5rem; margin-bottom: 5px;"><?= $info['icon'] ?></div>
            <div style="color: #666; font-size: 0.85rem;"><?= $info['label'] ?></div>
            <div style="font-size: 1.5rem; font-weight: bold; color: #333;"><?= $count ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 🕐 RECENT ACTIVITIES -->
<div class="content-card">
    <h3>🕐 Aktivitas Terbaru Sistem</h3>
    
    <?php if (empty($recent_activities)): ?>
        <p style="text-align: center; color: #999; padding: 40px;">Belum ada aktivitas.</p>
    <?php else: ?>
        <div style="max-height: 500px; overflow-y: auto;">
            <?php foreach ($recent_activities as $activity): ?>
            <div style="padding: 12px; background: #f8f9fa; margin-bottom: 8px; border-radius: 6px; border-left: 3px solid #667eea;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #333;">
                            <?= htmlspecialchars($activity['full_name']) ?>
                            <span style="background: <?= 
                                match($activity['role']) {
                                    'admin' => '#e3f2fd',
                                    'manager' => '#fff3e0',
                                    'staff' => '#f3e5f5',
                                    default => '#f5f5f5'
                                }
                            ?>; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-left: 5px;">
                                <?= htmlspecialchars($activity['role']) ?>
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin: 5px 0;">
                            <strong><?= htmlspecialchars($activity['action']) ?></strong>
                            <?php if (!empty($activity['old_status']) && !empty($activity['new_status'])): ?>
                                <span style="color: #999;">
                                    (<?= htmlspecialchars($activity['old_status']) ?> → <?= htmlspecialchars($activity['new_status']) ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.85rem; color: #666;">
                            Reservasi: <?= htmlspecialchars($activity['customer_name']) ?> 
                            - <?= htmlspecialchars($activity['court_name']) ?>
                        </div>
                        <?php if ($activity['notes']): ?>
                        <div style="font-size: 0.8rem; color: #666; font-style: italic; margin-top: 5px;">
                            "<?= htmlspecialchars($activity['notes']) ?>"
                        </div>
                        <?php endif; ?>
                        <div style="font-size: 0.75rem; color: #999; margin-top: 5px;">
                            <?= DateHelper::diffHuman($activity['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ⚡ QUICK ACTIONS -->
<div class="content-card">
    <h3>⚡ Quick Actions</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;">
        <a href="<?= $base_url ?>?c=user&a=create" 
           style="padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            ➕ Tambah User Baru
        </a>
        <a href="<?= $base_url ?>?c=reservation&a=create" 
           style="padding: 12px 24px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            📅 Buat Reservasi
        </a>
        <a href="<?= $base_url ?>?c=court&a=index" 
           style="padding: 12px 24px; background: #f57c00; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            🏟️ Kelola Lapangan
        </a>
        <a href="<?= $base_url ?>?c=reservation&a=managerPending" 
           style="padding: 12px 24px; background: #FF9800; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            ⏳ Review Pending (<?= $pending_manager ?>)
        </a>
        <a href="<?= $base_url ?>?c=recyclebin&a=index" 
           style="padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            🗑️ Recycle Bin (<?= $stats['trashed'] ?>)
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>