<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Auth\Models\AdminProfile;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Modules\Auth\Enums\UserRoleEnum;
use Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole(UserRoleEnum::ADMIN->value);
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
