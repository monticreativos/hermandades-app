<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\ContactoExterno;
use App\Models\Hermano;
use App\Models\SecretariaActoProtocolo;
use App\Models\SecretariaEntidadExterna;
use App\Models\SecretariaInvitacionActo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelacionesInstitucionalesController extends Controller
{
    public function index(): View
    {
        return view('secretaria.relaciones.index', [
            'entidades' => SecretariaEntidadExterna::query()->latest()->get(),
            'contactosExternos' => ContactoExterno::query()->orderBy('nombre')->get(),
            'hermanos' => Hermano::query()->where('estado', 'Alta')->orderBy('apellidos')->limit(300)->get(['id', 'numero_hermano', 'nombre', 'apellidos']),
            'actos' => SecretariaActoProtocolo::query()->with(['invitaciones.entidad', 'invitaciones.hermano', 'invitaciones.contactoExterno'])->latest('fecha')->get(),
        ]);
    }

    public function storeEntidad(Request $request): RedirectResponse
    {
        SecretariaEntidadExterna::query()->create($request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:40'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:32'],
            'notas' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Entidad externa añadida.');
    }

    public function storeActo(Request $request): RedirectResponse
    {
        SecretariaActoProtocolo::query()->create($request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Acto protocolario creado.');
    }

    public function storeInvitacion(Request $request): RedirectResponse
    {
        SecretariaInvitacionActo::query()->create($request->validate([
            'acto_id' => ['required', 'integer', 'exists:secretaria_actos_protocolo,id'],
            'entidad_externa_id' => ['nullable', 'integer', 'exists:secretaria_entidades_externas,id'],
            'hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'nombre_invitado' => ['required', 'string', 'max:255'],
            'estado_confirmacion' => ['required', 'in:pendiente,confirmado,declinado'],
            'fila' => ['nullable', 'integer', 'min:1'],
            'banco' => ['nullable', 'integer', 'min:1'],
            'orden_protocolo' => ['nullable', 'integer', 'min:1'],
            'notas' => ['nullable', 'string'],
        ]) + [
            'categoria_fuente' => ContactoExterno::query()->find($request->integer('contacto_externo_id'))?->categoria,
            'nombre_invitado' => $request->filled('nombre_invitado')
                ? (string) $request->string('nombre_invitado')
                : ($request->filled('contacto_externo_id')
                    ? (string) (ContactoExterno::query()->find($request->integer('contacto_externo_id'))?->nombre ?? '')
                    : (string) (Hermano::query()->find($request->integer('hermano_id'))?->nombreCompleto() ?? '')
                ),
        ]);

        return back()->with('status', 'Invitado añadido al protocolo.');
    }

    public function anadirCategoria(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'acto_id' => ['required', 'integer', 'exists:secretaria_actos_protocolo,id'],
            'categoria' => ['required', 'string', 'max:60'],
        ]);

        $contactos = ContactoExterno::query()->where('categoria', $data['categoria'])->get();
        foreach ($contactos as $c) {
            SecretariaInvitacionActo::query()->firstOrCreate([
                'acto_id' => $data['acto_id'],
                'contacto_externo_id' => $c->id,
                'nombre_invitado' => $c->nombre,
            ], [
                'estado_confirmacion' => 'pendiente',
                'categoria_fuente' => $c->categoria,
            ]);
        }

        return back()->with('status', 'Invitados añadidos masivamente por categoría: '.$data['categoria']);
    }
}
