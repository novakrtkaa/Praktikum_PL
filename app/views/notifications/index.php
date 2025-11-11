<!-- File: app/views/notifications/index.php -->
<?php $title = 'Notifikasi'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>🔔 Notifikasi</h1>
            <p>Semua notifikasi sistem Anda</p>
        </div>
        
        <?php if (!empty($notifications)): ?>
        <form method="POST" action="<?= $base_url ?>?c=notification&a=markAllAsRead">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <button type="submit" 
                    style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                ✓ Tandai Semua Dibaca
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($notifications)): ?>
    <div class="content-card">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">🔔</div>
            <h3 style="color: #999; margin-bottom: 10px;">Tidak Ada Notifikasi</h3>
            <p style="color: #bbb;">Anda akan menerima notifikasi ketika ada aktivitas baru</p>
        </div>
    </div>
<?php else: ?>
    <div class="content-card">
        <?php foreach ($notifications as $notif): ?>
        <div style="background: <?= $notif->isRead() ? '#fff' : '#e3f2fd' ?>; 
                    padding: 20px; 
                    margin-bottom: 15px; 
                    border-radius: 8px; 
                    border-left: 4px solid <?= 
                        match($notif->getType()) {
                            'new_reservation' => '#2196F3',
                            'reservation_approved' => '#4CAF50',
                            'rescheduled' => '#FF9800',
                            'reschedule_requested' => '#FF9800',
                            'cancel_requested' => '#F44336',
                            'cancel_approved' => '#9E9E9E',
                            'reservation_rejected' => '#F44336',
                            default => '#9E9E9E'
                        }
                    ?>;">
            
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                <!-- Icon -->
                <div style="font-size: 2rem; flex-shrink: 0;">
                    <?= match($notif->getType()) {
                        'new_reservation' => '📝',
                        'reservation_approved' => '✅',
                        'rescheduled' => '📅',
                        'reschedule_requested' => '⚠️',
                        'cancel_requested' => '🗑️',
                        'cancel_approved' => '✓',
                        'reservation_rejected' => '❌',
                        default => '🔔'
                    } ?>
                </div>
                
                <!-- Content -->
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <h4 style="margin: 0; color: #333; font-size: 1.1rem;">
                            <?= htmlspecialchars($notif->getTitle()) ?>
                        </h4>
                        <?php if (!$notif->isRead()): ?>
                        <span style="background: #FF5252; color: white; font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; font-weight: bold;">
                            BARU
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <p style="color: #666; margin: 10px 0; line-height: 1.6;">
                        <?= htmlspecialchars($notif->getMessage()) ?>
                    </p>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                        <span style="color: #999; font-size: 0.85rem;">
                            🕐 <?= DateHelper::diffHuman($notif->getCreatedAt()) ?>
                        </span>
                        
                        <?php if ($notif->getLink()): ?>
                        <a href="<?= $base_url . $notif->getLink() ?>" 
                           style="color: #2196F3; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                            👁️ Lihat Detail →
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                    <?php if (!$notif->isRead()): ?>
                    <a href="<?= $base_url ?>?c=notification&a=markAsRead&id=<?= $notif->getId() ?>" 
                       style="padding: 8px 16px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 0.85rem; text-align: center; white-space: nowrap;">
                        ✓ Tandai Dibaca
                    </a>
                    <?php else: ?>
                    <div style="padding: 8px 16px; background: #E0E0E0; color: #666; border-radius: 5px; font-size: 0.85rem; text-align: center;">
                        ✓ Dibaca
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
    @media (max-width: 768px) {
        .content-card > div {
            flex-direction: column;
        }
        
        .content-card > div > div:last-child {
            flex-direction: row;
        }
    }
</style>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>