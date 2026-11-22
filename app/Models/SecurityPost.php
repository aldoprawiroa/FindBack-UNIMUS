<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_active'])]
class SecurityPost extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Pengguna atau petugas keamanan yang ditugaskan di pos ini.
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }
}
