<?php

use App\Http\Controllers\Ajustes\ActividadRegistroController;
use App\Http\Controllers\Ajustes\AuditoriaLogController;
use App\Http\Controllers\Ajustes\EstadoSistemaController;
use App\Http\Controllers\Ajustes\RecalcularNumerosHermanosController;
use App\Http\Controllers\Ajustes\SincronizarCuentasAuxiliaresController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CategoriaPatrimonioController;
use App\Http\Controllers\ComunicadoMasivoTrackController;
use App\Http\Controllers\ConfiguracionHermandadController;
use App\Http\Controllers\CuadrillaController;
use App\Http\Controllers\CuadrillaRelevoPdfController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Economia\AnalisisDeudaController;
use App\Http\Controllers\Economia\AsientoIAGeneracionController;
use App\Http\Controllers\Economia\ArqueoTesoreriaController;
use App\Http\Controllers\Economia\AsientoController;
use App\Http\Controllers\Economia\CuentaContableController;
use App\Http\Controllers\Economia\DashboardEconomiaController;
use App\Http\Controllers\Economia\EconomiaCuotasController;
use App\Http\Controllers\Economia\FacturasController;
use App\Http\Controllers\Economia\InformeContableController;
use App\Http\Controllers\Economia\InformeHistorialEconomiaController;
use App\Http\Controllers\Economia\LibroDiarioController;
use App\Http\Controllers\Economia\LoteriaController;
use App\Http\Controllers\Economia\MovimientoRapidoController;
use App\Http\Controllers\Economia\ProveedorController;
use App\Http\Controllers\Economia\RemesaSepaController;
use App\Http\Controllers\EnserController;
use App\Http\Controllers\EstadoConservacionPatrimonioController;
use App\Http\Controllers\HermanoController;
use App\Http\Controllers\HermanoFamiliaController;
use App\Http\Controllers\HermanoExtractoContablePdfController;
use App\Http\Controllers\HermanoPortalInvitacionController;
use App\Http\Controllers\Informes\CensoElectoralController;
use App\Http\Controllers\Informes\EstadisticasController;
use App\Http\Controllers\Informes\EtiquetasController;
use App\Http\Controllers\Informes\HermanoCertificadoController;
use App\Http\Controllers\Informes\InformesPanelController;
use App\Http\Controllers\Informes\ListadosPersonalizadosController;
use App\Http\Controllers\Portal\PortalActivacionController;
use App\Http\Controllers\Portal\PortalAuthenticatedSessionController;
use App\Http\Controllers\Portal\PortalAvisoLeidoController;
use App\Http\Controllers\Portal\PortalBizumCuotaController;
use App\Http\Controllers\Portal\PortalCertificadoHaciendaController;
use App\Http\Controllers\Portal\PortalCuadrillaController;
use App\Http\Controllers\Portal\PortalDocumentoArchivoDescargaController;
use App\Http\Controllers\Portal\PortalDocumentosController;
use App\Http\Controllers\Portal\PortalEmailVerificationController;
use App\Http\Controllers\Portal\PortalFirmaConformidadController;
use App\Http\Controllers\Portal\PortalInicioController;
use App\Http\Controllers\Portal\PortalNotificacionesController;
use App\Http\Controllers\Portal\PortalPagosController;
use App\Http\Controllers\Portal\PortalPapeletaInfoController;
use App\Http\Controllers\Portal\PortalPapeletaPdfController;
use App\Http\Controllers\Portal\PortalPerfilController;
use App\Http\Controllers\Portal\PortalRecuperacionContrasenaController;
use App\Http\Controllers\Portal\PortalRgpdController;
use App\Http\Controllers\Portal\PortalSolicitudCambioController;
use App\Http\Controllers\Portal\PortalTiendaController;
use App\Http\Controllers\Portal\PortalVerifyEmailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Salida\ConfiguracionSalidaController;
use App\Http\Controllers\Salida\CortejoController;
use App\Http\Controllers\Salida\InsigniaController;
use App\Http\Controllers\Salida\PapeletaController;
use App\Http\Controllers\Salida\PapeletaPdfController;
use App\Http\Controllers\Salida\TunicaController;
use App\Http\Controllers\SearchGlobalController;
use App\Http\Controllers\Secretaria\ArchivoDigitalController;
use App\Http\Controllers\Secretaria\AvisoController;
use App\Http\Controllers\Secretaria\ComunicadoMasivoController;
use App\Http\Controllers\Secretaria\DirectorioContactosController;
use App\Http\Controllers\Secretaria\FirmaConformidadSolicitudController;
use App\Http\Controllers\Secretaria\PlantillaDocumentalController;
use App\Http\Controllers\Secretaria\RegistroDocumentalController;
use App\Http\Controllers\Secretaria\RelacionesInstitucionalesController;
use App\Http\Controllers\Secretaria\SolicitudCambioDatosController;
use App\Http\Controllers\Tienda\AperturaCajaTiendaController;
use App\Http\Controllers\Tienda\CategoriaTiendaController;
use App\Http\Controllers\Tienda\CierreCajaTiendaController;
use App\Http\Controllers\Tienda\InformesTiendaController;
use App\Http\Controllers\Tienda\ProductoTiendaController;
use App\Http\Controllers\Tienda\TpvApiController;
use App\Http\Controllers\Tienda\TpvController;
use App\Http\Controllers\Tienda\VentasDiaTiendaController;
use App\Http\Controllers\Tienda\VentaTiendaTicketPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    $config = \App\Models\ConfiguracionHermandad::query()->first();
    $nombreHermandad = $config?->nombre_corto
        ?: $config?->nombre_hermandad
        ?: config('app.name', 'GestaHer');

    return view('home', ['nombreHermandad' => $nombreHermandad]);
})->name('home');

Route::get('/track/comunicado/{token}', ComunicadoMasivoTrackController::class)
    ->name('comunicados.track');

Route::prefix('portal')->name('portal.')->group(function (): void {
    Route::middleware('guest:portal')->group(function (): void {
        Route::get('login', [PortalAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [PortalAuthenticatedSessionController::class, 'store']);
        Route::get('activar/{token}', [PortalActivacionController::class, 'show'])
            ->where('token', '[A-Za-z0-9]{64}')
            ->name('activar.show');
        Route::post('activar', [PortalActivacionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('activar.store');
        Route::get('activacion/codigo', [PortalActivacionController::class, 'createPorCodigo'])->name('activacion.codigo');
        Route::post('activacion/codigo', [PortalActivacionController::class, 'storePorCodigo'])
            ->middleware('throttle:10,1')
            ->name('activacion.codigo.store');
        Route::get('recuperar-contrasena', [PortalRecuperacionContrasenaController::class, 'create'])->name('recuperar.request');
        Route::post('recuperar-contrasena', [PortalRecuperacionContrasenaController::class, 'store'])->name('recuperar.email');
    });

    Route::get('recuperar-contrasena/codigo', [PortalRecuperacionContrasenaController::class, 'showCodigo'])->name('recuperar.codigo');
    Route::post('recuperar-contrasena/codigo', [PortalRecuperacionContrasenaController::class, 'restablecer'])
        ->middleware('throttle:10,1')
        ->name('recuperar.restablecer');

    Route::get('verify-email/{id}/{hash}', PortalVerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::middleware('auth:portal')->group(function (): void {
        Route::post('logout', [PortalAuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('verify-email', [PortalEmailVerificationController::class, 'notice'])->name('verification.notice');
        Route::post('email/verification-notification', [PortalEmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    Route::middleware(['auth:portal', 'portal.verified'])->group(function (): void {
        Route::get('proteccion-datos', [PortalRgpdController::class, 'show'])->name('rgpd.show');
        Route::post('proteccion-datos', [PortalRgpdController::class, 'accept'])->name('rgpd.accept');
    });

    Route::middleware(['auth:portal', 'portal.verified', 'portal.rgpd'])->group(function (): void {
        Route::get('/', PortalInicioController::class)->name('inicio');
        Route::get('perfil', [PortalPerfilController::class, 'show'])->name('perfil.index');
        Route::get('perfil/solicitud-cambio', [PortalSolicitudCambioController::class, 'create'])->name('perfil.solicitud.create');
        Route::post('perfil/solicitud-cambio', [PortalSolicitudCambioController::class, 'store'])->name('perfil.solicitud.store');
        Route::get('pagos', PortalPagosController::class)->name('pagos.index');
        Route::post('pagos/bizum/cuota', PortalBizumCuotaController::class)->name('pagos.bizum.cuota');
        Route::get('notificaciones', PortalNotificacionesController::class)->name('notificaciones.index');
        Route::post('avisos-recibidos/{avisoHermano}/leer', PortalAvisoLeidoController::class)->name('avisos-recibidos.leer');
        Route::get('papeleta/sitio', PortalPapeletaInfoController::class)->name('papeleta.info');
        Route::get('papeletas/{papeleta}/pdf', PortalPapeletaPdfController::class)->name('papeletas.pdf');
        Route::get('documentos', PortalDocumentosController::class)->name('documentos.index');
        Route::get('documentos/certificado-hacienda.pdf', PortalCertificadoHaciendaController::class)->name('documentos.certificado-hacienda');
        Route::get('documentos/archivo/{documento}/descargar', PortalDocumentoArchivoDescargaController::class)->name('documentos.archivo.descargar');
        Route::get('firmas/{solicitud}', [PortalFirmaConformidadController::class, 'show'])->name('firmas.show');
        Route::post('firmas/{solicitud}/firmar', [PortalFirmaConformidadController::class, 'firmar'])->name('firmas.firmar');

        Route::get('tienda', [PortalTiendaController::class, 'index'])->name('tienda.index');
        Route::post('tienda/carrito/vaciar', [PortalTiendaController::class, 'vaciar'])->name('tienda.carrito.vaciar');
        Route::post('tienda/carrito/{productoTienda}', [PortalTiendaController::class, 'agregar'])->name('tienda.carrito.agregar');
        Route::delete('tienda/carrito/{productoTienda}', [PortalTiendaController::class, 'quitar'])->name('tienda.carrito.quitar');
        Route::post('tienda/reservar', [PortalTiendaController::class, 'reservar'])
            ->middleware('throttle:15,1')
            ->name('tienda.reservar');
        Route::post('tienda/bizum', [PortalTiendaController::class, 'bizum'])
            ->middleware('throttle:10,1')
            ->name('tienda.bizum');
        Route::get('cuadrilla', PortalCuadrillaController::class)->name('cuadrilla.index');
    });
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/search/global', SearchGlobalController::class)
        ->name('search.global');

    Route::post('/bancos', [BancoController::class, 'store'])->name('bancos.store')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin');
    Route::put('/bancos/{banco}', [BancoController::class, 'update'])->name('bancos.update')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin');
    Route::delete('/bancos/{banco}', [BancoController::class, 'destroy'])->name('bancos.destroy')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin');

    Route::resource('hermanos', HermanoController::class)->except(['create', 'edit'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin');
    Route::get('/hermanos/{hermano}/documentos/{tipo}', [HermanoController::class, 'descargarDocumento'])
        ->whereIn('tipo', ['partida_bautismo', 'dni_escaneado'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.documentos.descargar');
    Route::get('/hermanos/{hermano}/extracto-contable.pdf', HermanoExtractoContablePdfController::class)
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.extracto-contable.pdf');
    Route::get('/hermanos/{hermano}/certificados/pertenencia', [HermanoCertificadoController::class, 'pertenencia'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.certificados.pertenencia');
    Route::get('/hermanos/{hermano}/certificados/cuotas-hacienda', [HermanoCertificadoController::class, 'cuotasHacienda'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.certificados.cuotas-hacienda');
    Route::post('/hermanos/{hermano}/portal/invitacion', [HermanoPortalInvitacionController::class, 'store'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.portal.invitacion');
    Route::post('/hermanos/{hermano}/familia', [HermanoFamiliaController::class, 'store'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.familia.store');
    Route::post('/hermanos/{hermano}/familia/configurar', [HermanoFamiliaController::class, 'configurar'])
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('hermanos.familia.configurar');

    Route::prefix('secretaria')
        ->name('secretaria.')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->group(function (): void {
            Route::get('/solicitudes-cambio', [SolicitudCambioDatosController::class, 'index'])->name('solicitudes-cambio.index');
            Route::get('/solicitudes-cambio/{solicitud}', [SolicitudCambioDatosController::class, 'show'])
                ->name('solicitudes-cambio.show');
            Route::post('/solicitudes-cambio/{solicitud}/aprobar', [SolicitudCambioDatosController::class, 'aprobar'])
                ->name('solicitudes-cambio.aprobar');
            Route::post('/solicitudes-cambio/{solicitud}/rechazar', [SolicitudCambioDatosController::class, 'rechazar'])
                ->name('solicitudes-cambio.rechazar');

            Route::get('/avisos', [AvisoController::class, 'index'])->name('avisos.index');
            Route::get('/avisos/nuevo', [AvisoController::class, 'create'])->name('avisos.create');
            Route::post('/avisos', [AvisoController::class, 'store'])->name('avisos.store');
            Route::get('/avisos/{aviso}', [AvisoController::class, 'show'])->name('avisos.show');

            Route::get('/comunicados-masivos', [ComunicadoMasivoController::class, 'index'])->name('comunicados-masivos.index');
            Route::get('/comunicados-masivos/nuevo', [ComunicadoMasivoController::class, 'create'])->name('comunicados-masivos.create');
            Route::post('/comunicados-masivos', [ComunicadoMasivoController::class, 'store'])->name('comunicados-masivos.store');
            Route::get('/comunicados-masivos/{comunicado}', [ComunicadoMasivoController::class, 'show'])->name('comunicados-masivos.show');

            Route::get('/archivo-digital', [ArchivoDigitalController::class, 'index'])->name('archivo-digital.index');
            Route::get('/archivo-digital/nuevo', [ArchivoDigitalController::class, 'create'])->name('archivo-digital.create');
            Route::post('/archivo-digital', [ArchivoDigitalController::class, 'store'])->name('archivo-digital.store');
            Route::get('/archivo-digital/{documento}/descargar', [ArchivoDigitalController::class, 'descargar'])->name('archivo-digital.descargar');
            Route::delete('/archivo-digital/{documento}', [ArchivoDigitalController::class, 'destroy'])->name('archivo-digital.destroy');
            Route::post('/archivo-digital/{documento}/resumir', [ArchivoDigitalController::class, 'resumir'])->name('archivo-digital.resumir');
            Route::post('/archivo-digital/{documento}/justificantes', [ArchivoDigitalController::class, 'vincularJustificante'])->name('archivo-digital.justificantes.store');

            Route::get('/firmas-conformidad', [FirmaConformidadSolicitudController::class, 'index'])->name('firmas-conformidad.index');
            Route::get('/firmas-conformidad/nueva', [FirmaConformidadSolicitudController::class, 'create'])->name('firmas-conformidad.create');
            Route::post('/firmas-conformidad', [FirmaConformidadSolicitudController::class, 'store'])->name('firmas-conformidad.store');
            Route::get('/firmas-conformidad/{solicitud}', [FirmaConformidadSolicitudController::class, 'show'])->name('firmas-conformidad.show');

            Route::get('/registro-documental', [RegistroDocumentalController::class, 'index'])->name('registro.index');
            Route::post('/registro-documental', [RegistroDocumentalController::class, 'store'])->name('registro.store');
            Route::post('/registro-documental/drop', [RegistroDocumentalController::class, 'storeDrop'])->name('registro.drop');
            Route::patch('/registro-documental/{registro}', [RegistroDocumentalController::class, 'update'])->name('registro.update');
            Route::get('/registro-documental/{registro}/ver', [RegistroDocumentalController::class, 'ver'])->name('registro.ver');
            Route::get('/registro-documental/{registro}/descargar', [RegistroDocumentalController::class, 'descargar'])->name('registro.descargar');

            Route::get('/plantillas-documentales', [PlantillaDocumentalController::class, 'index'])->name('plantillas.index');
            Route::post('/plantillas-documentales', [PlantillaDocumentalController::class, 'store'])->name('plantillas.store');
            Route::post('/plantillas-documentales/pdf', [PlantillaDocumentalController::class, 'generarPdf'])->name('plantillas.pdf');
            Route::post('/plantillas-documentales/previsualizar', [PlantillaDocumentalController::class, 'previsualizar'])->name('plantillas.preview');
            Route::post('/plantillas-documentales/importar-docx', [PlantillaDocumentalController::class, 'importarDocx'])->name('plantillas.importar-docx');

            Route::get('/relaciones-institucionales', [RelacionesInstitucionalesController::class, 'index'])->name('relaciones.index');
            Route::post('/relaciones-institucionales/entidades', [RelacionesInstitucionalesController::class, 'storeEntidad'])->name('relaciones.entidades.store');
            Route::post('/relaciones-institucionales/actos', [RelacionesInstitucionalesController::class, 'storeActo'])->name('relaciones.actos.store');
            Route::post('/relaciones-institucionales/invitaciones', [RelacionesInstitucionalesController::class, 'storeInvitacion'])->name('relaciones.invitaciones.store');
            Route::post('/relaciones-institucionales/invitaciones/categoria', [RelacionesInstitucionalesController::class, 'anadirCategoria'])->name('relaciones.invitaciones.categoria');

            Route::get('/directorio-contactos', [DirectorioContactosController::class, 'index'])->name('directorio.index');
            Route::post('/directorio-contactos', [DirectorioContactosController::class, 'store'])->name('directorio.store');
            Route::post('/directorio-contactos/quick', [DirectorioContactosController::class, 'storeQuick'])->name('directorio.quick');
        });

    Route::prefix('informes')
        ->name('informes.')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->group(function (): void {
            Route::get('/', [InformesPanelController::class, 'index'])->name('index');
            Route::get('/censo-electoral', [CensoElectoralController::class, 'index'])->name('censo.index');
            Route::get('/censo-electoral/pdf', [CensoElectoralController::class, 'pdf'])->name('censo.pdf');
            Route::get('/etiquetas', [EtiquetasController::class, 'index'])->name('etiquetas.index');
            Route::get('/etiquetas/pdf', [EtiquetasController::class, 'pdf'])->name('etiquetas.pdf');
            Route::get('/etiquetas/csv', [EtiquetasController::class, 'csv'])->name('etiquetas.csv');
            Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas.index');
            Route::get('/listados', [ListadosPersonalizadosController::class, 'index'])->name('listados.index');
            Route::post('/listados/export', [ListadosPersonalizadosController::class, 'export'])->name('listados.export');
        });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/ajustes/hermandad', [ConfiguracionHermandadController::class, 'index'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.index');
    Route::put('/ajustes/hermandad', [ConfiguracionHermandadController::class, 'update'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.update');
    Route::get('/ajustes/estado-sistema', [EstadoSistemaController::class, 'index'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.estado-sistema');
    Route::post('/ajustes/sincronizar-cuentas-auxiliares', [SincronizarCuentasAuxiliaresController::class, 'store'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.sincronizar-cuentas-auxiliares');
    Route::get('/ajustes/registro-actividad', [ActividadRegistroController::class, 'index'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.actividades.index');
    Route::get('/ajustes/auditoria', [AuditoriaLogController::class, 'index'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.auditoria.index');
    Route::get('/ajustes/renumeracion-hermanos', [RecalcularNumerosHermanosController::class, 'show'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.renumeracion.show');
    Route::post('/ajustes/renumeracion-hermanos', [RecalcularNumerosHermanosController::class, 'store'])
        ->middleware('role:Administrador Hermandad|SuperAdmin')
        ->name('ajustes.renumeracion.store');

    Route::resource('patrimonio', EnserController::class)
        ->except(['create', 'edit'])
        ->parameters(['patrimonio' => 'enser'])
        ->middleware('permission:patrimonio.gestion');
    Route::get('/patrimonio-galeria', [EnserController::class, 'galeria'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.galeria');

    Route::middleware('permission:cuadrillas.gestion')
        ->prefix('cuadrillas')
        ->name('cuadrillas.')
        ->group(function (): void {
            Route::get('/', [CuadrillaController::class, 'index'])->name('index');
            Route::post('/', [CuadrillaController::class, 'store'])->name('store');
            Route::get('/{cuadrilla}/iguala', [CuadrillaController::class, 'iguala'])->name('iguala');
            Route::post('/{cuadrilla}/iguala/asignar', [CuadrillaController::class, 'asignarCostalero'])->name('asignar');
            Route::get('/{cuadrilla}/ensayos', [CuadrillaController::class, 'ensayos'])->name('ensayos');
            Route::post('/{cuadrilla}/ensayos', [CuadrillaController::class, 'storeEnsayo'])->name('ensayos.store');
            Route::post('/{cuadrilla}/ensayos/{ensayo}/asistencia', [CuadrillaController::class, 'marcarAsistencia'])->name('ensayos.asistencia');
            Route::get('/{cuadrilla}/relevos', [CuadrillaController::class, 'relevos'])->name('relevos');
            Route::post('/{cuadrilla}/relevos', [CuadrillaController::class, 'storeRelevo'])->name('relevos.store');
            Route::post('/{cuadrilla}/relevos/{relevo}/detalle', [CuadrillaController::class, 'addDetalleRelevo'])->name('relevos.detalle.store');
            Route::get('/{cuadrilla}/relevos/{relevo}/pdf', CuadrillaRelevoPdfController::class)->name('relevos.pdf');
            Route::get('/{cuadrilla}/avisos', [CuadrillaController::class, 'avisos'])->name('avisos');
            Route::post('/{cuadrilla}/avisos', [CuadrillaController::class, 'storeAviso'])->name('avisos.store');
        });

    Route::post('/patrimonio/categorias', [CategoriaPatrimonioController::class, 'store'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.categorias.store');
    Route::put('/patrimonio/categorias/{categoriaPatrimonio}', [CategoriaPatrimonioController::class, 'update'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.categorias.update');
    Route::delete('/patrimonio/categorias/{categoriaPatrimonio}', [CategoriaPatrimonioController::class, 'destroy'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.categorias.destroy');

    Route::post('/patrimonio/estados-conservacion', [EstadoConservacionPatrimonioController::class, 'store'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.estados-conservacion.store');
    Route::put('/patrimonio/estados-conservacion/{estadoConservacionPatrimonio}', [EstadoConservacionPatrimonioController::class, 'update'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.estados-conservacion.update');
    Route::delete('/patrimonio/estados-conservacion/{estadoConservacionPatrimonio}', [EstadoConservacionPatrimonioController::class, 'destroy'])
        ->middleware('permission:patrimonio.gestion')
        ->name('patrimonio.estados-conservacion.destroy');

    // Rutas protegidas por middleware de Spatie.
    Route::get('/admin', fn () => 'Zona SuperAdmin')
        ->middleware('role:SuperAdmin')
        ->name('admin.panel');

    Route::get('/secretaria', fn () => 'Zona Secretaria')
        ->middleware('role:Secretaría|Administrador Hermandad|SuperAdmin')
        ->name('secretaria.panel');

    Route::middleware('permission:contabilidad.gestion')
        ->prefix('economia')
        ->name('economia.')
        ->group(function (): void {
            Route::get('/', fn () => redirect()->route('economia.libro-diario.index'))->name('panel');
            Route::get('/dashboard', DashboardEconomiaController::class)->name('dashboard');
            Route::get('/libro-diario', [LibroDiarioController::class, 'index'])->name('libro-diario.index');
            Route::get('/movimiento-rapido', [MovimientoRapidoController::class, 'create'])->name('movimiento-rapido.create');
            Route::post('/movimiento-rapido', [MovimientoRapidoController::class, 'store'])->name('movimiento-rapido.store');
            Route::post('/asientos/ia-generar', AsientoIAGeneracionController::class)->name('asientos.ia-generar');
            Route::get('/plan-contable', [CuentaContableController::class, 'index'])->name('plan-contable.index');
            Route::get('/cuentas/buscar', [CuentaContableController::class, 'search'])->name('cuentas.search');
            Route::post('/asientos', [AsientoController::class, 'store'])->name('asientos.store');
            Route::put('/asientos/{asiento}', [AsientoController::class, 'update'])->name('asientos.update');
            Route::delete('/asientos/{asiento}', [AsientoController::class, 'destroy'])->name('asientos.destroy');
            Route::get('/cuotas', [EconomiaCuotasController::class, 'index'])->name('cuotas.index');
            Route::post('/cuotas/generar-asiento', [EconomiaCuotasController::class, 'generarAsiento'])->name('cuotas.generar-asiento');
            Route::post('/cuotas/exportar-sepa', [EconomiaCuotasController::class, 'exportarSepa'])->name('cuotas.exportar-sepa');
            Route::get('/remesas', [RemesaSepaController::class, 'index'])->name('remesas.index');
            Route::get('/remesas/nueva', [RemesaSepaController::class, 'create'])->name('remesas.create');
            Route::post('/remesas', [RemesaSepaController::class, 'store'])->name('remesas.store');
            Route::get('/remesas/devoluciones', [RemesaSepaController::class, 'devoluciones'])->name('remesas.devoluciones');
            Route::get('/remesas/{remesa}', [RemesaSepaController::class, 'show'])->name('remesas.show');
            Route::get('/remesas/{remesa}/descargar-xml', [RemesaSepaController::class, 'descargarXml'])->name('remesas.descargar-xml');
            Route::post('/remesas/{remesa}/importar-respuesta', [RemesaSepaController::class, 'importarRespuesta'])->name('remesas.importar-respuesta');
            Route::get('/analisis-deuda', [AnalisisDeudaController::class, 'index'])->name('analisis-deuda.index');
            Route::post('/analisis-deuda/reclamacion-masiva', [AnalisisDeudaController::class, 'reclamacionMasiva'])->name('analisis-deuda.reclamacion-masiva');
            Route::get('/tesoreria/arqueo-mensual', [ArqueoTesoreriaController::class, 'create'])->name('tesoreria.arqueo-mensual');
            Route::post('/tesoreria/arqueo-mensual/pdf', [ArqueoTesoreriaController::class, 'pdf'])->name('tesoreria.arqueo-mensual.pdf');
            Route::get('/informes/historial', [InformeHistorialEconomiaController::class, 'index'])->name('informes.historial');
            Route::get('/informes/historial/{informeHistorial}/descargar', [InformeHistorialEconomiaController::class, 'descargar'])->name('informes.historial.descargar');
            Route::get('/informes/libro-mayor', [InformeContableController::class, 'libroMayor'])->name('informes.libro-mayor');
            Route::get('/informes/balance', [InformeContableController::class, 'balance'])->name('informes.balance');
            Route::get('/informes/iva-soportado', [InformeContableController::class, 'ivaSoportado'])->name('informes.iva-soportado');
            Route::get('/informes/impuesto-sociedades-auxiliar', [InformeContableController::class, 'impuestoSociedadesAuxiliar'])->name('informes.is-auxiliar');
            Route::get('/informes/modelo-182', [InformeContableController::class, 'modelo182'])->name('informes.modelo-182');
            Route::get('/informes/modelo-182/csv', [InformeContableController::class, 'modelo182Csv'])->name('informes.modelo-182.csv');
            Route::redirect('facturero', 'facturas');
            Route::get('/facturas', [FacturasController::class, 'index'])->name('facturas.index');
            Route::get('/facturas/galeria', [FacturasController::class, 'galeria'])->name('facturas.galeria');
            Route::get('/proveedores/buscar', [ProveedorController::class, 'buscar'])->name('proveedores.buscar');
            Route::get('/proveedores/{proveedor}/extracto-contable', [ProveedorController::class, 'extractoContable'])->name('proveedores.extracto-contable');
            Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
            Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
            Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
            Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
            Route::get('/documentos-gasto/{documento}/descargar', [FacturasController::class, 'descargar'])->name('documentos-gasto.descargar');
            Route::get('/documentos-gasto/{documento}/ver', [FacturasController::class, 'ver'])->name('documentos-gasto.ver');
            Route::patch('/documentos-gasto/{documento}/estado', [FacturasController::class, 'actualizarEstado'])->name('documentos-gasto.estado');
            Route::get('/loterias', [LoteriaController::class, 'index'])->name('loterias.index');
            Route::post('/loterias', [LoteriaController::class, 'store'])->name('loterias.store');
            Route::get('/loterias/{loteria}', [LoteriaController::class, 'show'])->name('loterias.show');
            Route::post('/loterias/{loteria}/asignaciones', [LoteriaController::class, 'storeAsignacion'])->name('loterias.asignaciones.store');
            Route::post('/loterias/asignaciones/{asignacion}/toggle-cobro', [LoteriaController::class, 'toggleCobro'])->name('loterias.asignaciones.toggle-cobro');
        });

    Route::middleware('permission:tienda.gestion')
        ->prefix('tienda')
        ->name('tienda.')
        ->group(function (): void {
            Route::get('/', fn () => view('tienda.panel'))->name('panel');
            Route::get('/tpv', TpvController::class)->name('tpv');
            Route::get('/api/productos', [TpvApiController::class, 'productos'])->name('api.productos');
            Route::get('/api/hermanos', [TpvApiController::class, 'hermanos'])->name('api.hermanos');
            Route::get('/api/pedido/{uuid}', [TpvApiController::class, 'pedido'])->name('api.pedido');
            Route::post('/api/checkout', [TpvApiController::class, 'checkout'])->name('api.checkout');
            Route::post('/api/checkout-pedido', [TpvApiController::class, 'checkoutPedido'])->name('api.checkout-pedido');
            Route::resource('productos', ProductoTiendaController::class)->parameters(['productos' => 'productoTienda'])->except(['show']);
            Route::post('/categorias', [CategoriaTiendaController::class, 'store'])->name('categorias.store');
            Route::put('/categorias/{categoriaTienda}', [CategoriaTiendaController::class, 'update'])->name('categorias.update');
            Route::delete('/categorias/{categoriaTienda}', [CategoriaTiendaController::class, 'destroy'])->name('categorias.destroy');
            Route::get('/informes', [InformesTiendaController::class, 'index'])->name('informes.index');
            Route::get('/informes/ranking', [InformesTiendaController::class, 'ranking'])->name('informes.ranking');
            Route::get('/informes/margenes', [InformesTiendaController::class, 'margenes'])->name('informes.margenes');
            Route::get('/informes/stock-bajo', [InformesTiendaController::class, 'stockBajo'])->name('informes.stock-bajo');
            Route::get('/apertura-caja', [AperturaCajaTiendaController::class, 'create'])->name('apertura-caja.create');
            Route::post('/apertura-caja', [AperturaCajaTiendaController::class, 'store'])->name('apertura-caja.store');
            Route::get('/cierre-caja', [CierreCajaTiendaController::class, 'create'])->name('cierre-caja.create');
            Route::post('/cierre-caja', [CierreCajaTiendaController::class, 'store'])->name('cierre-caja.store');
            Route::get('/ventas-dia', [VentasDiaTiendaController::class, 'index'])->name('ventas-dia.index');
            Route::get('/ventas/{ventaTienda}/ticket', VentaTiendaTicketPdfController::class)->name('ventas.ticket');
        });

    Route::prefix('salida')
        ->name('salida.')
        ->group(function (): void {
            Route::get('/', fn () => redirect()->route('salida.papeletas.index'))->name('panel');
            Route::get('/papeletas', [PapeletaController::class, 'index'])->name('papeletas.index');
            Route::get('/papeletas/buscar-hermano', [PapeletaController::class, 'buscarHermano'])->name('papeletas.buscar-hermano');
            Route::post('/papeletas', [PapeletaController::class, 'store'])->name('papeletas.store');
            Route::put('/papeletas/{papeleta}', [PapeletaController::class, 'update'])->name('papeletas.update');
            Route::delete('/papeletas/{papeleta}', [PapeletaController::class, 'destroy'])->name('papeletas.destroy');
            Route::patch('/papeletas/{papeleta}/asistencia', [PapeletaController::class, 'toggleAsistencia'])->name('papeletas.toggle-asistencia');
            Route::get('/configuracion', [ConfiguracionSalidaController::class, 'index'])->name('configuracion.index');
            Route::post('/configuracion', [ConfiguracionSalidaController::class, 'guardar'])->name('configuracion.guardar');
            Route::get('/insignias', [InsigniaController::class, 'index'])->name('insignias.index');
            Route::post('/insignias', [InsigniaController::class, 'store'])->name('insignias.store');
            Route::put('/insignias/{insignia}', [InsigniaController::class, 'update'])->name('insignias.update');
            Route::delete('/insignias/{insignia}', [InsigniaController::class, 'destroy'])->name('insignias.destroy');
            Route::get('/tunicas', [TunicaController::class, 'index'])->name('tunicas.index');
            Route::post('/tunicas', [TunicaController::class, 'store'])->name('tunicas.store');
            Route::put('/tunicas/{tunica}', [TunicaController::class, 'update'])->name('tunicas.update');
            Route::delete('/tunicas/{tunica}', [TunicaController::class, 'destroy'])->name('tunicas.destroy');
            Route::get('/cortejo', [CortejoController::class, 'index'])->name('cortejo.index');
            Route::get('/papeletas/{papeleta}/pdf', [PapeletaPdfController::class, 'papeleta'])->name('papeletas.pdf');
            Route::get('/cortejo/{ejercicio}/pdf', [PapeletaPdfController::class, 'listadoCortejo'])->name('cortejo.pdf');
        });
});

require __DIR__.'/auth.php';
