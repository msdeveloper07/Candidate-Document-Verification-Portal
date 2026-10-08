<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

class OtpCode extends Model
{
    protected $fillable = [
        'candidate_id', 'dial_code', 'phone', 'code_hash', 'purpose',
        'channel', 'attempts', 'expires_at', 'verified_at',
        'ip_address', 'user_agent',
    ];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'expires_at'  => 'datetime',
            'verified_at' => 'datetime',
            'attempts'    => 'integer',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return filled($this->verified_at);
    }

    public function matches(string $plain): bool
    {
        return Hash::check($plain, $this->code_hash);
    }

    public function scopeUsable($query)
    {
        return $query->whereNull('verified_at')->where('expires_at', '>', now());
    }
}
