<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\ContactoExterno;
use App\Models\ContactoExternoTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorioContactosController extends Controller
{
    public function index(Request $request): View
    {
        $q = ContactoExterno::query()->with('tags')->orderBy('nombre');
        if ($request->filled('categoria')) {
            $q->where('categoria', (string) $request->string('categoria'));
        }
        if ($request->filled('q')) {
            $s = '%'.$request->string('q').'%';
            $q->where(fn ($w) => $w->where('nombre', 'like', $s)->orWhere('entidad_institucion', 'like', $s)->orWhere('email', 'like', $s));
        }

        return view('secretaria.directorio.index', [
            'contactos' => $q->paginate(20)->withQueryString(),
            'categorias' => ['Hermandades', 'Ayuntamiento', 'Proveedores', 'Exaltadores', 'Prensa', 'Personalidades'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'entidad_institucion' => ['nullable', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:60'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $contacto = ContactoExterno::query()->create($data);
        $this->syncTags($contacto, (string) ($data['tags'] ?? ''));

        return back()->with('status', 'Contacto externo creado en el directorio.');
    }

    public function storeQuick(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'entidad_institucion' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        $contacto = ContactoExterno::query()->create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $contacto->id,
                'text' => $contacto->nombre.($contacto->entidad_institucion ? ' · '.$contacto->entidad_institucion : ''),
            ]);
        }

        return back()->with('status', 'Contacto rápido creado. Ya puedes seleccionarlo en remitente/destinatario.');
    }

    private function syncTags(ContactoExterno $contacto, string $raw): void
    {
        $names = collect(explode(',', $raw))->map(fn ($v) => trim($v))->filter()->unique()->take(20);
        $ids = $names->map(function (string $name) {
            return ContactoExternoTag::query()->firstOrCreate(['nombre' => $name])->id;
        })->all();
        $contacto->tags()->sync($ids);
    }
}
