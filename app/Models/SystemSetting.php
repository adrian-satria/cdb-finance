<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $primaryKey = 'id_setting';

    protected $fillable = ['key', 'value', 'group', 'label', 'type', 'options'];

    protected $casts = ['options' => 'array'];

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
