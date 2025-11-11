<!-- File: app/views/dashboard/staff.php (COMPLETE) -->
<?php $title = 'Staff Dashboard'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>👤 Staff Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars($user->getFullName()) ?>! Kelola reservasi pelanggan.</p>
</div>

<!-- 🔔 NOTIFIKASI SECTION (STAFF) -->
<?php if (!empty($notifications) && $unread_count > 0): ?>
<div class="content-card" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); border-left: 4px solid #9c27b0;">
    <h3>🔔 Notifikasi Baru (<?= $unread_count ?>)</h3>
    
    <div style="max-height: 300px; overflow-y: auto;">
        <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
        <div style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 3px solid <?= 
            match($notif->getType()) {
                'reservation_approved' => '#4CAF50',
                'reschedule_requested' => '#FF9800',
                'reservation_rejected' => '#F44336',
                'cancel_approved' => '#9E9E9E',
                default => '#9E9E9E'
            } 
        ?>;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="font-size: 1.5rem;">
                            <?= match($notif->getType()) {
                                'reservation_approved' => '✅',
                                'reschedule_requested' => '📅',
                                'reservation_rejected' => '❌',
                                'cancel_approved' => '✓',
                                default => '🔔'
                            } ?>
                        </div>
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
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-shrink: 0;">
                    <?php if ($notif->getLink()): ?>
                    <a href="<?= $base_url . $notif->getLink() ?>" 
                       style="padding: 6px 12px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem; white-space: nowrap;">
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
           style="color: #9c27b0; text-decoration: none; font-weight: 600;">
            Lihat Semua Notifikasi →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 📅 PERLU RESCHEDULE -->
<?php if (!empty($need_reschedule)): ?>
<div class="content-card" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #FF9800;">
    <h3>⚠️ Reservasi Perlu Dijadwal Ulang (<?= count($need_reschedule) ?>)</h3>
    <p style="color: #666; margin-bottom: 15px;">Manager meminta Anda untuk mengubah jadwal reservasi berikut:</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Lapangan</th>
                <th>Waktu Sebelumnya</th>
                <th>Alasan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($need_reschedule, 0, 5) as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['court_name']) ?></td>
                <td>
                    <?= DateHelper::format($r['start_time'], 'd M Y H:i') ?>
                    <br>
                    <small style="color: #666;">s/d <?= DateHelper::format($r['end_time'], 'H:i') ?></small>
                </td>
                <td>
                    <div style="max-width: 200px; font-size: 0.85rem; color: #666;">
                        <?= htmlspecialchars(substr($r['rejection_reason'] ?? '', 0, 50)) ?>...
                    </div>
                </td>
                <td>
                    <a href="<?= $base_url ?>?c=reservation&a=edit&id=<?= $r['id'] ?>" 
                       style="padding: 6px 12px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem; white-space: nowrap;">
                        📝 Edit Jadwal
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- 🕐 AKTIVITAS SAYA -->
<div class="content-card">
    <h3>🕐 Aktivitas Saya Terbaru</h3>
    
    <?php if (empty($my_activities)): ?>
        <p style="text-align: center; color: #999; padding: 40px;">
            Belum ada aktivitas. Mulai dengan membuat reservasi baru!
        </p>
    <?php else: ?>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php foreach ($my_activities as $activity): ?>
            <div style="padding: 12px; background: #f8f9fa; margin-bottom: 8px; border-radius: 6px; border-left: 3px solid #9c27b0;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.9rem; color: #333; margin-bottom: 5px;">
                            <span style="font-size: 1.2rem; margin-right: 5px;">
                                <?= match($activity['action']) {
                                    'created' => '➕',
                                    'rescheduled' => '📅',
                                    'request_cancel' => '🗑️',
                                    default => '📝'
                                } ?>
                            </span>
                            <?= htmlspecialchars($activity['action']) ?>
                            <?php if (!empty($activity['old_status']) && !empty($activity['new_status'])): ?>
                                <span style="color: #999; font-weight: normal; font-size: 0.85rem;">
                                    (<?= htmlspecialchars($activity['old_status']) ?> → <?= htmlspecialchars($activity['new_status']) ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin: 5px 0;">
                            <strong>Customer:</strong> <?= htmlspecialchars($activity['customer_name']) ?> 
                            | <strong>Lapangan:</strong> <?= htmlspecialchars($activity['court_name']) ?>
                        </div>
                        <?php if ($activity['notes']): ?>
                        <div style="font-size: 0.8rem; color: #666; font-style: italic; margin-top: 5px; padding: 8px; background: white; border-radius: 4px;">
                            💬 "<?= htmlspecialchars($activity['notes']) ?>"
                        </div>
                        <?php endif; ?>
                        <div style="font-size: 0.75rem; color: #999; margin-top: 5px;">
                            🕐 <?= DateHelper::diffHuman($activity['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 📋 RESERVASI TERBARU -->
<div class="content-card">
    <h3>📋 Reservasi Terbaru</h3>
    
    <?php if (empty($recent_reservations)): ?>
        <p style="text-align: center; color: #999; padding: 40px;">Belum ada data reservasi.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Customer</th>
                    <th>Lapangan</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Status Workflow</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($recent_reservations, 0, 10) as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['court_name']) ?></td>
                    <td><?= DateHelper::format($r['start_time'], 'd M Y H:i') ?></td>
                    <td><?= DateHelper::format($r['end_time'], 'H:i') ?></td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; white-space: nowrap;
                              background: <?= match($r['workflow_status']) {
                                  'pending_manager' => '#FFC107',
                                  'approved' => '#4CAF50',
                                  'pending_reschedule' => '#FF9800',
                                  'rejected' => '#F44336',
                                  default => '#9E9E9E'
                              } ?>30;
                              color: <?= match($r['workflow_status']) {
                                  'pending_manager' => '#F57F17',
                                  'approved' => '#2E7D32',
                                  'pending_reschedule' => '#E65100',
                                  'rejected' => '#C62828',
                                  default => '#424242'
                              } ?>;">
                            <?= strtoupper(str_replace('_', ' ', $r['workflow_status'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $r['status'] === 'aktif' ? 'active' : 'completed' ?>">
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ⚡ QUICK ACTIONS -->
<div class="content-card">
    <h3>⚡ Quick Actions</h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;">
        <a href="<?= $base_url ?>?c=reservation&a=create" 
           style="padding: 12px 24px; background: #9c27b0; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            ➕ Buat Reservasi Baru
        </a>
        <a href="<?= $base_url ?>?c=reservation&a=index" 
           style="padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            📋 Lihat Semua Reservasi
        </a>
        <?php if (!empty($need_reschedule)): ?>
        <a href="<?= $base_url ?>?c=reservation&a=index" 
           style="padding: 12px 24px; background: #FF9800; color: white; text-decoration: none; border-radius: 8px; display: inline-block;">
            ⚠️ Perlu Reschedule (<?= count($need_reschedule) ?>)
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ℹ️ INFORMASI -->
<div class="content-card">
    <h3>ℹ️ Informasi & Panduan</h3>
    <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;">
        <p style="color: #666; line-height: 1.6; margin-bottom: 15px;">
            <strong>Sebagai Staff, Anda dapat:</strong>
        </p>
        <ul style="color: #666; line-height: 2; margin-left: 20px;">
            <li>✅ Membuat reservasi baru untuk pelanggan</li>
            <li>✅ Melihat daftar semua reservasi</li>
            <li>✅ Mengubah jadwal reservasi (jika diminta manager)</li>
            <li>✅ Meminta pembatalan reservasi yang sudah disetujui</li>
            <li>✅ Mencari reservasi berdasarkan nama atau lapangan</li>
            <li>❌ Tidak dapat langsung approve reservasi (perlu approval manager)</li>
            <li>❌ Tidak dapat mengelola lapangan</li>
            <li>❌ Tidak dapat mengelola user lain</li>
        </ul>
        
        <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 6px;">
            <strong style="color: #FF9800;">💡 Tips:</strong>
            <ul style="margin-top: 10px; margin-left: 20px; color: #666;">
                <li>Selalu cek notifikasi untuk update dari Manager</li>
                <li>Jika reservasi ditolak atau perlu reschedule, segera lakukan perubahan</li>
                <li>Berikan catatan yang jelas saat membuat atau mengubah reservasi</li>
            </ul>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>