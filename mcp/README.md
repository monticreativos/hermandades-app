# MCP avanzado de Contabilidad (Hermandad)

Servidor MCP listo para usar con Cursor/clients MCP, enfocado en:

- Plan contable (`cuentas_contables`)
- Auxiliares de `hermanos` (430.x) y `proveedores` (410.x)
- Propuesta de asientos con OpenRouter + validación de cuadrado

## 1) Instalación

Desde la raíz del proyecto:

```bash
npm install
```

## 2) Variables de entorno

En `.env`:

```env
OPENROUTER_API_KEY=tu_clave
MCP_OPENROUTER_MODEL=openrouter/auto
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hermandad_app
DB_USERNAME=root
DB_PASSWORD=secret
```

## 3) Ejecución

```bash
npm run mcp:contabilidad
```

## 4) Configuración MCP en Cursor (ejemplo)

```json
{
  "mcpServers": {
    "hermandad-contabilidad": {
      "command": "node",
      "args": ["mcp/hermandad-contabilidad-mcp.mjs"],
      "cwd": "c:/Users/dvdmo/Desktop/proyectos/hermandad-app"
    }
  }
}
```

## 5) Tools incluidas

- `health_check`
- `get_plan_contable`
- `buscar_proveedores_aux`
- `buscar_hermanos_aux`
- `suggest_asiento`
- `validar_asiento`

## 6) Notas de seguridad y calidad

- No hardcodea API keys.
- En contexto de gasto, la validación bloquea líneas `430`.
- Recomendado mantener el guardarraíl final en Laravel antes de persistir.
