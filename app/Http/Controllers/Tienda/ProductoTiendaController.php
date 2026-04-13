<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\StoreProductoTiendaRequest;
use App\Http\Requests\Tienda\UpdateProductoTiendaRequest;
use App\Models\CategoriaTienda;
use App\Models\ProductoTienda;
use App\Models\ProductoTiendaImagen;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoTiendaController extends Controller
{
    public function index(Request $request): View
    {
        $q = ProductoTienda::query()->with('imagenes')->orderBy('nombre');

        if ($request->filled('categoria')) {
            $q->where('categoria', $request->string('categoria'));
        }
        if ($request->filled('q')) {
            $s = '%'.$request->string('q').'%';
            $q->where(function ($w) use ($s): void {
                $w->where('nombre', 'like', $s)
                    ->orWhere('sku', 'like', $s);
            });
        }
        if ($request->boolean('solo_bajo_minimo')) {
            $q->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $productos = $q->paginate(24)->withQueryString();

        return view('tienda.productos.index', [
            'productos' => $productos,
            'categorias' => ProductoTienda::categorias(),
            'categoriasTienda' => class_exists(CategoriaTienda::class) ? CategoriaTienda::query()->orderBy('orden')->orderBy('nombre')->get() : collect(),
        ]);
    }

    public function create(): View
    {
        return view('tienda.productos.create', [
            'categorias' => ProductoTienda::categorias(),
        ]);
    }

    public function store(StoreProductoTiendaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['imagenes']);
        $data['activo'] = $request->boolean('activo', true);

        $p = ProductoTienda::query()->create($data);
        $this->guardarImagenes($p, $request);

        RegistroActividad::registrar('tienda_producto_alta', 'Producto tienda «'.$p->nombre.'» (ID '.$p->id.').');

        return redirect()->route('tienda.productos.index')->with('status', 'Producto creado.');
    }

    public function edit(ProductoTienda $productoTienda): View
    {
        return view('tienda.productos.edit', [
            'producto' => $productoTienda,
            'categorias' => ProductoTienda::categorias(),
        ]);
    }

    public function update(UpdateProductoTiendaRequest $request, ProductoTienda $productoTienda): RedirectResponse
    {
        $data = $request->validated();
        unset($data['imagenes'], $data['eliminar_imagenes']);
        $data['activo'] = $request->boolean('activo', true);

        $productoTienda->update($data);
        $this->eliminarImagenesMarcadas($productoTienda, $request);
        $this->guardarImagenes($productoTienda, $request);
        $this->sincronizarImagenPrincipal($productoTienda);

        return redirect()->route('tienda.productos.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(ProductoTienda $productoTienda): RedirectResponse
    {
        foreach ($productoTienda->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->archivo_path);
        }
        if ($productoTienda->imagen_path) {
            Storage::disk('public')->delete($productoTienda->imagen_path);
        }
        $productoTienda->delete();

        return redirect()->route('tienda.productos.index')->with('status', 'Producto eliminado.');
    }

    private function guardarImagenes(ProductoTienda $producto, Request $request): void
    {
        if (! $request->hasFile('imagenes')) {
            $this->sincronizarImagenPrincipal($producto);

            return;
        }

        $orden = (int) $producto->imagenes()->max('orden');
        foreach ($request->file('imagenes', []) as $imagen) {
            $orden++;
            $path = $imagen->store('tienda-productos', 'public');

            ProductoTiendaImagen::query()->create([
                'producto_tienda_id' => $producto->id,
                'archivo_path' => $path,
                'orden' => $orden,
                'es_principal' => false,
            ]);
        }

        $this->sincronizarImagenPrincipal($producto);
    }

    private function eliminarImagenesMarcadas(ProductoTienda $producto, Request $request): void
    {
        $ids = collect($request->input('eliminar_imagenes', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();
        if ($ids->isEmpty()) {
            return;
        }

        $imagenes = $producto->imagenes()->whereIn('id', $ids)->get();
        foreach ($imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->archivo_path);
            $imagen->delete();
        }
    }

    private function sincronizarImagenPrincipal(ProductoTienda $producto): void
    {
        $principal = $producto->imagenes()->orderBy('es_principal', 'desc')->orderBy('orden')->first();
        $producto->imagenes()->update(['es_principal' => false]);

        if ($principal) {
            $principal->update(['es_principal' => true]);
            $producto->updateQuietly(['imagen_path' => $principal->archivo_path]);

            return;
        }

        $producto->updateQuietly(['imagen_path' => null]);
    }
}
