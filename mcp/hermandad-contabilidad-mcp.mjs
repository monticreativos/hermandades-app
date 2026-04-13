import fs from "node:fs";
import path from "node:path";
import process from "node:process";

import dotenv from "dotenv";
import mysql from "mysql2/promise";
import { z } from "zod";
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { CallToolRequestSchema, ListToolsRequestSchema } from "@modelcontextprotocol/sdk/types.js";

const ROOT = process.cwd();
dotenv.config({ path: path.join(ROOT, ".env") });

const OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions";
const OPENROUTER_MODEL = process.env.MCP_OPENROUTER_MODEL || "openrouter/auto";
const OPENROUTER_API_KEY = process.env.OPENROUTER_API_KEY || "";

const dbConfig = {
  host: process.env.DB_HOST || "127.0.0.1",
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USERNAME || "root",
  password: process.env.DB_PASSWORD || "",
  database: process.env.DB_DATABASE || "",
};

function contentText(text) {
  return { content: [{ type: "text", text }] };
}

function money(n) {
  return Number(Number(n || 0).toFixed(2));
}

function isGastoContext(descripcion = "") {
  const t = descripcion.toLowerCase();
  const keys = ["pago", "factura", "compra", "flores", "cera", "culto", "proveedor", "gasto", "restaur"];
  return keys.some((k) => t.includes(k));
}

async function withDb(fn) {
  if (!dbConfig.database) {
    throw new Error("DB_DATABASE no configurada en .env");
  }
  const conn = await mysql.createConnection(dbConfig);
  try {
    return await fn(conn);
  } finally {
    await conn.end();
  }
}

async function getPlanContable({ contexto = "all", limit = 220 } = {}) {
  const prefixes =
    contexto === "gasto"
      ? ["6%", "57%", "410%", "472%"]
      : contexto === "ingreso"
        ? ["7%", "57%", "430%", "477%"]
        : null;

  return withDb(async (conn) => {
    let sql = "SELECT id, codigo, nombre, tipo, hermano_id, proveedor_id FROM cuentas_contables";
    const params = [];
    if (prefixes) {
      sql += " WHERE (" + prefixes.map(() => "codigo LIKE ?").join(" OR ") + ")";
      params.push(...prefixes);
    }
    sql += " ORDER BY codigo ASC LIMIT ?";
    params.push(Number(limit));
    const [rows] = await conn.execute(sql, params);
    return rows;
  });
}

async function buscarProveedores(query, limit = 20) {
  return withDb(async (conn) => {
    const [rows] = await conn.execute(
      `SELECT p.id, p.razon_social, p.nif_cif, c.id AS cuenta_id, c.codigo AS cuenta_codigo, c.nombre AS cuenta_nombre
       FROM proveedores p
       LEFT JOIN cuentas_contables c ON c.id = p.cuenta_contable_id
       WHERE p.razon_social LIKE ? OR COALESCE(p.nombre_comercial, '') LIKE ? OR COALESCE(p.nif_cif, '') LIKE ?
       ORDER BY p.razon_social ASC
       LIMIT ?`,
      [`%${query}%`, `%${query}%`, `%${query}%`, Number(limit)]
    );
    return rows;
  });
}

async function buscarHermanos(query, limit = 20) {
  return withDb(async (conn) => {
    const [rows] = await conn.execute(
      `SELECT h.id, h.numero_hermano, h.nombre, h.apellidos, h.dni, c.id AS cuenta_id, c.codigo AS cuenta_codigo, c.nombre AS cuenta_nombre
       FROM hermanos h
       LEFT JOIN cuentas_contables c ON c.id = h.cuenta_contable_id
       WHERE h.nombre LIKE ? OR h.apellidos LIKE ? OR CONCAT(COALESCE(h.nombre,''), ' ', COALESCE(h.apellidos,'')) LIKE ? OR COALESCE(h.dni,'') LIKE ?
       ORDER BY h.numero_hermano ASC
       LIMIT ?`,
      [`%${query}%`, `%${query}%`, `%${query}%`, `%${query}%`, Number(limit)]
    );
    return rows;
  });
}

function validateAsiento(lines, { contextoGasto = false } = {}) {
  if (!Array.isArray(lines) || !lines.length) return { ok: false, issues: ["Sin líneas"] };
  const issues = [];
  let debe = 0;
  let haber = 0;
  for (const [i, l] of lines.entries()) {
    const d = money(l.debe);
    const h = money(l.haber);
    debe += d;
    haber += h;
    if (!l.cuenta_codigo) issues.push(`Línea ${i + 1}: falta cuenta_codigo`);
    if (d <= 0 && h <= 0) issues.push(`Línea ${i + 1}: debe/haber vacío`);
    if (d > 0 && h > 0) issues.push(`Línea ${i + 1}: no puede tener debe y haber a la vez`);
    if (contextoGasto && String(l.cuenta_codigo).startsWith("430")) {
      issues.push(`Línea ${i + 1}: 430 (clientes) no permitido en gasto`);
    }
  }
  if (Math.abs(money(debe - haber)) > 0.01) issues.push(`No cuadra: Debe ${debe} vs Haber ${haber}`);
  return { ok: issues.length === 0, issues, total_debe: money(debe), total_haber: money(haber) };
}

async function suggestAsiento(descripcion) {
  if (!OPENROUTER_API_KEY) throw new Error("OPENROUTER_API_KEY no configurada");
  const contextoGasto = isGastoContext(descripcion);
  const plan = await getPlanContable({ contexto: contextoGasto ? "gasto" : "ingreso", limit: 240 });

  let proveedores = [];
  let hermanos = [];
  const words = String(descripcion).split(/\s+/).filter((w) => w.length >= 4);
  if (words.length) {
    const q = words.slice(0, 4).join(" ");
    proveedores = await buscarProveedores(q, 15);
    hermanos = await buscarHermanos(q, 15);
  }

  const systemPrompt = [
    "Eres contable senior de Hermandades (PGC ES).",
    "Devuelve SOLO JSON válido con esquema:",
    '{"glosa":"string","lineas":[{"cuenta_codigo":"string","debe":0,"haber":0,"concepto":"string"}]}',
    "Reglas:",
    "- Cuadrar Debe=Haber.",
    "- Solo Debe o Haber por línea.",
    "- En contexto de gasto no uses 430 (clientes).",
    "- Si hay proveedor asociado, prioriza 410.x del proveedor.",
  ].join("\n");

  const userPrompt = [
    `Descripción: ${descripcion}`,
    `Contexto detectado: ${contextoGasto ? "gasto/pago" : "ingreso/otro"}`,
    "Plan contable disponible:",
    JSON.stringify(plan),
    "Proveedores candidatos:",
    JSON.stringify(proveedores),
    "Hermanos candidatos:",
    JSON.stringify(hermanos),
  ].join("\n\n");

  const resp = await fetch(OPENROUTER_URL, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${OPENROUTER_API_KEY}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      model: OPENROUTER_MODEL,
      temperature: 0.1,
      response_format: { type: "json_object" },
      messages: [
        { role: "system", content: systemPrompt },
        { role: "user", content: userPrompt },
      ],
    }),
  });

  if (!resp.ok) {
    throw new Error(`OpenRouter error HTTP ${resp.status}`);
  }

  const data = await resp.json();
  const content = data?.choices?.[0]?.message?.content || "";
  const parsed = JSON.parse(content);
  const lineas = Array.isArray(parsed.lineas) ? parsed.lineas : [];
  const validation = validateAsiento(lineas, { contextoGasto });

  return {
    contexto: contextoGasto ? "gasto" : "ingreso",
    glosa: parsed.glosa || descripcion.slice(0, 150),
    lineas,
    validacion: validation,
    candidatos: {
      proveedores: proveedores.map((p) => ({
        id: p.id,
        razon_social: p.razon_social,
        cuenta_codigo: p.cuenta_codigo || null,
      })),
      hermanos: hermanos.map((h) => ({
        id: h.id,
        numero_hermano: h.numero_hermano,
        nombre: `${h.nombre} ${h.apellidos}`,
        cuenta_codigo: h.cuenta_codigo || null,
      })),
    },
  };
}

const server = new Server(
  { name: "hermandad-contabilidad-mcp", version: "1.0.0" },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    {
      name: "health_check",
      description: "Comprueba estado de MCP, .env y conexión DB",
      inputSchema: { type: "object", properties: {}, additionalProperties: false },
    },
    {
      name: "get_plan_contable",
      description: "Obtiene plan contable (all/gasto/ingreso)",
      inputSchema: {
        type: "object",
        properties: {
          contexto: { type: "string", enum: ["all", "gasto", "ingreso"] },
          limit: { type: "number" },
        },
        additionalProperties: false,
      },
    },
    {
      name: "buscar_proveedores_aux",
      description: "Busca proveedores con su subcuenta 410",
      inputSchema: {
        type: "object",
        properties: { query: { type: "string" }, limit: { type: "number" } },
        required: ["query"],
        additionalProperties: false,
      },
    },
    {
      name: "buscar_hermanos_aux",
      description: "Busca hermanos con su subcuenta 430",
      inputSchema: {
        type: "object",
        properties: { query: { type: "string" }, limit: { type: "number" } },
        required: ["query"],
        additionalProperties: false,
      },
    },
    {
      name: "suggest_asiento",
      description: "Genera propuesta de asiento contable con OpenRouter y validación",
      inputSchema: {
        type: "object",
        properties: { descripcion: { type: "string" } },
        required: ["descripcion"],
        additionalProperties: false,
      },
    },
    {
      name: "validar_asiento",
      description: "Valida que un asiento esté cuadrado y coherente",
      inputSchema: {
        type: "object",
        properties: {
          contexto_gasto: { type: "boolean" },
          lineas: {
            type: "array",
            items: {
              type: "object",
              properties: {
                cuenta_codigo: { type: "string" },
                debe: { type: "number" },
                haber: { type: "number" },
                concepto: { type: "string" },
              },
              required: ["cuenta_codigo", "debe", "haber"],
              additionalProperties: true,
            },
          },
        },
        required: ["lineas"],
        additionalProperties: false,
      },
    },
  ],
}));

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args = {} } = request.params;

  if (name === "health_check") {
    const existsEnv = fs.existsSync(path.join(ROOT, ".env"));
    try {
      const okDb = await withDb(async () => true);
      return contentText(
        JSON.stringify(
          {
            ok: true,
            env: existsEnv,
            db: okDb,
            model: OPENROUTER_MODEL,
            openrouter_key_configured: Boolean(OPENROUTER_API_KEY),
          },
          null,
          2
        )
      );
    } catch (e) {
      return contentText(JSON.stringify({ ok: false, env: existsEnv, db: false, error: String(e.message) }, null, 2));
    }
  }

  if (name === "get_plan_contable") {
    const schema = z.object({
      contexto: z.enum(["all", "gasto", "ingreso"]).optional(),
      limit: z.number().int().positive().max(1000).optional(),
    });
    const input = schema.parse(args);
    const rows = await getPlanContable(input);
    return contentText(JSON.stringify({ total: rows.length, rows }, null, 2));
  }

  if (name === "buscar_proveedores_aux") {
    const schema = z.object({ query: z.string().min(1), limit: z.number().int().positive().max(100).optional() });
    const input = schema.parse(args);
    const rows = await buscarProveedores(input.query, input.limit || 20);
    return contentText(JSON.stringify({ total: rows.length, rows }, null, 2));
  }

  if (name === "buscar_hermanos_aux") {
    const schema = z.object({ query: z.string().min(1), limit: z.number().int().positive().max(100).optional() });
    const input = schema.parse(args);
    const rows = await buscarHermanos(input.query, input.limit || 20);
    return contentText(JSON.stringify({ total: rows.length, rows }, null, 2));
  }

  if (name === "suggest_asiento") {
    const schema = z.object({ descripcion: z.string().min(10).max(2500) });
    const input = schema.parse(args);
    const result = await suggestAsiento(input.descripcion);
    return contentText(JSON.stringify(result, null, 2));
  }

  if (name === "validar_asiento") {
    const schema = z.object({
      contexto_gasto: z.boolean().optional(),
      lineas: z.array(
        z.object({
          cuenta_codigo: z.string(),
          debe: z.number().optional().default(0),
          haber: z.number().optional().default(0),
          concepto: z.string().optional(),
        })
      ),
    });
    const input = schema.parse(args);
    const result = validateAsiento(input.lineas, { contextoGasto: Boolean(input.contexto_gasto) });
    return contentText(JSON.stringify(result, null, 2));
  }

  return contentText(JSON.stringify({ error: `Tool no soportada: ${name}` }));
});

const transport = new StdioServerTransport();
await server.connect(transport);
