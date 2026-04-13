<x-app-layout>
    <x-slot name="header"><span class="text-xs uppercase tracking-wider text-slate-500">Secretaría · Plantillas</span></x-slot>
    <div class="py-8" x-data="plantillasEditor({
        previewUrl: '{{ route('secretaria.plantillas.preview') }}',
        importUrl: '{{ route('secretaria.plantillas.importar-docx') }}',
        csrf: '{{ csrf_token() }}'
    })">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Gestor profesional de plantillas y modelos</h1>
            @if (session('status'))<div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">
                <form method="POST" action="{{ route('secretaria.plantillas.store') }}" enctype="multipart/form-data" class="xl:col-span-3 card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input name="nombre" class="input-premium" placeholder="Nombre de plantilla" required />
                        <input name="tipo" class="input-premium" placeholder="Tipo (certificado, saluda, cabildo...)" required />
                        <input name="marca_agua" class="input-premium md:col-span-2" placeholder="Marca de agua textual (opcional)" />
                    </div>

                    <div x-data="{ over:false, nombre:'' }">
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Marca de agua por imagen (escudo)</label>
                        <div class="rounded-xl border-2 border-dashed border-[color:var(--color-accent)] bg-slate-50 p-4 text-center cursor-pointer"
                             :class="over ? 'bg-amber-50' : ''"
                             @click="$refs.marca.click()"
                             @dragover.prevent="over=true"
                             @dragleave.prevent="over=false"
                             @drop.prevent="over=false; $refs.marca.files = $event.dataTransfer.files; nombre = $event.dataTransfer.files?.[0]?.name || ''">
                            <p class="text-sm font-semibold text-[color:var(--color-primary)]">Arrastra imagen para marca de agua</p>
                            <p class="text-xs text-slate-500">PNG/JPG · opacidad suave automática en PDF</p>
                            <p x-show="nombre" x-cloak class="mt-1 text-xs text-emerald-700" x-text="'Archivo: ' + nombre"></p>
                        </div>
                        <input x-ref="marca" type="file" name="marca_agua_archivo" class="hidden" @change="nombre = $event.target.files?.[0]?.name || ''">
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn-soft text-xs" @click="$refs.docxInput.click()">📂 Importar desde mi PC</button>
                        <button type="button" class="btn-soft text-xs" @click="cargarPredeterminada('certificado')">Certificado de Antigüedad</button>
                        <button type="button" class="btn-soft text-xs" @click="cargarPredeterminada('saluda')">Saluda Oficial</button>
                        <button type="button" class="btn-soft text-xs" @click="cargarPredeterminada('cabildo')">Citación a Cabildo</button>
                    </div>
                    <div x-data="{ over:false }"
                         class="rounded-xl border-2 border-dashed border-[color:var(--color-accent)] bg-slate-50 p-4 text-center"
                         :class="over ? 'bg-amber-50' : ''"
                         @dragover.prevent="over=true"
                         @dragleave.prevent="over=false"
                         @drop.prevent="over=false; importarDocx($event.dataTransfer.files?.[0])">
                        <p class="text-sm font-semibold text-[color:var(--color-primary)]">Arrastra aquí un archivo .docx</p>
                        <p class="text-xs text-slate-500">Se importará al editor manteniendo estructura y estilos compatibles</p>
                        <input x-ref="docxInput" type="file" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="hidden" @change="importarDocx($event.target.files?.[0])">
                    </div>
                    <div x-show="sugerencias.length" x-cloak class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <p class="text-xs font-semibold uppercase text-amber-800">Sugerencias de mapeo detectadas</p>
                        <ul class="text-xs text-amber-900 mt-1 space-y-1">
                            <template x-for="s in sugerencias" :key="s.token">
                                <li><span x-text="s.token"></span> -> <span class="font-semibold" x-text="s.sugerida"></span></li>
                            </template>
                        </ul>
                        <button type="button" class="btn-soft text-xs mt-2" @click="reemplazarTokensDetectados()">Reemplazar tokens detectados (1 clic)</button>
                    </div>

                    <div class="bg-slate-100 rounded-xl p-4 overflow-auto">
                        <div id="editor-plantilla-wrapper" class="mx-auto bg-white shadow border border-slate-200" style="width: 794px; min-height: 1123px;">
                            <textarea id="editor-plantilla"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="cuerpo_plantilla" x-ref="cuerpoPlantilla" required>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn-soft" @click="previsualizar()">👁️ Previsualizar con datos reales</button>
                        <button type="submit" class="btn-accent" @click="sincronizarEditor()">Guardar plantilla</button>
                    </div>
                </form>

                <aside class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)]">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-[color:var(--color-primary)] mb-3">Variables un-clic</h2>
                    <div class="space-y-3 max-h-[70vh] overflow-auto pr-1">
                        @foreach($variablesPanel as $grupo => $vars)
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase mb-1">{{ $grupo }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($vars as $var)
                                        <button type="button" class="text-xs px-2 py-1 rounded-full border border-slate-200 hover:border-[color:var(--color-accent)]" @click="insertarVariable('{{ $var }}')">{{ $var }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>

            <form method="POST" action="{{ route('secretaria.plantillas.pdf') }}" class="card-premium p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <select name="plantilla_id" class="input-premium" required>
                    <option value="">Selecciona plantilla</option>
                    @foreach($plantillas as $p)<option value="{{ $p->id }}">{{ $p->nombre }}</option>@endforeach
                </select>
                <select name="hermano_id" class="input-premium md:col-span-2" x-model="hermanoPreviewId" required>
                    <option value="">Selecciona hermano</option>
                    @foreach($hermanos as $h)<option value="{{ $h->id }}">{{ $h->numero_hermano }} · {{ $h->nombreCompleto() }}</option>@endforeach
                </select>
                <div class="md:col-span-3"><button class="btn-soft">Exportar PDF de alta fidelidad (A4)</button></div>
            </form>
        </div>

        <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
            <div class="bg-white w-full max-w-5xl h-[88vh] rounded-xl shadow-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <p class="font-semibold text-[color:var(--color-primary)]">Previsualización con datos reales</p>
                    <button class="btn-soft text-xs" @click="previewOpen=false">Cerrar</button>
                </div>
                <iframe class="w-full h-[calc(88vh-56px)]" :srcdoc="previewHtml"></iframe>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            #editor-plantilla-wrapper .ck-editor {
                height: 1123px;
                display: flex;
                flex-direction: column;
            }
            #editor-plantilla-wrapper .ck-editor__main {
                flex: 1 1 auto;
                display: flex;
                flex-direction: column;
                min-height: 0;
            }
            #editor-plantilla-wrapper .ck-editor__editable_inline {
                flex: 1 1 auto;
                min-height: 980px;
                max-height: none;
                padding: 18mm 16mm;
                box-sizing: border-box;
            }
        </style>
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/classic/ckeditor.js"></script>
        <script>
            function plantillasEditor(cfg) {
                let editorInstance = null;
                return {
                    previewOpen: false,
                    previewHtml: '',
                    sugerencias: [],
                    hermanoPreviewId: '',
                    templates: {
                        certificado: `<p style="text-align:right">@{{fecha_hoy}}</p><p><strong>SECRETARÍA DE @{{nombre_hermandad}}</strong></p><p>Certifico que D./Dña. @{{nombre}} @{{apellidos}}, con DNI @{{dni}} y número de hermano @{{num_hermano}}, consta de alta desde @{{fecha_alta}}, con una antigüedad de @{{antiguedad_años}} años.</p><p>Y para que conste, firmo el presente certificado.</p><p>Fdo.: @{{secretario}}</p>`,
                        saluda: `<p><strong>@{{hermano_mayor}}</strong>, Hermano Mayor de @{{nombre_hermandad}}, saluda atentamente a D./Dña. @{{nombre}} @{{apellidos}}.</p><p>Es grato comunicarle su invitación oficial al acto correspondiente del ejercicio @{{ejercicio_actual}}.</p><p>@{{fecha_hoy}}</p>`,
                        cabildo: `<p><strong>Citación a Cabildo</strong></p><p>Se cita a D./Dña. @{{nombre}} @{{apellidos}} (n.º @{{num_hermano}}) al Cabildo General en la fecha que acuerde Secretaría.</p><p>Dirección de notificación: @{{direccion_completa}}.</p><p>En @{{nombre_hermandad}}, a @{{fecha_hoy}}.</p><p>Secretario: @{{secretario}}</p>`
                    },
                    init() {
                        const el = document.querySelector('#editor-plantilla');
                        if (!el || el.dataset.editorMounted === '1') {
                            return;
                        }
                        el.dataset.editorMounted = '1';

                        // Evita doble toolbar cuando Alpine rehidrata el bloque
                        document.querySelectorAll('.ck-editor').forEach((node, idx) => {
                            if (idx > 0) node.remove();
                        });

                        ClassicEditor.create(el, {
                            // Barra compatible con el build CDN clásico
                            toolbar: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', '|', 'insertTable', '|', 'bulletedList', 'numberedList', '|', 'link']
                        }).then(editor => {
                            editorInstance = editor;
                            this.sincronizarEditor();
                        }).catch(() => {
                            el.dataset.editorMounted = '0';
                        });
                    },
                    sincronizarEditor() {
                        if (editorInstance) {
                            this.$refs.cuerpoPlantilla.value = editorInstance.getData();
                        }
                    },
                    insertarVariable(token) {
                        if (!editorInstance) return;
                        editorInstance.model.change(writer => {
                            const insertAt = editorInstance.model.document.selection.getFirstPosition();
                            writer.insertText(token, insertAt);
                        });
                        this.sincronizarEditor();
                    },
                    cargarPredeterminada(tipo) {
                        if (!editorInstance || !this.templates[tipo]) return;
                        editorInstance.setData(this.templates[tipo]);
                        this.sincronizarEditor();
                    },
                    async previsualizar() {
                        this.sincronizarEditor();
                        if (!this.hermanoPreviewId) {
                            alert('Selecciona un hermano para previsualizar.');
                            return;
                        }
                        const res = await fetch(cfg.previewUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                            body: JSON.stringify({
                                cuerpo_plantilla: this.$refs.cuerpoPlantilla.value,
                                hermano_id: this.hermanoPreviewId,
                                titulo: document.querySelector('input[name="nombre"]')?.value || 'Previsualización',
                                marca_agua: document.querySelector('input[name="marca_agua"]')?.value || ''
                            })
                        });
                        const data = await res.json();
                        this.previewHtml = data.html || '<p>Error previsualizando</p>';
                        this.previewOpen = true;
                    },
                    async importarDocx(file) {
                        if (!file) return;
                        if (!file.name.toLowerCase().endsWith('.docx')) {
                            alert('Solo se admite formato .docx');
                            return;
                        }
                        const fd = new FormData();
                        fd.append('archivo_docx', file);
                        const res = await fetch(cfg.importUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                            body: fd
                        });
                        if (!res.ok) {
                            alert('No se pudo importar el .docx');
                            return;
                        }
                        const data = await res.json();
                        if (editorInstance && data.html) {
                            editorInstance.setData(data.html);
                            this.sincronizarEditor();
                        }
                        this.sugerencias = Array.isArray(data.sugerencias) ? data.sugerencias : [];
                    },
                    reemplazarTokensDetectados() {
                        if (!editorInstance || !this.sugerencias.length) return;
                        let html = editorInstance.getData();
                        this.sugerencias.forEach((s) => {
                            if (!s.token || !s.sugerida) return;
                            html = html.split(s.token).join(s.sugerida);
                        });
                        editorInstance.setData(html);
                        this.sincronizarEditor();
                    }
                }
            }
            document.addEventListener('alpine:init', () => {});
        </script>
    @endpush
</x-app-layout>
