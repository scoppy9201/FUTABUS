<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailSetting extends Model
{
    protected $fillable = [
        'mail_mailer', 'mail_host', 'mail_port',
        'mail_username', 'mail_password', 'mail_encryption',
        'mail_from_address', 'mail_from_name', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mail_port' => 'integer',
    ];

    // Encrypt mật khẩu khi lưu
    public function setMailPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['mail_password'] = Crypt::encryptString($value);
        }
    }

    // Decrypt mật khẩu khi đọc
    public function getMailPasswordAttribute($value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // Lấy setting đang active (singleton)
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
