<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hermano extends Model
{
    protected $fillable = [
        'numero_hermano',
        'nombre',
        'apellidos',
        'dni',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'telefono',
        'email',
        'banco_id',
        'sucursal',
        'iban',
        'titular_cuenta',
        'titular_cuenta_menor',
        'fecha_alta',
        'fecha_baja',
        'fecha_bautismo',
        'parroquia_bautismo',
        'estado',
        'es_cabeza_familia',
        'estado_cuota',
        'cuota_pendiente_ejercicio_id',
        'cuenta_contable_id',
        'beneficiario_fiscal_hermano_id',
        'periodicidad_pago',
        'importe_cuota_anual_referencia',
        'periodos_cuota_cobrados_json',
        'observaciones',
        'partida_bautismo_path',
        'dni_escaneado_path',
        'rgpd_aceptado',
        'rgpd_fecha',
        'rgpd_ip',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'fecha_bautismo' => 'date',
        'rgpd_aceptado' => 'boolean',
        'es_cabeza_familia' => 'boolean',
        'rgpd_fecha' => 'datetime',
        'importe_cuota_anual_referencia' => 'decimal:2',
        'periodos_cuota_cobrados_json' => 'array',
    ];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    public function cuotaPendienteEjercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'cuota_pendiente_ejercicio_id');
    }

    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_contable_id');
    }

    public function beneficiarioFiscal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'beneficiario_fiscal_hermano_id');
    }

    public function deducidosDeFamilia(): HasMany
    {
        return $this->hasMany(self::class, 'beneficiario_fiscal_hermano_id');
    }

    public function portalCuenta(): HasOne
    {
        return $this->hasOne(HermanoPortalCuenta::class);
    }

    public function solicitudesCambioDatos(): HasMany
    {
        return $this->hasMany(SolicitudCambioDatos::class);
    }

    public function remesaRecibos(): HasMany
    {
        return $this->hasMany(RemesaRecibo::class);
    }

    public function avisoDestinatarios(): HasMany
    {
        return $this->hasMany(AvisoHermano::class);
    }

    public function loteriaAsignaciones(): HasMany
    {
        return $this->hasMany(LoteriaAsignacion::class);
    }

    public function papeletas(): HasMany
    {
        return $this->hasMany(PapeletaSitio::class);
    }

    public function comunicadosMasivosRecibidos(): HasMany
    {
        return $this->hasMany(ComunicadoMasivoDestinatario::class);
    }

    public function solicitudesFirmaConformidad(): HasMany
    {
        return $this->hasMany(FirmaConformidadSolicitud::class);
    }

    public function tunicas(): HasMany
    {
        return $this->hasMany(Tunica::class);
    }

    public function costaleroPerfil(): HasOne
    {
        return $this->hasOne(CostaleroPerfil::class);
    }

    public function asistenciasEnsayo(): HasMany
    {
        return $this->hasMany(EnsayoAsistencia::class);
    }

    public function nombreCompleto(): string
    {
        return trim($this->apellidos.', '.$this->nombre);
    }

    public function deudaLoteria(): float
    {
        return (float) $this->loteriaAsignaciones()
            ->where('cobrado', false)
            ->sum('importe_a_cobrar');
    }

    public function tieneDeuda(): bool
    {
        return $this->deudaLoteria() > 0;
    }

    public function tieneCuotaOrdinariaPendiente(): bool
    {
        return in_array($this->estado_cuota, ['Pendiente', 'Impagada'], true);
    }

    public function familias(): BelongsToMany
    {
        return $this->belongsToMany(Familia::class, 'familia_hermano')
            ->withPivot(['parentesco'])
            ->withTimestamps();
    }
}
