<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/factories/CourtFactory.php';
require_once __DIR__ . '/app/factories/CustomerFactory.php';
require_once __DIR__ . '/app/factories/ReservationFactory.php';

$config = require __DIR__ . '/config/config.php';
$db = new Database($config['db']);
$conn = $db->getConnection();

echo "🗑️  Menghapus data lama...\n";

mysqli_query($conn, "DELETE FROM reservations");
mysqli_query($conn, "DELETE FROM courts");

echo "🏸 Menambahkan data lapangan...\n";

$courts = [
    ['Lapangan A', 'Sintetis'],
    ['Lapangan B', 'Kayu'],
    ['Lapangan C', 'Semen'],
    ['Lapangan D', 'Vinyl'],
    ['Lapangan E', 'Karpet']
];

$courtIds = [];
foreach ($courts as $court) {
    $stmt = mysqli_prepare($conn, "INSERT INTO courts (name, type) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $court[0], $court[1]);
    mysqli_stmt_execute($stmt);
    $courtIds[] = mysqli_insert_id($conn);
    echo "  ✓ {$court[0]} - {$court[1]}\n";
    mysqli_stmt_close($stmt);
}

echo "\n👥 Menambahkan data reservasi...\n";

$customerNames = [
    'Andi Wijaya', 'Budi Santoso', 'Citra Dewi', 'Dedi Rahman', 'Eka Putri',
    'Farhan Hidayat', 'Gita Lestari', 'Hendra Gunawan', 'Indah Pratama', 'Joko Susilo',
    'Lina Marlina', 'Maya Sari', 'Nadia Putri', 'Oscar Wijaya', 'Putri Ayu',
    'Rafi Rahman', 'Sari Indah', 'Tono Sutrisno', 'Umi Kalsum', 'Vina Lestari'
];

$statuses = ['aktif', 'selesai'];
$reservationCount = 0;

for ($i = 0; $i < 20; $i++) {
    $daysAhead = rand(0, 30);
    $timestamp = strtotime("+{$daysAhead} days");

    $dayOfWeek = date('N', $timestamp);
    if ($dayOfWeek >= 6) continue;
    
    $date = date('Y-m-d', $timestamp);

    $hour = rand(8, 16);
    $minute = (rand(0, 1) === 0) ? '00' : '30';
    $startTime = "{$date} {$hour}:{$minute}:00";

    $duration = [30, 60, 90][rand(0, 2)];
    $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($duration * 60));

    if (strtotime($endTime) > strtotime("{$date} 17:00:00")) {
        continue;
    }

    $customerName = $customerNames[array_rand($customerNames)];
    $courtId = $courtIds[array_rand($courtIds)];
    $status = $statuses[array_rand($statuses)];
    
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO reservations (customer_name, court_id, start_time, end_time, status) 
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sisss", $customerName, $courtId, $startTime, $endTime, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $reservationCount++;
        echo "  ✓ {$customerName} - {$startTime} ({$duration} min) - {$status}\n";
    }
    
    mysqli_stmt_close($stmt);
}

echo "\n✅ Seeding selesai!\n";
echo "📊 Summary:\n";
echo "  - Lapangan: " . count($courtIds) . "\n";
echo "  - Reservasi: {$reservationCount}\n";
echo "\n🚀 Aplikasi siap digunakan!\n";

mysqli_close($conn);