<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public static function roles(): array
    {
        return ['admin', 'employee', 'customer'];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Derive first name from the full name for convenience in forms.
     */
    public function getFirstNameAttribute(): ?string
    {
        $name = (string) ($this->attributes['name'] ?? '');
        $parts = preg_split('/\s+/', trim($name));
        return $parts[0] ?? null;
    }

    /**
     * Derive last name from the full name for convenience in forms.
     */
    public function getLastNameAttribute(): ?string
    {
        $name = (string) ($this->attributes['name'] ?? '');
        $parts = preg_split('/\s+/', trim($name));
        if (!$parts || count($parts) < 2) {
            return null;
        }
        array_shift($parts);
        return implode(' ', $parts);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
