<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Models\SecretariaPlantillaDocumental;
use App\Models\User;
use App\Services\Secretaria\PlantillaPdfRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Services\Secretaria\DocxPlantillaImportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlantillaDocumentalController extends Controller
{
    public function __construct(
        private readonly PlantillaPdfRenderer $pdfRenderer
    ) {}

    public function index(): View
    {
        $variables = [
            'Hermano' => ['{{nombre}}', '{{apellidos}}', '{{dni}}', '{{num_hermano}}', '{{antiguedad_años}}', '{{fecha_alta}}', '{{direccion_completa}}'],
            'Hermandad' => ['{{nombre_hermandad}}', '{{ejercicio_actual}}', '{{fecha_hoy}}'],
            'Cargos' => ['{{hermano_mayor}}', '{{secretario}}'],
        ];

        return view('secretaria.plantillas.index', [
            'plantillas' => SecretariaPlantillaDocumental::query()->latest()->get(),
            'hermanos' => Hermano::query()->orderBy('apellidos')->limit(200)->get(),
            'variablesPanel' => $variables,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:40'],
            'cuerpo_plantilla' => ['required', 'string'],
            'marca_agua' => ['nullable', 'string', 'max:120'],
            'marca_agua_archivo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
        ]);

        $marcaAguaPath = null;
        if ($request->hasFile('marca_agua_archivo')) {
            $marcaAguaPath = $request->file('marca_agua_archivo')->store('secretaria/plantillas/marcas-agua', 'public');
        }

        SecretariaPlantillaDocumental::query()->create([
            ...$data,
            'marca_agua_path' => $marcaAguaPath,
            'creado_por_user_id' => $request->user()?->id,
            'activa' => true,
        ]);

        return back()->with('status', 'Plantilla guardada.');
    }

    public function generarPdf(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'plantilla_id' => ['required', 'integer', 'exists:secretaria_plantillas_documentales,id'],
            'hermano_id' => ['required', 'integer', 'exists:hermanos,id'],
        ]);

        $plantilla = SecretariaPlantillaDocumental::query()->findOrFail($data['plantilla_id']);
        $hermano = Hermano::query()->findOrFail($data['hermano_id']);
        $contenido = $this->renderPlantilla($plantilla->cuerpo_plantilla, $hermano);
        $config = ConfiguracionHermandad::query()->first();
        $escudoUrl = $this->imageDataUriFromPublicPath($config?->escudo_path);
        $watermarkUrl = $this->imageDataUriFromPublicPath($plantilla->marca_agua_path);

        $viewData = [
            'contenido' => $contenido,
            'titulo' => $plantilla->nombre,
            'marcaAgua' => $plantilla->marca_agua ?: 'Hermandad',
            'marcaAguaPath' => $watermarkUrl,
            'escudoUrl' => $escudoUrl,
            'nombreHermandad' => $config?->nombre_hermandad ?: 'Hermandad',
        ];

        $html = view('secretaria.plantillas.pdf-documento', $viewData)->render();
        $browsershotPdf = $this->pdfRenderer->render($html);
        if (is_string($browsershotPdf) && $browsershotPdf !== '') {
            return response()->streamDownload(function () use ($browsershotPdf): void {
                echo $browsershotPdf;
            }, 'plantilla-'.$plantilla->id.'-hermano-'.$hermano->id.'.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        }

        $pdf = Pdf::loadView('secretaria.plantillas.pdf-documento', $viewData)->setPaper('a4', 'portrait');

        return $pdf->download('plantilla-'.$plantilla->id.'-hermano-'.$hermano->id.'.pdf');
    }

    public function previsualizar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cuerpo_plantilla' => ['required', 'string'],
            'hermano_id' => ['required', 'integer', 'exists:hermanos,id'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'marca_agua' => ['nullable', 'string', 'max:120'],
        ]);

        $hermano = Hermano::query()->findOrFail($data['hermano_id']);
        $config = ConfiguracionHermandad::query()->first();
        $contenido = $this->renderPlantilla($data['cuerpo_plantilla'], $hermano);
        $escudoUrl = $this->imageDataUriFromPublicPath($config?->escudo_path);

        $html = view('secretaria.plantillas.pdf-documento', [
            'contenido' => $contenido,
            'titulo' => $data['titulo'] ?: 'Previsualización',
            'marcaAgua' => $data['marca_agua'] ?: 'Hermandad',
            'marcaAguaPath' => null,
            'escudoUrl' => $escudoUrl,
            'nombreHermandad' => $config?->nombre_hermandad ?: 'Hermandad',
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function importarDocx(Request $request, DocxPlantillaImportService $importador): JsonResponse
    {
        $request->validate([
            'archivo_docx' => ['required', 'file', 'mimes:docx', 'max:10240'],
        ]);

        $tmp = $request->file('archivo_docx')->store('tmp/plantillas-docx', 'local');
        $absolute = Storage::disk('local')->path($tmp);

        try {
            $resultado = $importador->importarComoHtml($absolute);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($tmp);

            return response()->json([
                'message' => 'No se pudo importar el .docx. Verifica que el archivo no esté dañado.',
            ], 422);
        }

        Storage::disk('local')->delete($tmp);

        return response()->json($resultado);
    }

    private function renderPlantilla(string $template, Hermano $hermano): string
    {
        $config = ConfiguracionHermandad::query()->first();
        $antiguedad = $hermano->fecha_alta ? $hermano->fecha_alta->diffInYears(now()) : 0;
        $secretario = User::role('Secretaría')->first()?->name ?? 'Secretaría';
        $hermanoMayor = User::role('Hermano Mayor')->first()?->name ?? 'Hermano Mayor';

        $reemplazos = [
            '{{nombre}}' => (string) $hermano->nombre,
            '{{apellidos}}' => (string) $hermano->apellidos,
            '{{dni}}' => (string) $hermano->dni,
            '{{num_hermano}}' => (string) $hermano->numero_hermano,
            '{{antiguedad_años}}' => (string) $antiguedad,
            '{{fecha_alta}}' => $hermano->fecha_alta?->format('d/m/Y') ?? '-',
            '{{direccion_completa}}' => trim((string) $hermano->direccion.' '.(string) $hermano->codigo_postal.' '.(string) $hermano->localidad.' '.(string) $hermano->provincia),
            '{{nombre_hermandad}}' => (string) ($config?->nombre_hermandad ?? 'Hermandad'),
            '{{ejercicio_actual}}' => now()->format('Y'),
            '{{fecha_hoy}}' => now()->format('d/m/Y'),
            '{{hermano_mayor}}' => $hermanoMayor,
            '{{secretario}}' => $secretario,
            // Compatibilidad con la primera versión
            '{{nombre_hermano}}' => $hermano->nombreCompleto(),
            '{{dni_hermano}}' => (string) $hermano->dni,
            '{{antiguedad_hermano}}' => (string) $antiguedad,
        ];

        return strtr($template, $reemplazos);
    }

    private function imageDataUriFromPublicPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $content = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }
}
