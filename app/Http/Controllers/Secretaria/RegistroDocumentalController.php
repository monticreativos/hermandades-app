<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\ContactoExterno;
use App\Models\Hermano;
use App\Models\Proveedor;
use App\Models\SecretariaRegistroDocumental;
use App\Services\Secretaria\ProtocoloRegistroService;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistroDocumentalController extends Controller
{
    public function index(Request $request): View
    {
        $query = SecretariaRegistroDocumental::query()->with('subidoPor')->latest('fecha');
        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(fn ($w) => $w->where('extracto', 'like', $q)
                ->orWhere('remitente_destinatario', 'like', $q)
                ->orWhere('numero_protocolo', 'like', $q));
        }

        return view('secretaria.registro.index', [
            'registros' => $query->paginate(20)->withQueryString(),
            'hermanos' => Hermano::query()->orderBy('apellidos')->limit(300)->get(['id', 'numero_hermano', 'nombre', 'apellidos']),
            'proveedores' => Proveedor::query()->orderBy('razon_social')->limit(300)->get(['id', 'razon_social', 'nif_cif']),
            'contactosExternos' => ContactoExterno::query()->orderBy('nombre')->limit(400)->get(['id', 'nombre', 'entidad_institucion', 'categoria']),
        ]);
    }

    public function store(Request $request, ProtocoloRegistroService $service): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo_movimiento' => ['required', 'in:entrada,salida'],
            'remitente_destinatario' => ['nullable', 'string', 'max:255'],
            'hermano_relacionado_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'remitente_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'remitente_proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'remitente_contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'destinatario_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'destinatario_proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'destinatario_contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'extracto' => ['required', 'string', 'max:500'],
            'tipo_documento' => ['required', 'in:Carta,Factura,Edicto,Invitacion'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:51200'],
        ]);

        $data['remitente_destinatario'] = $this->resolverTextoRemitenteDestinatario($data);

        $file = $request->file('archivo');
        $path = $file->store('secretaria/registro', 'local');
        $protocolo = $service->siguienteNumero($data['tipo_movimiento']);
        $selloPath = 'secretaria/registro-sellos/'.$protocolo.'.txt';
        Storage::disk('local')->put($selloPath, $service->contenidoSello($protocolo, (string) $data['fecha']));

        $registro = SecretariaRegistroDocumental::query()->create([
            ...$data,
            'numero_protocolo' => $protocolo,
            'archivo_path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'tamano_bytes' => $file->getSize(),
            'sello_registro_path' => $selloPath,
            'subido_por_user_id' => $request->user()?->id,
        ]);

        RegistroActividad::registrar('secretaria_registro_documental', 'Registro documental '.$registro->numero_protocolo.' creado.');

        return back()->with('status', 'Registro documental creado con sello digital '.$protocolo.'.');
    }

    public function storeDrop(Request $request, ProtocoloRegistroService $service): RedirectResponse
    {
        $request->merge([
            'fecha' => now()->toDateString(),
            'tipo_movimiento' => 'entrada',
            'remitente_destinatario' => 'Pendiente de clasificar',
            'extracto' => 'Documento cargado por drag & drop',
            'tipo_documento' => 'Carta',
        ]);

        return $this->store($request, $service);
    }

    public function update(Request $request, SecretariaRegistroDocumental $registro): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo_movimiento' => ['required', 'in:entrada,salida'],
            'remitente_destinatario' => ['nullable', 'string', 'max:255'],
            'hermano_relacionado_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'remitente_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'remitente_proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'remitente_contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'destinatario_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'destinatario_proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'destinatario_contacto_externo_id' => ['nullable', 'integer', 'exists:contactos_externos,id'],
            'extracto' => ['required', 'string', 'max:500'],
            'tipo_documento' => ['required', 'in:Carta,Factura,Edicto,Invitacion'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:51200'],
        ]);

        $data['remitente_destinatario'] = $this->resolverTextoRemitenteDestinatario($data);

        if ($request->hasFile('archivo')) {
            Storage::disk('local')->delete($registro->archivo_path);
            $file = $request->file('archivo');
            $data['archivo_path'] = $file->store('secretaria/registro', 'local');
            $data['nombre_original'] = $file->getClientOriginalName();
            $data['mime'] = $file->getClientMimeType();
            $data['tamano_bytes'] = $file->getSize();
        }

        $registro->update($data);

        RegistroActividad::registrar('secretaria_registro_documental_actualizado', 'Registro documental '.$registro->numero_protocolo.' actualizado.');

        return back()->with('status', 'Registro documental actualizado.');
    }

    public function ver(SecretariaRegistroDocumental $registro): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($registro->archivo_path), 404);

        return response()->file(Storage::disk('local')->path($registro->archivo_path), [
            'Content-Type' => $registro->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$registro->nombre_original.'"',
        ]);
    }

    public function descargar(SecretariaRegistroDocumental $registro): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($registro->archivo_path), 404);

        return Storage::disk('local')->download(
            $registro->archivo_path,
            $registro->nombre_original ?: basename($registro->archivo_path),
            ['Content-Type' => $registro->mime ?: 'application/octet-stream']
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private function resolverTextoRemitenteDestinatario(array $data): string
    {
        $remitente = 'No indicado';
        $destinatario = 'No indicado';

        if (! empty($data['remitente_hermano_id'])) {
            $h = Hermano::query()->find((int) $data['remitente_hermano_id']);
            if ($h) {
                $remitente = 'Hermano: '.trim($h->apellidos.', '.$h->nombre);
            }
        } elseif (! empty($data['remitente_proveedor_id'])) {
            $p = Proveedor::query()->find((int) $data['remitente_proveedor_id']);
            if ($p) {
                $remitente = 'Proveedor: '.$p->razon_social;
            }
        } elseif (! empty($data['remitente_contacto_externo_id'])) {
            $c = ContactoExterno::query()->find((int) $data['remitente_contacto_externo_id']);
            if ($c) {
                $remitente = 'Contacto: '.$c->nombre;
            }
        }

        if (! empty($data['destinatario_hermano_id'])) {
            $h = Hermano::query()->find((int) $data['destinatario_hermano_id']);
            if ($h) {
                $destinatario = 'Hermano: '.trim($h->apellidos.', '.$h->nombre);
            }
        } elseif (! empty($data['destinatario_proveedor_id'])) {
            $p = Proveedor::query()->find((int) $data['destinatario_proveedor_id']);
            if ($p) {
                $destinatario = 'Proveedor: '.$p->razon_social;
            }
        } elseif (! empty($data['destinatario_contacto_externo_id'])) {
            $c = ContactoExterno::query()->find((int) $data['destinatario_contacto_externo_id']);
            if ($c) {
                $destinatario = 'Contacto: '.$c->nombre;
            }
        }

        return 'Remitente: '.$remitente.' | Destinatario: '.$destinatario;
    }
}
