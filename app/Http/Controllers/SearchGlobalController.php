<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\ConfiguracionHermandad;
use App\Models\ContactoExterno;
use App\Models\Cuadrilla;
use App\Models\DocumentoArchivo;
use App\Models\Enser;
use App\Models\Hermano;
use App\Models\ProductoTienda;
use App\Models\SecretariaRegistroDocumental;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchGlobalController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        $hermanos = Hermano::query()
            ->where(function ($query) use ($q): void {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Hermano $hermano): array => [
                'category' => 'Hermanos',
                'title' => $hermano->nombre.' '.$hermano->apellidos,
                'subtitle' => 'DNI: '.$hermano->dni,
                'url' => route('hermanos.show', $hermano),
            ])
            ->all();

        $users = User::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(3)
            ->get()
            ->map(fn (User $user): array => [
                'category' => 'Usuarios',
                'title' => $user->name,
                'subtitle' => $user->email,
                'url' => route('profile.edit'),
            ])
            ->all();

        $config = ConfiguracionHermandad::query()
            ->where(function ($query) use ($q): void {
                $query->where('nombre_hermandad', 'like', "%{$q}%")
                    ->orWhere('nombre_corto', 'like', "%{$q}%")
                    ->orWhere('cif', 'like', "%{$q}%");
            })
            ->limit(1)
            ->get()
            ->map(fn (ConfiguracionHermandad $hermandad): array => [
                'category' => 'Configuracion Hermandad',
                'title' => $hermandad->nombre_corto ?: $hermandad->nombre_hermandad,
                'subtitle' => 'Datos institucionales',
                'url' => route('ajustes.index'),
            ])
            ->all();

        $enseres = [];
        if ($request->user()?->hasPermissionTo('patrimonio.gestion')) {
            $enseres = Enser::query()
                ->with(['categoriaPatrimonio', 'estadoConservacionPatrimonio'])
                ->where(function ($query) use ($q): void {
                    $query->where('nombre', 'like', "%{$q}%")
                        ->orWhere('ubicacion', 'like', "%{$q}%")
                        ->orWhere('autor', 'like', "%{$q}%")
                        ->orWhere('materiales', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get()
                ->map(fn (Enser $enser): array => [
                    'category' => 'Patrimonio',
                    'title' => $enser->nombre,
                    'subtitle' => ($enser->categoriaPatrimonio?->nombre ?? 'Sin categoría').($enser->ubicacion ? ' · '.$enser->ubicacion : ''),
                    'url' => route('patrimonio.show', $enser),
                ])
                ->all();
        }

        $economiaRemesas = [];
        if ($request->user()?->hasPermissionTo('contabilidad.gestion') && preg_match('/remesa|sepa|pain\.?008|concili|domicili|camt/i', $q)) {
            $economiaRemesas = [
                [
                    'category' => 'Economía',
                    'title' => 'Remesas SEPA y conciliación',
                    'subtitle' => 'Generar pain.008, importar respuesta banco',
                    'url' => route('economia.remesas.index'),
                ],
            ];
        }
        if ($request->user()?->hasPermissionTo('contabilidad.gestion') && preg_match('/dashboard|kpi|cuadro.*mando|tesorer|saldo.*caja|saldo.*banco/i', $q)) {
            $economiaRemesas[] = [
                'category' => 'Economía',
                'title' => 'Dashboard de Mayordomía',
                'subtitle' => 'Saldos 570/572, cuotas y evolución mensual',
                'url' => route('economia.dashboard'),
            ];
        }

        $tiendaAtajos = [];
        if ($request->user()?->hasPermissionTo('tienda.gestion') && preg_match('/\btpv\b|punto de venta|terminal.*venta|tienda.*cofrad|medallas.*tienda|stock.*tienda/i', $q)) {
            $tiendaAtajos[] = [
                'category' => 'Tienda',
                'title' => 'Terminal punto de venta (TPV)',
                'subtitle' => 'Cobro rápido integrado con contabilidad',
                'url' => route('tienda.tpv'),
            ];
        }

        $cuadrillasAtajos = [];
        if ($request->user()?->hasPermissionTo('cuadrillas.gestion') && preg_match('/costaler|capataz|cuadrill|iguala|trabajadera|ensayo|relevo/i', $q)) {
            $cuadrillasAtajos[] = [
                'category' => 'Cuadrillas',
                'title' => 'Panel de cuadrillas y trabajaderas',
                'subtitle' => 'Igualá, ensayos, avisos y relevos',
                'url' => route('cuadrillas.index'),
            ];
        }
        if ($request->user()?->hasPermissionTo('cuadrillas.gestion')) {
            $cuadrillasAtajos = array_merge($cuadrillasAtajos, Cuadrilla::query()
                ->where(function ($w) use ($q): void {
                    $w->where('nombre', 'like', "%{$q}%")
                        ->orWhere('paso', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get()
                ->map(fn (Cuadrilla $c): array => [
                    'category' => 'Cuadrillas',
                    'title' => $c->nombre.' ('.$c->año.')',
                    'subtitle' => strtoupper($c->paso),
                    'url' => route('cuadrillas.iguala', $c),
                ])
                ->all());
        }

        $productosTienda = [];
        if ($request->user()?->hasPermissionTo('tienda.gestion')) {
            $productosTienda = ProductoTienda::query()
                ->where(function ($query) use ($q): void {
                    $query->where('nombre', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                })
                ->orderBy('nombre')
                ->limit(8)
                ->get()
                ->map(fn (ProductoTienda $p): array => [
                    'category' => 'Tienda',
                    'title' => $p->nombre,
                    'subtitle' => ($p->sku ? 'SKU '.$p->sku.' · ' : '').number_format((float) $p->precio_venta, 2, ',', '.').' € · Stock '.$p->stock_actual,
                    'url' => route('tienda.productos.edit', $p),
                ])
                ->all();
        }

        $asientosConta = [];
        if ($request->user()?->hasPermissionTo('contabilidad.gestion')) {
            $asientosConta = Asiento::query()
                ->with('ejercicio')
                ->where(function ($query) use ($q): void {
                    $query->where('glosa', 'like', "%{$q}%")
                        ->orWhere('numero_asiento', 'like', "%{$q}%");
                })
                ->orderByDesc('fecha')
                ->limit(5)
                ->get()
                ->map(fn (Asiento $asiento): array => [
                    'category' => 'Contabilidad',
                    'title' => 'Asiento '.$asiento->numero_asiento.' · '.$asiento->glosa,
                    'subtitle' => optional($asiento->fecha)->format('d/m/Y').($asiento->ejercicio ? ' · Ej. '.$asiento->ejercicio->año : ''),
                    'url' => route('economia.libro-diario.index'),
                ])
                ->all();
        }

        $informesAcceso = $request->user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']) ?? false;
        $informesAtajos = [];
        if ($informesAcceso) {
            $pushInforme = function (string $url, string $title, string $subtitle) use (&$informesAtajos): void {
                foreach ($informesAtajos as $ex) {
                    if ($ex['url'] === $url) {
                        return;
                    }
                }
                $informesAtajos[] = [
                    'category' => 'Informes',
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'url' => $url,
                ];
            };

            if (preg_match('/censo|votant|electoral|elecci/i', $q)) {
                $pushInforme(route('informes.censo.index'), 'Censo electoral de votantes', 'Listado y PDF oficial (RGPD)');
            }
            if (preg_match('/etiqueta|mailing|postal|csv/i', $q)) {
                $pushInforme(route('informes.etiquetas.index'), 'Etiquetas postales y CSV', 'A4 3×7 y exportación mailing');
            }
            if (preg_match('/informe|secretar/i', $q)) {
                $pushInforme(route('informes.index'), 'Panel de informes', 'Censo, estadísticas, listados Excel y etiquetas');
            }
            if (preg_match('/estad[ií]stic|anal[ií]sis|pir[aá]mide|demograf|kpi|junta|salud|morosidad|mapa.*cp|c[oó]digo postal/i', $q)) {
                $pushInforme(route('informes.estadisticas.index'), 'Estadísticas y análisis', 'Pirámide de edad, altas/bajas, top CP y KPIs');
            }
            if (preg_match('/excel|xlsx|listado.*medida|export.*herman/i', $q)) {
                $pushInforme(route('informes.listados.index'), 'Listados a medida (Excel)', 'Columnas personalizables y filtro por estado');
            }
            if (preg_match('/certificad|pertenencia|hacienda.*cuota|cuota.*hacienda|deducci/i', $q)) {
                $pushInforme(route('informes.index'), 'Certificados de hermanos', 'Desde la ficha: menú Generar certificado (PDF)');
            }
        }

        $ajustesAtajos = [];
        if ($request->user()?->hasAnyRole(['Administrador Hermandad', 'SuperAdmin'])) {
            $pushAjuste = function (string $url, string $title, string $subtitle) use (&$ajustesAtajos): void {
                foreach ($ajustesAtajos as $ex) {
                    if ($ex['url'] === $url) {
                        return;
                    }
                }
                $ajustesAtajos[] = [
                    'category' => 'Ajustes y auditoría',
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'url' => $url,
                ];
            };

            if (preg_match('/estado.*sistema|health|salud.*app|storage.*link|diagn[oó]stic/i', $q)) {
                $pushAjuste(route('ajustes.estado-sistema'), 'Estado del sistema', 'Storage, ejercicios contables y datos incompletos');
            }
            if (preg_match('/registro.*actividad|auditor[ií]a|log.*acci|qui[eé]n hizo/i', $q)) {
                $pushAjuste(route('ajustes.actividades.index'), 'Registro de actividad', 'Historial de acciones críticas');
            }
            if (preg_match('/recalcular|renumer|n[uú]mero.*hermano|hueco|orden.*cronol/i', $q)) {
                $pushAjuste(route('ajustes.renumeracion.show'), 'Recalcular números de hermano', 'Reordenar por fecha de alta (irreversible)');
            }
            if (preg_match('/ajuste|config.*hermandad|escudo|firmas/i', $q)) {
                $pushAjuste(route('ajustes.index'), 'Ajustes de la Hermandad', 'Datos, escudo, firmas y herramientas de gobierno');
            }
        }

        $secretariaCom = [];
        if ($request->user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin'])) {
            $pushSec = function (string $url, string $title, string $subtitle, string $category = 'Comunicación y archivo') use (&$secretariaCom): void {
                foreach ($secretariaCom as $ex) {
                    if ($ex['url'] === $url) {
                        return;
                    }
                }
                $secretariaCom[] = [
                    'category' => $category,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'url' => $url,
                ];
            };

            if (preg_match('/comunicad|email.*masiv|correo.*masiv|redactor|newsletter|mailing.*html/i', $q)) {
                $pushSec(route('secretaria.comunicados-masivos.create'), 'Redactor de comunicados (email masivo)', 'HTML, filtros y envío en cola');
            }
            if (preg_match('/archivo.*digital|documento.*instituc|subir.*pdf|gesti[oó]n documental/i', $q)) {
                $pushSec(route('secretaria.archivo-digital.index'), 'Archivo digital de la Hermandad', 'Reglas, actas, inventario y boletines');
            }
            if (preg_match('/firma.*conformidad|firmar.*tallaje|firmar.*t[uú]nica|acepto y firmo/i', $q)) {
                $pushSec(route('secretaria.firmas-conformidad.index'), 'Firmas de conformidad', 'Solicitudes y registro en portal');
            }
            if (preg_match('/acta.*cabildo|^actas?\b|cabildo/i', $q)) {
                $pushSec(route('secretaria.archivo-digital.index'), 'Actas de Cabildo', 'Categoría en archivo digital', 'Comunicación y archivo');
            }
            if (preg_match('/bolet[ií]n|gaceta|publicaci[oó]n.*hermandad/i', $q)) {
                $pushSec(route('secretaria.archivo-digital.create'), 'Subir boletín (PDF)', 'Categoría Boletín — visible en portal', 'Comunicación y archivo');
            }
            if (preg_match('/aviso.*hermano|tabl[oó]n|noticia.*portal|igual[aá]|misa.*hermandad/i', $q)) {
                $pushSec(route('secretaria.avisos.create'), 'Avisos y tablón del portal', 'Noticias, igualás, misas — marcar visible en inicio');
            }
            if (preg_match('/registro|protocolo|entrada|salida|sello/i', $q)) {
                $pushSec(route('secretaria.registro.index'), 'Libro de registro entrada/salida', 'Protocolo oficial y sello digital');
            }
            if (preg_match('/plantilla|certificado|saluda|cabildo|modelo/i', $q)) {
                $pushSec(route('secretaria.plantillas.index'), 'Plantillas y generador de documentos', 'Variables automáticas por hermano');
            }
            if (preg_match('/invitad|protocolo|quinario|relaciones institucionales|consejo|ayuntamiento/i', $q)) {
                $pushSec(route('secretaria.relaciones.index'), 'Invitados y relaciones institucionales', 'Confirmaciones y asientos por fila/banco');
            }
        }

        $documentosArchivoBusqueda = [];
        if ($request->user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']) && mb_strlen($q) >= 3) {
            $documentosArchivoBusqueda = DocumentoArchivo::query()
                ->where(function ($w) use ($q): void {
                    $w->where('titulo', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (DocumentoArchivo $d): array => [
                    'category' => 'Archivo digital',
                    'title' => $d->titulo,
                    'subtitle' => (DocumentoArchivo::etiquetasCategoria()[$d->categoria] ?? $d->categoria).' · '.(DocumentoArchivo::etiquetasNivel()[$d->nivel_acceso] ?? $d->nivel_acceso),
                    'url' => route('secretaria.archivo-digital.index', ['q' => $q]),
                ])
                ->all();
        }

        $registrosDocumentales = [];
        if ($request->user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']) && mb_strlen($q) >= 3) {
            $registrosDocumentales = SecretariaRegistroDocumental::query()
                ->where(fn ($w) => $w->where('extracto', 'like', "%{$q}%")
                    ->orWhere('remitente_destinatario', 'like', "%{$q}%")
                    ->orWhere('numero_protocolo', 'like', "%{$q}%"))
                ->latest('fecha')
                ->limit(5)
                ->get()
                ->map(fn (SecretariaRegistroDocumental $r): array => [
                    'category' => 'Registro documental',
                    'title' => $r->numero_protocolo.' · '.$r->extracto,
                    'subtitle' => ($r->fecha?->format('d/m/Y') ?? '').' · '.$r->remitente_destinatario,
                    'url' => route('secretaria.registro.index', ['q' => $q]),
                ])
                ->all();
        }

        $contactosExternos = [];
        if ($request->user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']) && mb_strlen($q) >= 2) {
            $contactosExternos = ContactoExterno::query()
                ->where(fn ($w) => $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('entidad_institucion', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('categoria', 'like', "%{$q}%"))
                ->orderBy('nombre')
                ->limit(8)
                ->get()
                ->map(fn (ContactoExterno $c): array => [
                    'category' => 'Directorio',
                    'title' => $c->nombre,
                    'subtitle' => trim(($c->categoria ?: 'Contacto').($c->entidad_institucion ? ' · '.$c->entidad_institucion : '')),
                    'url' => route('secretaria.directorio.index', ['q' => $q]),
                ])->all();
        }

        $results = array_merge($secretariaCom, $documentosArchivoBusqueda, $registrosDocumentales, $contactosExternos, $ajustesAtajos, $informesAtajos, $economiaRemesas, $tiendaAtajos, $cuadrillasAtajos, $productosTienda, $hermanos, $users, $config, $asientosConta, $enseres);

        return response()->json(['results' => $results]);
    }
}
