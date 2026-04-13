export default function registerEconomiaAsientoLibroDiario(Alpine) {
    Alpine.data('asientoLibroDiario', (config) => ({
        showFilters: Boolean(config?.initialShowFilters),
        mode: 'create',
        asientoId: null,
        fecha: '',
        glosa: '',
        iaDescripcion: '',
        iaIvaModo: 'auto',
        iaLoading: false,
        iaError: '',
        lines: [],
        cfg: config,

        get formAction() {
            return this.mode === 'edit' && this.asientoId
                ? `${this.cfg.asientosBaseUrl}/${this.asientoId}`
                : this.cfg.createUrl;
        },

        init() {
            if (this.cfg.validation?.hasErrors) {
                this.applyOldInputFromServer();
                this.$nextTick(() => this.$dispatch('open-modal', 'asiento-contable'));
            } else {
                this.resetCreate();
            }
        },

        applyOldInputFromServer() {
            const o = this.cfg.old || {};
            this.mode = o.mode === 'edit' ? 'edit' : 'create';
            this.asientoId = o.asiento_id ? Number(o.asiento_id) : null;
            this.fecha = o.fecha || '';
            this.glosa = o.glosa || '';
            const ap = Array.isArray(o.apuntes) ? o.apuntes : [];
            if (ap.length) {
                this.lines = ap.map((p) => ({
                    cuenta_contable_id: String(p.cuenta_contable_id ?? ''),
                    cuenta_label: '',
                    tipo: p.cuenta_tipo || '',
                    q: '',
                    open: false,
                    resultados: [],
                    debe: p.debe !== undefined && p.debe !== '' && Number(p.debe) > 0 ? String(p.debe) : '',
                    haber: p.haber !== undefined && p.haber !== '' && Number(p.haber) > 0 ? String(p.haber) : '',
                    concepto: p.concepto_detalle ?? '',
                    factura_proveedor: p.factura_proveedor || '',
                    factura_estado: p.factura_estado || 'Pendiente',
                    tiene_documento: !!p.tiene_documento,
                }));
            } else {
                this.lines = [this.blankLine(), this.blankLine()];
            }
        },

        blankLine() {
            return {
                cuenta_contable_id: '',
                cuenta_label: '',
                tipo: '',
                q: '',
                open: false,
                resultados: [],
                debe: '',
                haber: '',
                concepto: '',
                factura_proveedor: '',
                factura_estado: 'Pendiente',
                tiene_documento: false,
            };
        },

        resetCreate() {
            this.mode = 'create';
            this.asientoId = null;
            const t = new Date();
            this.fecha = t.toISOString().slice(0, 10);
            this.glosa = '';
            this.iaDescripcion = '';
            this.iaIvaModo = 'auto';
            this.iaError = '';
            this.iaLoading = false;
            this.lines = [this.blankLine(), this.blankLine()];
        },

        openFromEvent(detail) {
            if (detail && detail.mode === 'create') {
                this.resetCreate();
                this.$dispatch('open-modal', 'asiento-contable');
            }
        },

        openEdit(id, fromValidation = false) {
            const row = this.cfg.asientosPayload.find((a) => String(a.id) === String(id));
            if (!row || !row.ejercicio_abierto) return;
            this.mode = 'edit';
            this.asientoId = id;
            this.fecha = row.fecha;
            this.glosa = row.glosa;
            this.lines = row.apuntes && row.apuntes.length
                ? row.apuntes.map((p) => ({
                    cuenta_contable_id: String(p.cuenta_contable_id),
                    cuenta_label: p.cuenta_label || '',
                    tipo: p.cuenta_tipo || '',
                    q: '',
                    open: false,
                    resultados: [],
                    debe: p.debe > 0 ? String(p.debe) : '',
                    haber: p.haber > 0 ? String(p.haber) : '',
                    concepto: p.concepto_detalle || '',
                    factura_proveedor: p.factura_proveedor || '',
                    factura_estado: p.factura_estado || 'Pendiente',
                    tiene_documento: !!p.tiene_documento,
                }))
                : [this.blankLine(), this.blankLine()];
            if (!fromValidation) {
                this.$dispatch('open-modal', 'asiento-contable');
            }
        },

        addLine() {
            this.lines.push(this.blankLine());
        },

        removeLine(i) {
            if (this.lines.length > 2) this.lines.splice(i, 1);
        },

        totalDebe() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.debe) || 0), 0);
        },

        totalHaber() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.haber) || 0), 0);
        },

        diff() {
            return Math.round((this.totalDebe() - this.totalHaber()) * 100) / 100;
        },

        cuadrado() {
            return Math.abs(this.diff()) < 0.005;
        },

        formatMoney(n) {
            return (Number(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        puedeGuardar() {
            if (!this.glosa || !this.glosa.trim() || !this.fecha) return false;
            if (!this.cuadrado()) return false;
            for (const l of this.lines) {
                if (!l.cuenta_contable_id) return false;
                const d = parseFloat(l.debe) || 0;
                const h = parseFloat(l.haber) || 0;
                if (d <= 0 && h <= 0) return false;
                if (d > 0 && h > 0) return false;
            }
            return true;
        },

        async buscarCuenta(idx) {
            const line = this.lines[idx];
            line.open = true;
            const q = (line.q || '').trim();
            if (q.length < 1) {
                line.resultados = [];
                return;
            }
            const url = `${this.cfg.cuentasSearchUrl}?q=${encodeURIComponent(q)}`;
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            line.resultados = data.cuentas || [];
        },

        pickCuenta(idx, c) {
            const line = this.lines[idx];
            line.cuenta_contable_id = String(c.id);
            line.cuenta_label = c.label;
            line.tipo = c.tipo || '';
            line.q = '';
            line.open = false;
            line.resultados = [];
        },

        esGastoDebe(line) {
            return line.tipo === 'Gasto' && (parseFloat(line.debe) || 0) > 0;
        },

        onImporte(idx, campo) {
            const line = this.lines[idx];
            if (campo === 'debe' && (parseFloat(line.debe) || 0) > 0) line.haber = '';
            if (campo === 'haber' && (parseFloat(line.haber) || 0) > 0) line.debe = '';
        },

        async generarConIA() {
            this.iaError = '';
            const descripcion = (this.iaDescripcion || '').trim();
            if (descripcion.length < 12) {
                this.iaError = 'Describa el hecho con más detalle (mínimo 12 caracteres).';
                return;
            }
            this.iaLoading = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(this.cfg.aiGenerateUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        descripcion,
                        tratamiento_iva: this.iaIvaModo || 'auto',
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    const msg = data?.errors?.descripcion?.[0] || data?.message || 'No se pudo generar el asiento.';
                    throw new Error(msg);
                }
                const lineas = Array.isArray(data.lineas) ? data.lineas : [];
                if (!lineas.length) {
                    throw new Error('La IA no devolvió líneas válidas.');
                }
                this.glosa = data.glosa || this.glosa || descripcion.slice(0, 150);
                this.lines = lineas.map((l) => ({
                    cuenta_contable_id: String(l.cuenta_contable_id || ''),
                    cuenta_label: l.cuenta_label || '',
                    tipo: l.cuenta_tipo || '',
                    q: '',
                    open: false,
                    resultados: [],
                    debe: Number(l.debe || 0) > 0 ? String(l.debe) : '',
                    haber: Number(l.haber || 0) > 0 ? String(l.haber) : '',
                    concepto: l.concepto_detalle || '',
                    factura_proveedor: '',
                    factura_estado: 'Pendiente',
                    tiene_documento: false,
                }));
            } catch (e) {
                this.iaError = e?.message || 'Error al generar el asiento con IA.';
            } finally {
                this.iaLoading = false;
            }
        },
    }));
}
