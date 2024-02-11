<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\EmailUpdated;
use App\Models\Concerns\HasVirtualColumns;
use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Order;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketTransfer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $display_name
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, HasRoles, HasVirtualColumns, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legal_name',
        'preferred_name',
        'email',
        'password',
        'birthday',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthday' => 'date:Y-m-d',
    ];

    /**
     * The virtual generated columns on the model
     *
     * @var array<int, string>
     */
    protected $virtualColumns = [
        'display_name',
    ];

    protected static function booted(): void
    {
        // Send a new verification email when the email address changes
        static::updating(function (User $user) {
            if ($user->isDirty('email') && $user->hasVerifiedEmail()) {
                $user->email_verified_at = null;
                event(new EmailUpdated($user));
            }
        });
    }

    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    public function reservedTickets(): HasMany
    {
        return $this->hasMany(ReservedTicket::class);
    }

    public function availableReservedTickets(): HasMany
    {
        return $this->hasMany(ReservedTicket::class)
            ->where(fn ($query) => $query->canBePurchased());
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)
            ->where(fn ($query) => $query->notExpired());
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(CompletedWaiver::class);
    }

    public function ticketTransfers(): HasMany
    {
        return $this->hasMany(TicketTransfer::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): int
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return mixed[]
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
