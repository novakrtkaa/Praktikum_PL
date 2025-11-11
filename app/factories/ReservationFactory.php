<?php

class ReservationFactory
{
    protected $conn;
    protected array $customerNames;
    protected array $courtIds;

    public function __construct($conn, array $customerNames = [], array $courtIds = [])
    {
        $this->conn = $conn;
        $this->customerNames = $customerNames;
        $this->courtIds = $courtIds;
        
        if (empty($this->customerNames)) {
            $this->customerNames = [
                'Andi Wijaya', 'Budi Santoso', 'Citra Dewi', 'Dedi Rahman', 'Eka Putri',
                'Farhan Hidayat', 'Gita Lestari', 'Hendra Gunawan', 'Indah Pratama', 'Joko Susilo'
            ];
        }
    }

    public function create(): ?int
    {
        if (empty($this->courtIds)) {
            echo "Error: Court IDs kosong!\n";
            return null;
        }

        $customerName = $this->customerNames[array_rand($this->customerNames)];
        $courtId = (int) $this->courtIds[array_rand($this->courtIds)];

        $tries = 0;
        do {
            $offsetDays = rand(0, 30);
            $ts = strtotime("+{$offsetDays} days");
            $weekday = (int) date('N', $ts);
            $tries++;
            if ($tries > 60) break;
        } while ($weekday >= 6);

        $date = date('Y-m-d', $ts);

        $possibleStarts = [];
        for ($hour = 8; $hour <= 16; $hour++) {
            $possibleStarts[] = sprintf('%02d:00:00', $hour);
            $possibleStarts[] = sprintf('%02d:30:00', $hour);
        }
        $startTimePart = $possibleStarts[array_rand($possibleStarts)];
        $start = $date . ' ' . $startTimePart;

        $durations = [30, 45, 60];
        shuffle($durations);
        $chosen = null;
        foreach ($durations as $d) {
            $endTs = strtotime($start) + $d * 60;
            if ((int) date('H', $endTs) < 17 || ((int)date('H', $endTs) === 17 && (int)date('i', $endTs) === 0)) {
                $chosen = $d;
                break;
            }
        }
        if ($chosen === null) {
            $chosen = 30;
        }

        $end = date('Y-m-d H:i:s', strtotime($start) + $chosen * 60);
        $start = date('Y-m-d H:i:s', strtotime($start));

        $status = (rand(0, 1) === 0) ? 'aktif' : 'selesai';

        $startEsc = mysqli_real_escape_string($this->conn, $start);
        $endEsc = mysqli_real_escape_string($this->conn, $end);
        $nameEsc = mysqli_real_escape_string($this->conn, $customerName);
        $statusEsc = mysqli_real_escape_string($this->conn, $status);
        
        $sql = "INSERT INTO reservations (customer_name, court_id, start_time, end_time, status) 
                VALUES ('{$nameEsc}', {$courtId}, '{$startEsc}', '{$endEsc}', '{$statusEsc}')";
        
        $res = mysqli_query($this->conn, $sql);
        
        if ($res) {
            return mysqli_insert_id($this->conn);
        }

        return null;
    }

    public function createMany(int $count = 10): array
    {
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $id = $this->create();
            if ($id) $ids[] = $id;
        }
        return $ids;
    }

    public function createForCustomer(string $customerName, int $count = 5): array
    {
        $orig = $this->customerNames;
        $this->customerNames = [$customerName];
        $ids = $this->createMany($count);
        $this->customerNames = $orig;
        return $ids;
    }

    public function createForCourt(int $courtId, int $count = 5): array
    {
        $orig = $this->courtIds;
        $this->courtIds = [$courtId];
        $ids = $this->createMany($count);
        $this->courtIds = $orig;
        return $ids;
    }

    public function createInRange(string $startDate, string $endDate, int $count = 10): array
    {
        $sTs = strtotime($startDate);
        $eTs = strtotime($endDate);
        if ($sTs === false || $eTs === false || $sTs > $eTs) return [];

        $ids = [];
        $maxLoop = $count * 5;
        $loops = 0;
        
        while (count($ids) < $count && $loops < $maxLoop) {
            $loops++;
            $randTs = rand($sTs, $eTs);
            
            $weekday = (int) date('N', $randTs);
            if ($weekday >= 6) {
                $randTs = strtotime('next monday', $randTs);
                if ($randTs > $eTs) continue;
            }
            
            $date = date('Y-m-d', $randTs);
            $hour = rand(8, 16);
            $minute = (rand(0, 1) === 0) ? 0 : 30;
            $start = sprintf('%s %02d:%02d:00', $date, $hour, $minute);

            $dur = [30, 45, 60][array_rand([0,1,2])];
            $endTs = strtotime($start) + $dur * 60;
            if ((int)date('H', $endTs) > 17 || ((int)date('H', $endTs) === 17 && (int)date('i', $endTs) > 0)) {
                continue;
            }

            $startEsc = mysqli_real_escape_string($this->conn, date('Y-m-d H:i:s', strtotime($start)));
            $endEsc = mysqli_real_escape_string($this->conn, date('Y-m-d H:i:s', $endTs));
            
            $customerName = $this->customerNames[array_rand($this->customerNames)];
            $nameEsc = mysqli_real_escape_string($this->conn, $customerName);
            
            $court = (int) $this->courtIds[array_rand($this->courtIds)];
            $status = (rand(0, 1) === 0) ? 'aktif' : 'selesai';
            
            $sql = "INSERT INTO reservations (customer_name, court_id, start_time, end_time, status) 
                    VALUES ('{$nameEsc}', {$court}, '{$startEsc}', '{$endEsc}', '{$status}')";
            
            $res = mysqli_query($this->conn, $sql);
            if ($res) $ids[] = mysqli_insert_id($this->conn);
        }

        return $ids;
    }
}