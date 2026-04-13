# Esquema Técnico: Sistema de Gestión de Hermandades (Breeze + Blade + Alpine)

## 1. Stack Tecnológico
- **Base:** Laravel 11 + Laravel Breeze (vistas Blade).
- **Frontend:** Tailwind CSS (Mobile First) + Alpine.js (para lógica de modales y toggles).
- **Arquitectura:** Multitenancy por subdominio (basado en base de datos única con `hermandad_id` o bases de datos separadas).
- **Interactividad:** Modales controlados por Alpine.js y envío de formularios via POST/PUT tradicionales.

---

## 2. Base de Datos y Modelos Críticos

### A. Gestión de Hermanos (Core)
- **Campos Técnicos:** `id`, `hermandad_id`, `numero_hermano` (indexado), `estado` (activo/baja/difunto).
- **Información Personal:** Nombre, apellidos, DNI/NIE, fecha_nacimiento, sexo.
- **Contacto:** Dirección, Localidad, CP, Provincia, Teléfono, Email.
- **Económicos:** `tipo_pago` (banco/efectivo), `iban`, `bic`, `titular_cuenta`.
- **Religiosos:** `fecha_bautismo`, `parroquia_bautismo`, `fecha_alta_hermandad`.
- **Documentación:** Sistema de archivos para DNI escaneado y Partida de Bautismo.

### B. Gestión Económica (ERP Profesional)
Siguiendo el Plan General Contable (PGC) para Entidades sin Fines Lucrativos en España:
- **Plan de Cuentas:** Grupos 1-7 adaptados.
- **Libro Diario:** `asientos` (id, fecha, glosa) y `apuntes` (asiento_id, cuenta_id, debe, haber).
- **Facturas de gasto (documentos):** `documentos_gasto` enlazados a asiento/línea; texto libre `proveedor` desde el asiento y opcionalmente `proveedor_id` → `proveedores`.
- **Proveedores:** `proveedores` con datos fiscales ES (razón social, nombre comercial, tipo persona jurídica/física/autónomo, NIF/CIF/NIE, domicilio fiscal, régimen de IVA, IBAN, contacto).
- **Remesas SEPA:** Generación de archivos XML (Cuaderno 19) para el cobro de cuotas.

### C. Gestión de Patrimonio (Inventario)
- **Clasificación:** 1. Categoría (Orfebrería, Bordado, Talla, Textil).
    2. Ubicación (Almacén, Casa Hermandad, Paso de Cristo, Paso de Virgen).
- **Ficha Técnica:** Autor, año, materiales, dimensiones, estado de conservación (Semáforo: Bueno/Regular/Malo), última restauración.

### D. Estación de Penitencia
- **`configuracion_salidas`:** Una fila por **año natural** (`año` único): fecha de salida, donativo por defecto, fechas de reparto de papeletas, notas, bandera `activa` (campaña de salida). **No se borra** al cambiar de ejercicio; se edita desde Salida → Configuración eligiendo el año.
- **`insignias`:** Catálogo reutilizable (nombre, tramo Cristo/Virgen/General, cupos). No está versionado por año.
- **`papeletas_sitio`:** Por `hermano_id` + `ejercicio_id` (único): puesto, tramo, `insignia_id` opcional, donativo, estado (Solicitada/Emitida/Anulada), asistencia. **Histórico conservado** mientras exista el ejercicio.
- **`tunicas`:** Inventario (código, talla, estado, `hermano_id` opcional, fianza, fechas préstamo/devolución).

### E. Informes y censo
- **Censo electoral:** Filtros por fecha de informe, antigüedad mínima (`configuracion_hermandad.censo_antiguedad_anos`), estado Alta, mayoría de edad; opción excluir morosos (lotería sin cobrar y cuota ordinaria pendiente). PDF con DNI enmascarado.
- **Etiquetas / mailing:** Hermanos en alta; todos o cabeza de familia por domicilio; filtro CP; PDF rejilla A4; CSV UTF-8.

### F. Cuotas–contabilidad y auditoría (v1.0)
- **Hermanos:** `estado_cuota` (`Al_corriente` | `Pendiente`), `cuota_pendiente_ejercicio_id` (FK ejercicio). Pendiente al generar asiento masivo de cuotas (debe 430/431) desde Economía; vuelve a Al corriente con asiento cobro Debe 572 / Haber 430–431 y concepto con `n.º {numero_hermano}`.
- **Actividades:** tabla `actividades` (usuario, acción, descripción, fecha) para auditoría de acciones críticas.
- **Ajustes:** estado del sistema (storage, ejercicios, datos incompletos), registro de actividad, renumeración de `numero_hermano` por `fecha_alta` (irreversible, confirmación `REORDENAR`).

### G. Comunicación y archivo digital (v1.1)
- **`comunicados_masivos`:** asunto, `cuerpo_html`, `filtro_envio` (`todos` | `con_deuda` | `tramo_cofradia` | `solo_costaleros`), `filtro_tramo_valor` (opcional), estado de cola, totales. Envío en **jobs** por lotes.
- **`comunicado_masivo_destinatarios`:** por comunicado y hermano; `tracking_token` (UUID) para pixel de apertura; `correo_enviado_en`, `abierto_en`, `aperturas_count`, `ultima_apertura_ip`.
- **`documentos_archivo`:** título, `categoria` (reglas, actas, inventario artístico, boletín), `nivel_acceso` (`junta_gobierno` | `publico_hermanos`), fichero en disco (`archivo_path`).
- **`firma_conformidad_solicitudes`:** texto para el hermano, opcional `documento_archivo_id` (solo documentos públicos), estado `pendiente`/`firmado`, `firmado_en`, `firmado_ip`.
- **`avisos` (ampliado):** `urgente`, `visible_tablon` (tablón en inicio del portal).

### H. Tienda y ventas (TPV + portal)
- **`productos_tienda`:** catálogo (nombre, categoría, `precio_venta` TTC, `precio_coste`, `iva_porcentaje`, `stock_actual`, `stock_minimo`, `sku`, `imagen_path` principal de compatibilidad, `activo`).
- **`producto_tienda_imagenes`:** galería de producto (`producto_tienda_id`, `archivo_path`, `orden`, `es_principal`) para soportar carga múltiple y vista museística en catálogo/portal.
- **`ventas_tienda` / `venta_tienda_lineas`:** cobros TPV o portal Bizum (`folio`, `metodo_pago` efectivo/tarjeta/bizum, `venta_anonima`, `hermano_id` opcional, totales base/IVA/TTC, `asiento_id`, `user_id` cajero nullable en portal). Ticket térmico 80 mm en PDF (`tienda/ventas/{venta}/ticket`).
- **`aperturas_caja_tienda`:** una por fecha — saldo inicial en efectivo al abrir; el cierre calcula esperado = inicial + ventas en efectivo del día.
- **`cierres_caja_tienda`:** teóricos por método, `saldo_inicial_efectivo`, `efectivo_esperado_cierre`, conteo físico y descuadre (`diferencia_efectivo`).
- **`pedidos_tienda_portal` / `pedido_tienda_portal_lineas`:** reservas desde portal (stock descontado al reservar); cobro final en TPV con `registrarDesdePedidoReserva` sin doble descuento.

### I. Cuadrillas y costaleros (v1)
- **`cuadrillas`:** por año y paso (Cristo/Virgen), capataz, estructura (`numero_trabajaderas`, `puestos_por_trabajadera`) y estado activa.
- **`costalero_perfiles`:** extensión de `hermanos` para igualá (altura, calzado, ropa, trabajadera, palo), salud (`alergias`, `lesiones`) y antigüedad en cuadrilla.
- **`ensayos_cuadrilla` + `ensayo_asistencias`:** calendario y control de asistencia por costalero; alerta visual al acumular 2 faltas.
- **`relevos_cuadrilla` + `relevo_detalles`:** cuadrante del día de salida con puntos, horarios, turnos y asignación; exportable a PDF.
- **`cuadrilla_avisos`:** avisos del capataz para la cuadrilla, visibles en portal del hermano en "Mi trabajadera".

### J. Secretaría y gestión documental (v1)
- **`secretaria_registros_documentales`:** libro oficial de entrada/salida (fecha, tipo movimiento, remitente/destinatario, extracto, tipo documento, número de protocolo único, archivo adjunto y sello digital).
- **`secretaria_plantillas_documentales`:** repositorio de modelos editables (nombre, tipo, cuerpo HTML enriquecido, marca de agua textual e imagen `marca_agua_path`).
- **`secretaria_entidades_externas`:** agenda institucional (otras hermandades, ayuntamiento, consejo, proveedores VIP).
- **`secretaria_actos_protocolo` + `secretaria_invitaciones_acto`:** actos oficiales, confirmaciones y asignación de sitio protocolario (fila/banco/orden).
- **`documentos_archivo_justificantes`:** vinculación de documentos anexos/justificantes a un documento principal del archivo digital.

### K. Unidades familiares y fiscalidad (v1)
- **`familias`:** agrupación familiar (nombre, `pago_unificado`, `pagador_hermano_id`).
- **`familia_hermano`:** vinculación hermano-familia con `parentesco` (Padre, Madre, Hijo/a, Cónyuge, Tutor).
- **`hermanos.es_cabeza_familia`:** marca para envíos postales agrupados (boletín único).
- **`hermanos.beneficiario_fiscal_hermano_id`:** DNI beneficiario fiscal para deducción de cuotas/donativos (Modelo 182).

---

## 3. Estructura de UI (Mobile First)
- **Layout:** Sidebar lateral colapsable para escritorio, menú inferior tipo "App" para móvil.
- **Modales:** Componentes Blade reutilizables con Alpine.js:
  `<x-modal name="edit-hermano" :show="false">...</x-modal>`
- **Tablas:** Diseño responsivo (ocultar columnas no esenciales en móvil) con filtros en la cabecera.

---

## 4. Lógica de Informes
- Generación de PDF (usando `barryvdh/laravel-dompdf`).
- **Listados:** Censo electoral, listado de cobro, etiquetas para correspondencia, listado de cofradía por antigüedad.