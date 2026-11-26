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

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SECURITY = 'security';

    public const ROLE_MAHASISWA = 'mahasiswa';

    public const ROLE_DOSEN = 'dosen';

    /** @var list<string> */
    public const STAFF_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SECURITY,
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
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSecurity(): bool
    {
        return $this->role === self::ROLE_SECURITY;
    }

    public function isMahasiswa(): bool
    {
        return $this->role === self::ROLE_MAHASISWA;
    }

    public function isDosen(): bool
    {
        return $this->role === self::ROLE_DOSEN;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function canManageReturns(): bool
    {
        return $this->hasRole(...self::STAFF_ROLES);
    }

    public function assignSecurityPost(SecurityPost|int $securityPost): static
    {
        $this->security_post_id = $securityPost instanceof SecurityPost
            ? $securityPost->id
            : $securityPost;

        $this->save();

        return $this;
    }

    public function unassignSecurityPost(): static
    {
        $this->security_post_id = null;
        $this->save();

        return $this;
    }

    public function hasAssignedSecurityPost(): bool
    {
        return $this->security_post_id !== null;
    }

    public function isAssignedTo(SecurityPost|int $securityPost): bool
    {
        $postId = $securityPost instanceof SecurityPost ? $securityPost->id : $securityPost;

        return $this->security_post_id === $postId;
    }
}
