<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'security_post_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'security_post_id' => 'integer',
        ];
    }

    /**
     * Pos satpam tempat pengguna atau admin ditugaskan.
     */
    public function securityPost(): BelongsTo
    {
        return $this->belongsTo(SecurityPost::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSecurity(): bool
    {
        return $this->role === 'security';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function canManageReturns(): bool
    {
        return $this->hasRole('admin', 'security');
    }
}
