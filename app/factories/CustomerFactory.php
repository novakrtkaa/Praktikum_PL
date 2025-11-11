<?php

class CustomerFactory
{
    protected static array $firstNames = [
        'Andi','Budi','Citra','Dewi','Eka','Farhan','Gita','Hendra','Indah','Joko',
        'Lina','Maya','Nadia','Oscar','Putri','Rafi','Sari','Tono','Umi','Vina'
    ];

    protected static array $lastNames = [
        'Santoso','Rahman','Putri','Saputra','Wijaya','Hidayat','Lestari','Gunawan','Pratama','Wicaksono'
    ];

    public static function create(int $i = 0): array
    {
        $first = self::$firstNames[array_rand(self::$firstNames)];
        $last = self::$lastNames[array_rand(self::$lastNames)];
        $name = "{$first} {$last}";
        $email = strtolower($first) . ($i + 1) . '@example.com';
        $phone = '08' . strval(rand(100000000, 999999999));
        return [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
        ];
    }

    public static function createMany(int $count = 50): array
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = self::create($i);
        }
        return $out;
    }
}
