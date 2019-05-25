<?php

namespace Moecasts\Laravel\Wallet;

class WalletProxy
{

    /**
     * @var array
     */
    protected static $data = [];

    /**
     * @param int $key
     * @return bool
     */
    public static function has(int $key): bool
    {
        return \array_key_exists($key, static::$data);
    }

    /**
     * @param int $key
     * @return int
     */
    public static function get(int $key): float
    {
        return (float) (static::$data[$key] ?? 0);
    }

    /**
     * @param int $key
     * @param int $value
     * @return bool
     */
    public static function set(int $key, float $value): bool
    {
        static::$data[$key] = $value;
        return true;
    }

    /**
     * @return void
     */
    public static function fresh(): void
    {
        static::$data = [];
    }

}
