<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $setting = static::where('key', $key)->first();
            if ($setting && ! empty($setting->value)) {
                return $setting->value;
            }
        } catch (\Throwable $e) {
            // DB not reachable during build/testing
        }

        $configMap = [
            'company_name' => 'vaishnavi.business_name',
            'phone_primary' => 'vaishnavi.phone_primary',
            'phone_secondary' => 'vaishnavi.phone_secondary',
            'whatsapp_number' => 'vaishnavi.whatsapp',
            'address' => 'vaishnavi.address',
            'address_line_1' => 'vaishnavi.address',
            'city' => 'vaishnavi.city',
            'state' => 'vaishnavi.state',
            'contact_email' => 'vaishnavi.contact_email',
            'support_email' => 'vaishnavi.support_email',
        ];

        if (isset($configMap[$key])) {
            return config($configMap[$key], $default);
        }

        return $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
