<?php

class CourtFactory
{
    protected static array $names = [
        'Lapangan A','Lapangan B','Lapangan C','Lapangan D','Lapangan E',
        'Lapangan F','Lapangan G'
    ];

    public static function create(int $i = 0): array
    {
        $name = self::$names[$i % count(self::$names)];
        $type = 'Indoor';
        return [
            'name' => $name,
            'type' => $type,
        ];
    }

    public static function createMany(int $count = 5): array
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) $out[] = self::create($i);
        return $out;
    }
}
