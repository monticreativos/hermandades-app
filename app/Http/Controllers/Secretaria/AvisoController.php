<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaria\StoreAvisoRequest;
use App\Models\Aviso;
use App\Models\Hermano;
use App\Notifications\AvisoHermanoMailNotification;
use App\Services\Portal\DestinatariosAvisoResolver;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AvisoController extends Controller
{
    public function index(): View
    {
        $avisos = Aviso::query()
            ->with(['creadoPor'])
            ->withCount('destinatarios')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('secretaria.avisos.index', [
            'avisos' => $avisos,
        ]);
    }

    public function create(): View
    {
        $hermanos = Hermano::query()
            ->orderBy('numero_hermano')
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos', 'estado', 'email']);

        return view('secretaria.avisos.create', [
            'hermanos' => $hermanos,
        ]);
    }

    public function store(StoreAvisoRequest $request, DestinatariosAvisoResolver $resolver): RedirectResponse
    {
        $user = auth()->user();
        abort_if(! $user, 403);

        try {
            [$aviso, $hermanos] = DB::transaction(function () use ($request, $resolver, $user) {
                $aviso = Aviso::query()->create([
                    'titulo' => $request->validated('titulo'),
                    'cuerpo' => $request->validated('cuerpo'),
                    'alcance' => $request->validated('alcance'),
                    'solo_alta' => $request->boolean('solo_alta'),
                    'solo_portal' => $request->boolean('solo_portal'),
                    'urgente' => (bool) $request->validated('urgente'),
                    'visible_tablon' => (bool) $request->validated('visible_tablon'),
                    'hermano_id' => $request->validated('alcance') === Aviso::ALCANCE_INDIVIDUAL
                        ? $request->validated('hermano_id')
                        : null,
                    'creado_por_user_id' => $user->id,
                    'enviado_en' => null,
                ]);

                if ($aviso->alcance === Aviso::ALCANCE_SELECTIVO) {
                    $ids = $request->validated('hermano_ids');
                    $coleccion = $resolver->hermanosPorIds($ids);
                } else {
                    $coleccion = $resolver->hermanosPara($aviso);
                }

                if ($coleccion->isEmpty()) {
                    $aviso->delete();
                    throw ValidationException::withMessages([
                        'alcance' => ['No hay destinatarios para este aviso. Revise el alcance o la selección.'],
                    ]);
                }

                $now = now();
                $rows = $coleccion->map(fn (Hermano $h) => [
                    'aviso_id' => $aviso->id,
                    'hermano_id' => $h->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table('aviso_hermano')->insert($chunk);
                }

                $aviso->forceFill(['enviado_en' => $now])->save();

                return [$aviso, $coleccion];
            });
        } catch (ValidationException $e) {
            return redirect()
                ->route('secretaria.avisos.create')
                ->withInput()
                ->withErrors($e->errors());
        }

        if ($request->boolean('notificar_email')) {
            foreach ($hermanos as $hermano) {
                $email = trim((string) $hermano->email);
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                Notification::route('mail', $email)
                    ->notify(new AvisoHermanoMailNotification($aviso, $hermano));
            }
        }

        RegistroActividad::registrar(
            'aviso_hermanos_enviado',
            'Aviso «'.$aviso->titulo.'» ('.$aviso->alcance.') a '.$hermanos->count().' destinatario(s), ID '.$aviso->id.'.'
        );

        return redirect()
            ->route('secretaria.avisos.show', $aviso)
            ->with('status', 'Aviso enviado a '.$hermanos->count().' hermano(s).');
    }

    public function show(Aviso $aviso): View
    {
        $aviso->load(['creadoPor', 'hermanoIndividual']);
        $destinatarios = $aviso->destinatarios()
            ->with('hermano')
            ->orderBy('id')
            ->paginate(40);

        return view('secretaria.avisos.show', [
            'aviso' => $aviso,
            'destinatarios' => $destinatarios,
        ]);
    }
}
