<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaria\StoreComunicadoMasivoRequest;
use App\Jobs\EnviarLoteComunicadoMasivoJob;
use App\Models\ComunicadoMasivo;
use App\Models\ComunicadoMasivoDestinatario;
use App\Models\ContactoExterno;
use App\Models\Hermano;
use App\Services\Comunicacion\ComunicadoMasivoDestinatariosResolver;
use App\Support\RegistroActividad;
use App\Support\SanitizeComunicadoHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComunicadoMasivoController extends Controller
{
    public function index(): View
    {
        $comunicados = ComunicadoMasivo::query()
            ->with('creadoPor')
            ->withCount('destinatarios')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('secretaria.comunicados-masivos.index', [
            'comunicados' => $comunicados,
        ]);
    }

    public function create(): View
    {
        return view('secretaria.comunicados-masivos.create', [
            'categoriasContactos' => ContactoExterno::query()->distinct()->orderBy('categoria')->pluck('categoria')->filter()->values(),
            'destinatariosSugeridos' => [
                'hermanos' => Hermano::query()->where('estado', 'Alta')->whereNotNull('email')->orderBy('apellidos')->limit(150)->get(['id', 'nombre', 'apellidos']),
                'contactos' => ContactoExterno::query()->whereNotNull('email')->orderBy('nombre')->limit(200)->get(['id', 'nombre', 'categoria']),
            ],
        ]);
    }

    public function store(StoreComunicadoMasivoRequest $request, ComunicadoMasivoDestinatariosResolver $resolver): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $cuerpo = SanitizeComunicadoHtml::clean($request->validated('cuerpo_html'));

        $comunicado = ComunicadoMasivo::query()->create([
            'asunto' => $request->validated('asunto'),
            'cuerpo_html' => $cuerpo,
            'filtro_envio' => $request->validated('filtro_envio'),
            'filtro_tramo_valor' => $request->validated('filtro_tramo_valor'),
            'filtro_contacto_categoria' => $request->validated('filtro_contacto_categoria'),
            'audiencia_mixta' => $request->validated('audiencia_mixta'),
            'destinatarios_individuales' => $request->validated('destinatarios_individuales'),
            'creado_por_user_id' => $user->id,
            'estado' => ComunicadoMasivo::ESTADO_ENCOLADO,
            'total_destinatarios' => 0,
            'correos_enviados' => 0,
        ]);

        $destinatarios = $resolver->resolver($comunicado->fresh());

        if ($destinatarios->isEmpty()) {
            $comunicado->delete();

            return redirect()
                ->route('secretaria.comunicados-masivos.create')
                ->withInput()
                ->withErrors(['filtro_envio' => 'No hay destinatarios con email válido que cumplan el filtro seleccionado.']);
        }

        DB::transaction(function () use ($comunicado, $destinatarios): void {
            $now = now();
            $rows = $destinatarios->map(fn ($d) => [
                'comunicado_masivo_id' => $comunicado->id,
                'hermano_id' => $d['hermano_id'],
                'contacto_externo_id' => $d['contacto_externo_id'],
                'tracking_token' => (string) Str::uuid(),
                'nombre_destinatario' => $d['nombre'],
                'email_destinatario' => $d['email'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 200) as $chunk) {
                ComunicadoMasivoDestinatario::query()->insert($chunk);
            }

            $comunicado->forceFill(['total_destinatarios' => count($rows)])->save();
        });

        $comunicado->refresh();

        EnviarLoteComunicadoMasivoJob::dispatch($comunicado->id);

        RegistroActividad::registrar(
            'comunicado_masivo_encolado',
            'Comunicado masivo «'.$comunicado->asunto.'» ('.$comunicado->filtro_envio.') encolado para '.$comunicado->total_destinatarios.' destinatario(s), ID '.$comunicado->id.'.'
        );

        return redirect()
            ->route('secretaria.comunicados-masivos.show', $comunicado)
            ->with('status', 'El envío se ha puesto en cola. Los correos se irán despachando en segundo plano sin bloquear el servidor.');
    }

    public function show(ComunicadoMasivo $comunicado): View
    {
        $comunicado->load('creadoPor');
        $stats = [
            'abiertos' => $comunicado->destinatarios()->whereNotNull('abierto_en')->count(),
            'pendientes_correo' => $comunicado->destinatarios()->whereNull('correo_enviado_en')->count(),
        ];
        $destinatarios = $comunicado->destinatarios()
            ->with(['hermano', 'contactoExterno'])
            ->orderBy('id')
            ->paginate(40);

        return view('secretaria.comunicados-masivos.show', [
            'comunicado' => $comunicado,
            'stats' => $stats,
            'destinatarios' => $destinatarios,
        ]);
    }
}
