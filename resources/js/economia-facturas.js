import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function mapProveedorToForm(p) {
    return {
        razon_social: p.razon_social ?? '',
        nombre_comercial: p.nombre_comercial ?? '',
        tipo_persona: p.tipo_persona ?? 'juridica',
        nif_cif: p.nif_cif ?? '',
        direccion: p.direccion ?? '',
        codigo_postal: p.codigo_postal ?? '',
        municipio: p.municipio ?? '',
        provincia: p.provincia ?? '',
        pais: (p.pais ?? 'ES').toString().toUpperCase().slice(0, 2),
        telefono: p.telefono ?? '',
        email: p.email ?? '',
        regimen_iva: p.regimen_iva ?? '',
        iban: p.iban ?? '',
        notas: p.notas ?? '',
    };
}

export default function registerEconomiaFacturas(Alpine) {
    Alpine.data('facturasProveedoresPage', (config) => ({
        modalOpen: false,
        modalMode: 'create',
        editingId: null,
        formErrors: {},
        saving: false,
        tom: null,
        form: mapProveedorToForm({}),

        init() {
            this.$nextTick(() => {
                const el = this.$refs.proveedorSelect;
                if (!el) {
                    return;
                }
                this.tom = new TomSelect(el, {
                    wrapperClass: 'ts-wrapper form-select-premium',
                    plugins: ['clear_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    loadThrottle: 250,
                    maxOptions: 100,
                    shouldLoad() {
                        return true;
                    },
                    load: (query, callback) => {
                        const url = new URL(config.buscarUrl, window.location.origin);
                        url.searchParams.set('q', query ?? '');
                        fetch(url, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        })
                            .then((r) => r.json())
                            .then((json) => callback(json))
                            .catch(() => callback());
                    },
                });
            });
        },

        resetForm() {
            this.formErrors = {};
            this.editingId = null;
            this.form = mapProveedorToForm({});
        },

        abrirNuevo() {
            this.modalMode = 'create';
            this.resetForm();
            this.modalOpen = true;
        },

        async abrirEditar() {
            const id = this.tom?.getValue();
            if (!id) {
                window.alert('Seleccione un proveedor en el filtro.');
                return;
            }
            this.modalMode = 'edit';
            this.formErrors = {};
            this.editingId = id;
            try {
                const r = await fetch(`${config.proveedorBaseUrl}/${id}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await r.json();
                if (data.proveedor) {
                    this.form = mapProveedorToForm(data.proveedor);
                }
                this.modalOpen = true;
            } catch {
                window.alert('No se pudo cargar el proveedor.');
            }
        },

        err(key) {
            const e = this.formErrors[key];
            return Array.isArray(e) ? e[0] : e ?? '';
        },

        async guardarProveedor() {
            this.saving = true;
            this.formErrors = {};
            const url =
                this.modalMode === 'create'
                    ? config.storeUrl
                    : `${config.proveedorBaseUrl}/${this.editingId}`;
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';
            try {
                const r = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await r.json().catch(() => ({}));
                if (!r.ok) {
                    this.formErrors = data.errors ?? {};
                    this.saving = false;
                    return;
                }
                if (data.option) {
                    this.tom.addOption(data.option);
                    this.tom.refreshOptions(false);
                    this.tom.setValue(data.option.value, true);
                }
                this.modalOpen = false;
            } catch {
                window.alert('Error de red.');
            }
            this.saving = false;
        },

        async eliminarProveedor() {
            if (this.modalMode !== 'edit' || !this.editingId) {
                return;
            }
            if (
                !window.confirm(
                    '¿Eliminar este proveedor? Los documentos dejarán de estar vinculados; el texto libre del asiento, si existía, se mantiene.'
                )
            ) {
                return;
            }
            this.saving = true;
            try {
                const r = await fetch(`${config.proveedorBaseUrl}/${this.editingId}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
                if (r.ok) {
                    const id = this.editingId;
                    this.tom.removeOption(id);
                    if (this.tom.getValue() === id) {
                        this.tom.clear(true);
                    }
                    this.modalOpen = false;
                } else {
                    const data = await r.json().catch(() => ({}));
                    window.alert(data.message ?? 'No se pudo eliminar.');
                }
            } catch {
                window.alert('No se pudo eliminar.');
            }
            this.saving = false;
        },
    }));
}
