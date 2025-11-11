<!-- File: app/views/dashboard/manager.php (UPDATED) -->
<?php $title = 'Manager Dashboard'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>📊 Manager Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars($user->getFullName()) ?>! Kelola lapangan dan reservasi.</p>
</div>

<!-- 🔔 NOTIFIKASI SECTION -->
<?php if (!empty($notifications)): ?>
<div class="content-card" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #ff9800;">
    <h3>🔔 Notifikasi Baru (<?= $unread_count ?>)</h3>
    
    <div style="max-height: 300px; overflow-y: auto;">
        <?php foreach ($notifications as $notif): ?>
        <div style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 3px solid <?= 
            match($notif->getType()) {
                'new_reservation' => '#2196F3',
                'rescheduled' => '#FF9800',
                'cancel_requested' => '#F44336',
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
           style="color: #FF6F00; text-decoration: none; font-weight: 600;">
            Lihat Semua Notifikasi →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ⏳ PENDING APPROVALS -->
<?php if (!empty($pending_approvals)): ?>
<div class="content-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196F3;">
    <h3>⏳ Reservasi Menunggu Approval (<?= count($pending_approvals) ?>)</h3>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Lapangan</th>
                <th>Waktu</th>
                <th>Dibuat Oleh</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($pending_approvals, 0, 5) as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['court_name']) ?></td>
                <td><?= DateHelper::format($r['start_time'], 'd M Y H:i') ?></td>
                <td>
                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                        <?= htmlspecialchars($r['created_by_name'] ?? 'N/A') ?>
                    </span>
                </td>
                <td>
                    <a href="<?= $base_url ?>?c=reservation&a=edit&id=<?= $r['id'] ?>" 
                       style="padding: 6px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem;">
                        📋 Review
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (count($pending_approvals) > 5): ?>
    <div style="margin-top: 15px; text-align: right;">
        <a href="<?= $base_url ?>?c=reservation&a=managerPending" 
           style="color: #1976D2; text-decoration: none; font-weight: 600;">
            Lihat Semua (<?= count($pending_approvals) ?>) →
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- 🗑️ PENDING CANCELLATIONS -->
<?php if (!empty($pending_cancel)): ?>
<div class="content-card" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-left: 4px solid #F44336;">
    <h3>🗑️ Permintaan Pembatalan (<?= count($pending_cancel) ?>)</h3>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Lapangan</th>
                <th>Alasan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($pending_cancel, 0, 3) as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['court_name']) ?></td>
                <td>
                    <small><?= htmlspecialchars(substr($r['cancellation_reason'] ?? '', 0, 40)) ?>...</small>
                </td>
                <td>
                    <a href="<?= $base_url ?>?c=reservation&a=edit&id=<?= $r['id'] ?>" 
                       style="padding: 6px 12px; background: #F44336; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem;">
                        📋 Review
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (count($pending_cancel) > 3): ?>
    <div style="margin-top: 15px; text-align: right;">
        <a href="<?= $base_url ?>?c=reservation&a=pendingCancel" 
           style="color: #C62828; text-decoration: none; font-weight: 600;">
            Lihat Semua (<?= count($pending_cancel) ?>) →
        </a>
    </div>
    <?php endif; ?>
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
        <div class="icon">⏳</div>
        <div class="label">Pending Approval</div>
        <div class="value"><?= count($pending_approvals) ?></div>
    </div>
    
    <div class="stat-card">
        <div class="icon">🗑️</div>
        <div class="label">Pending Cancel</div>
        <div class="value"><?= count($pending_cancel) ?></div>
    </div>
</div>

<!-- 📈 WORKFLOW STATS -->
<div class="content-card">
    <h3>📈 Status Workflow</h3>
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
    <h3>🕐 Aktivitas Terbaru</h3>
    
    <?php if (empty($recent_activities)): ?>
        <p style="text-align: center; color: #999; padding: 40px;">Belum ada aktivitas.</p>
    <?php else: ?>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php foreach ($recent_activities as $activity): ?>
            <div style="padding: 12px; background: #f8f9fa; margin-bottom: 8px; border-radius: 6px; border-left: 3px solid #667eea;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #333;">
                            <?= htmlspecialchars($activity['full_name']) ?>
                            <span style="background: #e3f2fd; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-left: 5px;">
                                <?= htmlspecialchars($activity['role']) ?>
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin: 5px 0;">
                            <strong><?= htmlspecialchars($activity['action']) ?></strong> 
                            - <?= htmlspecialchars($activity['customer_name']) ?> 
                            (<?= htmlspecialchars($activity['court_name']) ?>)
                        </div>
                        <?php if ($activity['notes']): ?>
                        <div style="font-size: 0.8rem; color: #666; font-style: italic;">
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
        <a href="<?= $base_url ?>?c=reservation&a=managerPending" 
           style="padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            ⏳ Review Pending (<?= count($pending_approvals) ?>)
        </a>
        <a href="<?= $base_url ?>?c=reservation&a=pendingCancel" 
           style="padding: 12px 24px; background: #F44336; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            🗑️ Review Cancellations (<?= count($pending_cancel) ?>)
        </a>
        <a href="<?= $base_url ?>?c=reservation&a=create" 
           style="padding: 12px 24px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            📅 Buat Reservasi Baru
        </a>
        <a href="<?= $base_url ?>?c=court&a=index" 
           style="padding: 12px 24px; background: #f57c00; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            🏟️ Kelola Lapangan
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>