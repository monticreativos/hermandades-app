<?php

namespace App\Models;

use App\Notifications\PortalVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HermanoPortalCuenta extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyEmailTrait;
    use Notifiable;

    protected $table = 'hermano_portal_cuentas';

    protected $fillable = [
        'hermano_id',
        'email',
        'email_verified_at',
        'password',
        'activacion_token_hash',
        'activacion_expira',
        'activacion_codigo_hash',
        'activacion_codigo_expira',
        'recuperacion_codigo_hash',
        'recuperacion_expira',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activacion_token_hash',
        'recuperacion_codigo_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activacion_expira' => 'datetime',
            'activacion_codigo_expira' => 'datetime',
            'recuperacion_expira' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function solicitudesCambioDatos(): HasMany
    {
        return $this->hasMany(SolicitudCambioDatos::class, 'hermano_portal_cuenta_id');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new PortalVerifyEmail);
    }

    public function tieneActivacionPendiente(): bool
    {
        return $this->password === null || $this->password === '';
    }
}
