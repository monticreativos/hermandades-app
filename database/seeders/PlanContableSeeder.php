<?php

namespace Database\Seeders;

use App\Models\CuentaContable;
use Illuminate\Database\Seeder;

/**
 * PGC completo adaptado a Hermandades y Cofradías.
 *
 * Fuente: Plan General de Contabilidad para Hermandades y Cofradías,
 * adaptado al PGC de Entidades Diocesanas de la Iglesia (Conferencia Episcopal Española, 2016).
 * Grupos 1-7 con todos los subgrupos, cuentas y subcuentas del cuadro oficial.
 */
class PlanContableSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = array_merge(
            $this->grupo1(),
            $this->grupo2(),
            $this->grupo3(),
            $this->grupo4(),
            $this->grupo5(),
            $this->grupo6(),
            $this->grupo7(),
        );

        foreach ($cuentas as $c) {
            CuentaContable::query()->updateOrCreate(
                ['codigo' => $c['codigo']],
                ['nombre' => $c['nombre'], 'tipo' => $c['tipo']]
            );
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 1 — Financiación básica
    // ──────────────────────────────────────────────────────────────
    private function grupo1(): array
    {
        return [
            // 10 Capital
            ['codigo' => '10', 'nombre' => 'Capital', 'tipo' => 'Patrimonio'],
            ['codigo' => '100', 'nombre' => 'Dotación fundacional', 'tipo' => 'Patrimonio'],
            ['codigo' => '101', 'nombre' => 'Fondo social', 'tipo' => 'Patrimonio'],

            // 11 Reservas
            ['codigo' => '11', 'nombre' => 'Reservas', 'tipo' => 'Patrimonio'],
            ['codigo' => '113', 'nombre' => 'Reservas voluntarias', 'tipo' => 'Patrimonio'],

            // 12 Excedentes pendientes de aplicación
            ['codigo' => '12', 'nombre' => 'Excedentes pendientes de aplicación', 'tipo' => 'Patrimonio'],
            ['codigo' => '120', 'nombre' => 'Remanente', 'tipo' => 'Patrimonio'],
            ['codigo' => '129', 'nombre' => 'Excedente del ejercicio', 'tipo' => 'Patrimonio'],

            // 13 Subvenciones, donaciones, legados y otros ajustes
            ['codigo' => '13', 'nombre' => 'Subvenciones, donaciones, legados y otros ajustes por cambios de valor', 'tipo' => 'Patrimonio'],
            ['codigo' => '130', 'nombre' => 'Subvenciones oficiales de capital', 'tipo' => 'Patrimonio'],
            ['codigo' => '1300', 'nombre' => 'Subvenciones del Estado', 'tipo' => 'Patrimonio'],
            ['codigo' => '1301', 'nombre' => 'Subvenciones de Comunidad Autónoma', 'tipo' => 'Patrimonio'],
            ['codigo' => '1302', 'nombre' => 'Subvenciones del Ayuntamiento', 'tipo' => 'Patrimonio'],
            ['codigo' => '131', 'nombre' => 'Donaciones y legados de capital', 'tipo' => 'Patrimonio'],
            ['codigo' => '132', 'nombre' => 'Otras subvenciones, donaciones y legados', 'tipo' => 'Patrimonio'],
            ['codigo' => '1320', 'nombre' => 'Otras subvenciones', 'tipo' => 'Patrimonio'],
            ['codigo' => '1321', 'nombre' => 'Otras donaciones y legados', 'tipo' => 'Patrimonio'],
            ['codigo' => '133', 'nombre' => 'Ajustes por valoración en activos financieros disponibles para la venta', 'tipo' => 'Patrimonio'],
            ['codigo' => '134', 'nombre' => 'Operaciones de cobertura', 'tipo' => 'Patrimonio'],

            // 14 Provisiones
            ['codigo' => '14', 'nombre' => 'Provisiones', 'tipo' => 'Pasivo'],
            ['codigo' => '140', 'nombre' => 'Provisión por retribuciones a largo plazo al personal', 'tipo' => 'Pasivo'],
            ['codigo' => '141', 'nombre' => 'Provisión para impuestos', 'tipo' => 'Pasivo'],
            ['codigo' => '142', 'nombre' => 'Provisión para otras responsabilidades', 'tipo' => 'Pasivo'],
            ['codigo' => '143', 'nombre' => 'Provisión por desmantelamiento, retiro o rehabilitación del inmovilizado', 'tipo' => 'Pasivo'],

            // 16 Deudas a largo plazo con partes vinculadas
            ['codigo' => '16', 'nombre' => 'Deudas a largo plazo con partes vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '160', 'nombre' => 'Deudas a largo plazo con entidades vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '161', 'nombre' => 'Proveedores de inmovilizado a largo plazo, partes vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '163', 'nombre' => 'Otras deudas a largo plazo con entidades vinculadas', 'tipo' => 'Pasivo'],

            // 17 Deudas a largo plazo por préstamos recibidos, empréstitos y otros conceptos
            ['codigo' => '17', 'nombre' => 'Deudas a largo plazo por préstamos recibidos, empréstitos y otros conceptos', 'tipo' => 'Pasivo'],
            ['codigo' => '170', 'nombre' => 'Deudas a largo plazo con entidades de crédito', 'tipo' => 'Pasivo'],
            ['codigo' => '1700', 'nombre' => 'Deudas a largo plazo con entidades de crédito', 'tipo' => 'Pasivo'],
            ['codigo' => '171', 'nombre' => 'Deudas a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '1710', 'nombre' => 'Deudas a largo plazo con otras entidades privadas o personas físicas', 'tipo' => 'Pasivo'],
            ['codigo' => '172', 'nombre' => 'Deudas a largo plazo transformables en subvenciones, donaciones y legados', 'tipo' => 'Pasivo'],
            ['codigo' => '1720', 'nombre' => 'Deudas a largo plazo transformables en subvenciones', 'tipo' => 'Pasivo'],
            ['codigo' => '1721', 'nombre' => 'Deudas a largo plazo transformables en donaciones y legados', 'tipo' => 'Pasivo'],
            ['codigo' => '173', 'nombre' => 'Proveedores de inmovilizado a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '1730', 'nombre' => 'Proveedores de inmovilizado a largo plazo de bienes procesionales o enseres', 'tipo' => 'Pasivo'],
            ['codigo' => '1731', 'nombre' => 'Proveedores de inmovilizado a largo plazo de otros bienes', 'tipo' => 'Pasivo'],
            ['codigo' => '174', 'nombre' => 'Acreedores por arrendamiento financiero a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '175', 'nombre' => 'Efectos a pagar a largo plazo', 'tipo' => 'Pasivo'],

            // 18 Pasivos por fianzas, garantías y otros conceptos a largo plazo
            ['codigo' => '18', 'nombre' => 'Pasivos por fianzas, garantías y otros conceptos a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '180', 'nombre' => 'Fianzas recibidas a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '181', 'nombre' => 'Anticipos recibidos por ventas o prestaciones de servicios a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '185', 'nombre' => 'Depósitos recibidos a largo plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '189', 'nombre' => 'Garantías financieras a largo plazo', 'tipo' => 'Pasivo'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 2 — Activo no corriente
    // ──────────────────────────────────────────────────────────────
    private function grupo2(): array
    {
        return [
            // 20 Inmovilizaciones intangibles
            ['codigo' => '20', 'nombre' => 'Inmovilizaciones intangibles', 'tipo' => 'Activo'],
            ['codigo' => '202', 'nombre' => 'Concesiones administrativas', 'tipo' => 'Activo'],
            ['codigo' => '2020', 'nombre' => 'Concesiones administrativas de locales o espacios multiculturales', 'tipo' => 'Activo'],
            ['codigo' => '2021', 'nombre' => 'Concesión administrativa uso imágenes, grupos escultóricos, etc.', 'tipo' => 'Activo'],
            ['codigo' => '203', 'nombre' => 'Patentes y marcas', 'tipo' => 'Activo'],
            ['codigo' => '2030', 'nombre' => 'Patentes, licencias y marcas', 'tipo' => 'Activo'],
            ['codigo' => '206', 'nombre' => 'Aplicaciones informáticas', 'tipo' => 'Activo'],
            ['codigo' => '2060', 'nombre' => 'Programas informáticos gestión Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2061', 'nombre' => 'Página web Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2062', 'nombre' => 'Otros programas: redes sociales, etc.', 'tipo' => 'Activo'],

            // 21 Inmovilizaciones materiales
            ['codigo' => '21', 'nombre' => 'Inmovilizaciones materiales', 'tipo' => 'Activo'],
            ['codigo' => '210', 'nombre' => 'Terrenos y bienes naturales', 'tipo' => 'Activo'],
            ['codigo' => '2100', 'nombre' => 'Terrenos locales de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2101', 'nombre' => 'Terrenos Museos', 'tipo' => 'Activo'],
            ['codigo' => '211', 'nombre' => 'Construcciones', 'tipo' => 'Activo'],
            ['codigo' => '2110', 'nombre' => 'Locales de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2111', 'nombre' => 'Museo de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '212', 'nombre' => 'Instalaciones técnicas', 'tipo' => 'Activo'],
            ['codigo' => '2120', 'nombre' => 'Instalaciones técnicas de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '213', 'nombre' => 'Maquinaria', 'tipo' => 'Activo'],
            ['codigo' => '2130', 'nombre' => 'Maquinaria de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '214', 'nombre' => 'Utillaje', 'tipo' => 'Activo'],
            ['codigo' => '2140', 'nombre' => 'Utillaje de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '215', 'nombre' => 'Otras instalaciones', 'tipo' => 'Activo'],
            ['codigo' => '2150', 'nombre' => 'Instalaciones en locales de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2151', 'nombre' => 'Instalaciones en locales arrendados por la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '216', 'nombre' => 'Mobiliario', 'tipo' => 'Activo'],
            ['codigo' => '2160', 'nombre' => 'Mobiliario de oficina de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '2161', 'nombre' => 'Otro mobiliario de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '217', 'nombre' => 'Equipos para procesos de información', 'tipo' => 'Activo'],
            ['codigo' => '2170', 'nombre' => 'Equipos informáticos de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '218', 'nombre' => 'Elementos de transporte', 'tipo' => 'Activo'],
            ['codigo' => '2180', 'nombre' => 'Elementos de transporte de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '219', 'nombre' => 'Otro inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '2190', 'nombre' => 'Otro inmovilizado material de la Cofradía', 'tipo' => 'Activo'],

            // 22 Inversiones inmobiliarias
            ['codigo' => '22', 'nombre' => 'Inversiones inmobiliarias', 'tipo' => 'Activo'],
            ['codigo' => '220', 'nombre' => 'Inversiones en terrenos y bienes naturales', 'tipo' => 'Activo'],
            ['codigo' => '221', 'nombre' => 'Inversiones en construcciones', 'tipo' => 'Activo'],

            // 23 Inmovilizaciones materiales en curso
            ['codigo' => '23', 'nombre' => 'Inmovilizaciones materiales en curso', 'tipo' => 'Activo'],
            ['codigo' => '230', 'nombre' => 'Adaptación de terrenos y bienes naturales', 'tipo' => 'Activo'],
            ['codigo' => '231', 'nombre' => 'Construcciones en curso', 'tipo' => 'Activo'],
            ['codigo' => '239', 'nombre' => 'Anticipos para inmovilizaciones materiales', 'tipo' => 'Activo'],

            // 24 Bienes del patrimonio histórico
            ['codigo' => '24', 'nombre' => 'Bienes del patrimonio histórico', 'tipo' => 'Activo'],
            ['codigo' => '240', 'nombre' => 'Bienes inmuebles', 'tipo' => 'Activo'],
            ['codigo' => '2400', 'nombre' => 'Monumentos', 'tipo' => 'Activo'],
            ['codigo' => '2402', 'nombre' => 'Conjuntos históricos', 'tipo' => 'Activo'],
            ['codigo' => '2403', 'nombre' => 'Sitios históricos', 'tipo' => 'Activo'],
            ['codigo' => '241', 'nombre' => 'Archivos', 'tipo' => 'Activo'],
            ['codigo' => '2410', 'nombre' => 'Archivos históricos', 'tipo' => 'Activo'],
            ['codigo' => '242', 'nombre' => 'Bibliotecas', 'tipo' => 'Activo'],
            ['codigo' => '2420', 'nombre' => 'Bibliotecas', 'tipo' => 'Activo'],
            ['codigo' => '243', 'nombre' => 'Museos', 'tipo' => 'Activo'],
            ['codigo' => '2430', 'nombre' => 'Museos', 'tipo' => 'Activo'],
            ['codigo' => '244', 'nombre' => 'Bienes muebles', 'tipo' => 'Activo'],
            ['codigo' => '2440', 'nombre' => 'Imágenes y grupos escultóricos procesionales y de culto', 'tipo' => 'Activo'],
            ['codigo' => '2441', 'nombre' => 'Tronos, andas, etc.', 'tipo' => 'Activo'],
            ['codigo' => '2442', 'nombre' => 'Enseres cofrades procesionales y de culto', 'tipo' => 'Activo'],

            // 25 Inversiones financieras a largo plazo en partes vinculadas
            ['codigo' => '25', 'nombre' => 'Inversiones financieras a largo plazo en partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '250', 'nombre' => 'Participaciones a largo plazo en partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '252', 'nombre' => 'Créditos a largo plazo a partes vinculadas', 'tipo' => 'Activo'],

            // 26 Otras inversiones financieras a largo plazo
            ['codigo' => '26', 'nombre' => 'Otras inversiones financieras a largo plazo', 'tipo' => 'Activo'],
            ['codigo' => '260', 'nombre' => 'Inversiones financieras a largo plazo en instrumentos de patrimonio', 'tipo' => 'Activo'],
            ['codigo' => '261', 'nombre' => 'Valores representativos de deuda a largo plazo', 'tipo' => 'Activo'],
            ['codigo' => '262', 'nombre' => 'Créditos a largo plazo', 'tipo' => 'Activo'],
            ['codigo' => '263', 'nombre' => 'Créditos a largo plazo por enajenación de inmovilizado', 'tipo' => 'Activo'],
            ['codigo' => '264', 'nombre' => 'Créditos a largo plazo al personal', 'tipo' => 'Activo'],
            ['codigo' => '268', 'nombre' => 'Imposiciones a largo plazo', 'tipo' => 'Activo'],

            // 27 Fianzas y depósitos constituidos a largo plazo
            ['codigo' => '27', 'nombre' => 'Fianzas y depósitos constituidos a largo plazo', 'tipo' => 'Activo'],
            ['codigo' => '270', 'nombre' => 'Fianzas constituidas a largo plazo', 'tipo' => 'Activo'],
            ['codigo' => '275', 'nombre' => 'Depósitos constituidos a largo plazo', 'tipo' => 'Activo'],

            // 28 Amortización acumulada del inmovilizado
            ['codigo' => '28', 'nombre' => 'Amortización acumulada del inmovilizado y otras cuentas correctoras', 'tipo' => 'Activo'],
            ['codigo' => '280', 'nombre' => 'Amortización acumulada del inmovilizado intangible', 'tipo' => 'Activo'],
            ['codigo' => '2802', 'nombre' => 'Amortización acumulada de concesiones administrativas', 'tipo' => 'Activo'],
            ['codigo' => '2806', 'nombre' => 'Amortización acumulada de aplicaciones informáticas', 'tipo' => 'Activo'],
            ['codigo' => '281', 'nombre' => 'Amortización acumulada del inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '2811', 'nombre' => 'Amortización acumulada de construcciones', 'tipo' => 'Activo'],
            ['codigo' => '2812', 'nombre' => 'Amortización acumulada de instalaciones técnicas', 'tipo' => 'Activo'],
            ['codigo' => '2813', 'nombre' => 'Amortización acumulada de maquinaria', 'tipo' => 'Activo'],
            ['codigo' => '2814', 'nombre' => 'Amortización acumulada de utillaje', 'tipo' => 'Activo'],
            ['codigo' => '2815', 'nombre' => 'Amortización acumulada de otras instalaciones', 'tipo' => 'Activo'],
            ['codigo' => '2816', 'nombre' => 'Amortización acumulada de mobiliario', 'tipo' => 'Activo'],
            ['codigo' => '2817', 'nombre' => 'Amortización acumulada de equipos para procesos de información', 'tipo' => 'Activo'],
            ['codigo' => '2818', 'nombre' => 'Amortización acumulada de elementos de transporte', 'tipo' => 'Activo'],
            ['codigo' => '2819', 'nombre' => 'Amortización acumulada de otro inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '282', 'nombre' => 'Amortización acumulada de las inversiones inmobiliarias', 'tipo' => 'Activo'],
            ['codigo' => '283', 'nombre' => 'Cesiones de uso sin contraprestación', 'tipo' => 'Activo'],
            ['codigo' => '2830', 'nombre' => 'Cesiones de uso del inmovilizado intangible', 'tipo' => 'Activo'],
            ['codigo' => '2831', 'nombre' => 'Cesiones de uso del inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '2832', 'nombre' => 'Cesiones de uso de las inversiones inmobiliarias', 'tipo' => 'Activo'],

            // 29 Deterioro de valor de activos no corrientes
            ['codigo' => '29', 'nombre' => 'Deterioro de valor de activos no corrientes', 'tipo' => 'Activo'],
            ['codigo' => '290', 'nombre' => 'Deterioro de valor del inmovilizado intangible', 'tipo' => 'Activo'],
            ['codigo' => '2902', 'nombre' => 'Deterioro de valor de concesiones administrativas', 'tipo' => 'Activo'],
            ['codigo' => '2906', 'nombre' => 'Deterioro de valor de aplicaciones informáticas', 'tipo' => 'Activo'],
            ['codigo' => '291', 'nombre' => 'Deterioro de valor del inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '2910', 'nombre' => 'Deterioro de valor de terrenos y bienes naturales', 'tipo' => 'Activo'],
            ['codigo' => '2911', 'nombre' => 'Deterioro de valor de construcciones', 'tipo' => 'Activo'],
            ['codigo' => '2913', 'nombre' => 'Deterioro de valor de maquinaria', 'tipo' => 'Activo'],
            ['codigo' => '2914', 'nombre' => 'Deterioro de valor de utillaje', 'tipo' => 'Activo'],
            ['codigo' => '2915', 'nombre' => 'Deterioro de valor de otras instalaciones', 'tipo' => 'Activo'],
            ['codigo' => '2916', 'nombre' => 'Deterioro de valor de mobiliario', 'tipo' => 'Activo'],
            ['codigo' => '2917', 'nombre' => 'Deterioro de valor de equipos para procesos de información', 'tipo' => 'Activo'],
            ['codigo' => '2918', 'nombre' => 'Deterioro de valor de elementos de transporte', 'tipo' => 'Activo'],
            ['codigo' => '2919', 'nombre' => 'Deterioro de valor de otro inmovilizado material', 'tipo' => 'Activo'],
            ['codigo' => '294', 'nombre' => 'Deterioro de valor de valores representativos de deuda a l/p de partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '295', 'nombre' => 'Deterioro de valor de créditos a largo plazo a partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '299', 'nombre' => 'Deterioro de valor de bienes del Patrimonio Histórico', 'tipo' => 'Activo'],
            ['codigo' => '2990', 'nombre' => 'Deterioro de valor de bienes inmuebles', 'tipo' => 'Activo'],
            ['codigo' => '2991', 'nombre' => 'Deterioro de valor de archivos', 'tipo' => 'Activo'],
            ['codigo' => '2992', 'nombre' => 'Deterioro de valor de bibliotecas', 'tipo' => 'Activo'],
            ['codigo' => '2993', 'nombre' => 'Deterioro de valor de Museos', 'tipo' => 'Activo'],
            ['codigo' => '2994', 'nombre' => 'Deterioro de valor de bienes muebles', 'tipo' => 'Activo'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 3 — Existencias
    // ──────────────────────────────────────────────────────────────
    private function grupo3(): array
    {
        return [
            ['codigo' => '30', 'nombre' => 'Bienes destinados a la actividad económica', 'tipo' => 'Activo'],
            ['codigo' => '300', 'nombre' => 'Mercaderías', 'tipo' => 'Activo'],
            ['codigo' => '3000', 'nombre' => 'Estampas, medallas, insignias, etc.', 'tipo' => 'Activo'],

            ['codigo' => '32', 'nombre' => 'Otros aprovisionamientos', 'tipo' => 'Activo'],
            ['codigo' => '320', 'nombre' => 'Elementos y conjuntos incorporables', 'tipo' => 'Activo'],
            ['codigo' => '321', 'nombre' => 'Combustibles', 'tipo' => 'Activo'],
            ['codigo' => '325', 'nombre' => 'Materiales diversos', 'tipo' => 'Activo'],
            ['codigo' => '328', 'nombre' => 'Material de oficina', 'tipo' => 'Activo'],

            ['codigo' => '33', 'nombre' => 'Productos en curso', 'tipo' => 'Activo'],
            ['codigo' => '330', 'nombre' => 'Productos en curso', 'tipo' => 'Activo'],

            ['codigo' => '35', 'nombre' => 'Productos terminados', 'tipo' => 'Activo'],
            ['codigo' => '350', 'nombre' => 'Productos terminados', 'tipo' => 'Activo'],

            ['codigo' => '39', 'nombre' => 'Deterioro de valor de las existencias', 'tipo' => 'Activo'],
            ['codigo' => '390', 'nombre' => 'Deterioro de valor de los bienes destinados a la actividad', 'tipo' => 'Activo'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 4 — Acreedores y deudores por operaciones de la actividad
    // ──────────────────────────────────────────────────────────────
    private function grupo4(): array
    {
        return [
            // 40 Proveedores
            ['codigo' => '40', 'nombre' => 'Proveedores', 'tipo' => 'Pasivo'],
            ['codigo' => '400', 'nombre' => 'Proveedores', 'tipo' => 'Pasivo'],
            ['codigo' => '401', 'nombre' => 'Proveedores, efectos comerciales a pagar', 'tipo' => 'Pasivo'],
            ['codigo' => '403', 'nombre' => 'Proveedores, entidades de la Iglesia', 'tipo' => 'Pasivo'],
            ['codigo' => '405', 'nombre' => 'Proveedores, otras partes vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '407', 'nombre' => 'Anticipos a proveedores', 'tipo' => 'Activo'],

            // 41 Beneficiarios y acreedores varios
            ['codigo' => '41', 'nombre' => 'Beneficiarios y acreedores varios', 'tipo' => 'Pasivo'],
            ['codigo' => '410', 'nombre' => 'Acreedores por prestaciones de servicios', 'tipo' => 'Pasivo'],
            ['codigo' => '411', 'nombre' => 'Acreedores, efectos comerciales a pagar', 'tipo' => 'Pasivo'],
            ['codigo' => '412', 'nombre' => 'Beneficiarios, acreedores', 'tipo' => 'Pasivo'],
            ['codigo' => '419', 'nombre' => 'Acreedores por operaciones en común', 'tipo' => 'Pasivo'],

            // 43 Deudores y clientes
            ['codigo' => '43', 'nombre' => 'Deudores y clientes', 'tipo' => 'Activo'],
            ['codigo' => '430', 'nombre' => 'Clientes', 'tipo' => 'Activo'],
            ['codigo' => '431', 'nombre' => 'Clientes, efectos comerciales a cobrar', 'tipo' => 'Activo'],
            ['codigo' => '433', 'nombre' => 'Deudores entidades de la Iglesia', 'tipo' => 'Activo'],
            ['codigo' => '4330', 'nombre' => 'Deudores entidades diocesanas', 'tipo' => 'Activo'],
            ['codigo' => '4334', 'nombre' => 'Deudores otras entidades de la Iglesia', 'tipo' => 'Activo'],
            ['codigo' => '4336', 'nombre' => 'Deudores entidades eclesiales de dudoso cobro', 'tipo' => 'Activo'],
            ['codigo' => '434', 'nombre' => 'Deudores otras entidades asociadas', 'tipo' => 'Activo'],
            ['codigo' => '436', 'nombre' => 'Clientes de dudoso cobro', 'tipo' => 'Activo'],
            ['codigo' => '438', 'nombre' => 'Anticipos de clientes', 'tipo' => 'Pasivo'],

            // 44 Usuarios y deudores varios
            ['codigo' => '44', 'nombre' => 'Usuarios y deudores varios', 'tipo' => 'Activo'],
            ['codigo' => '440', 'nombre' => 'Deudores', 'tipo' => 'Activo'],
            ['codigo' => '4400', 'nombre' => 'Cofrades y hermanos de la Cofradía', 'tipo' => 'Activo'],
            ['codigo' => '441', 'nombre' => 'Deudores, efectos comerciales a cobrar', 'tipo' => 'Activo'],
            ['codigo' => '446', 'nombre' => 'Deudores de dudoso cobro', 'tipo' => 'Activo'],
            ['codigo' => '4460', 'nombre' => 'Cofrades y hermanos de dudoso cobro', 'tipo' => 'Activo'],
            ['codigo' => '447', 'nombre' => 'Usuarios de servicios religiosos, deudores', 'tipo' => 'Activo'],
            ['codigo' => '448', 'nombre' => 'Patrocinadores, suscriptores y otros deudores', 'tipo' => 'Activo'],
            ['codigo' => '4480', 'nombre' => 'Patrocinadores', 'tipo' => 'Activo'],
            ['codigo' => '4482', 'nombre' => 'Colaboradores', 'tipo' => 'Activo'],
            ['codigo' => '4489', 'nombre' => 'Otros deudores', 'tipo' => 'Activo'],
            ['codigo' => '449', 'nombre' => 'Deudores por operaciones en común', 'tipo' => 'Activo'],

            // 46 Personal
            ['codigo' => '46', 'nombre' => 'Personal', 'tipo' => 'Activo'],
            ['codigo' => '460', 'nombre' => 'Anticipos de remuneraciones', 'tipo' => 'Activo'],
            ['codigo' => '464', 'nombre' => 'Entregas para gastos a justificar', 'tipo' => 'Activo'],
            ['codigo' => '465', 'nombre' => 'Remuneraciones pendientes de pago', 'tipo' => 'Pasivo'],

            // 47 Administraciones Públicas
            ['codigo' => '47', 'nombre' => 'Administraciones Públicas', 'tipo' => 'Activo'],
            ['codigo' => '470', 'nombre' => 'Hacienda Pública, deudora por diversos conceptos', 'tipo' => 'Activo'],
            ['codigo' => '4700', 'nombre' => 'Hacienda Pública, deudora por IVA', 'tipo' => 'Activo'],
            ['codigo' => '4707', 'nombre' => 'Hacienda Pública, deudora por colaboración en entrega y distribución de subvenciones', 'tipo' => 'Activo'],
            ['codigo' => '4708', 'nombre' => 'Hacienda Pública, deudora por subvenciones concedidas', 'tipo' => 'Activo'],
            ['codigo' => '4709', 'nombre' => 'Hacienda Pública, deudora por devolución de impuestos', 'tipo' => 'Activo'],
            ['codigo' => '471', 'nombre' => 'Organismos de la Seguridad Social, deudores', 'tipo' => 'Activo'],
            ['codigo' => '472', 'nombre' => 'Hacienda Pública, IVA soportado', 'tipo' => 'Activo'],
            ['codigo' => '473', 'nombre' => 'Hacienda Pública, retenciones y pagos a cuenta', 'tipo' => 'Activo'],
            ['codigo' => '474', 'nombre' => 'Activos por impuesto diferido', 'tipo' => 'Activo'],
            ['codigo' => '475', 'nombre' => 'Hacienda Pública, acreedora por conceptos fiscales', 'tipo' => 'Pasivo'],
            ['codigo' => '4750', 'nombre' => 'Hacienda Pública, acreedora por IVA', 'tipo' => 'Pasivo'],
            ['codigo' => '4751', 'nombre' => 'Hacienda Pública, acreedora por retenciones practicadas', 'tipo' => 'Pasivo'],
            ['codigo' => '4752', 'nombre' => 'Hacienda Pública, acreedora por impuesto sobre sociedades', 'tipo' => 'Pasivo'],
            ['codigo' => '4757', 'nombre' => 'Hacienda Pública, acreedora por subvenciones recibidas como entidad colaboradora', 'tipo' => 'Pasivo'],
            ['codigo' => '4758', 'nombre' => 'Hacienda Pública, acreedora por subvenciones a reintegrar', 'tipo' => 'Pasivo'],
            ['codigo' => '476', 'nombre' => 'Organismos de la Seguridad Social, acreedores', 'tipo' => 'Pasivo'],
            ['codigo' => '477', 'nombre' => 'Hacienda Pública, IVA repercutido', 'tipo' => 'Pasivo'],
            ['codigo' => '479', 'nombre' => 'Pasivos por diferencias temporarias imponibles', 'tipo' => 'Pasivo'],

            // 48 Ajustes por periodificación
            ['codigo' => '48', 'nombre' => 'Ajustes por periodificación', 'tipo' => 'Activo'],
            ['codigo' => '480', 'nombre' => 'Gastos anticipados', 'tipo' => 'Activo'],
            ['codigo' => '485', 'nombre' => 'Ingresos anticipados', 'tipo' => 'Pasivo'],

            // 49 Deterioro de valor de créditos y provisiones a corto plazo
            ['codigo' => '49', 'nombre' => 'Deterioro de valor de créditos por operaciones de la actividad y provisiones a corto plazo', 'tipo' => 'Activo'],
            ['codigo' => '490', 'nombre' => 'Deterioro de valor de créditos por operaciones de la actividad', 'tipo' => 'Activo'],
            ['codigo' => '493', 'nombre' => 'Deterioro de valor de créditos por operaciones de la actividad con partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '495', 'nombre' => 'Deterioro de valor de créditos de usuarios, patrocinadores, afiliados y otros deudores', 'tipo' => 'Activo'],
            ['codigo' => '499', 'nombre' => 'Provisión por operaciones de la actividad', 'tipo' => 'Pasivo'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 5 — Cuentas financieras
    // ──────────────────────────────────────────────────────────────
    private function grupo5(): array
    {
        return [
            // 51 Deudas a corto plazo con partes vinculadas
            ['codigo' => '51', 'nombre' => 'Deudas a corto plazo con partes vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '510', 'nombre' => 'Deudas a corto plazo con entidades vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '5103', 'nombre' => 'Deudas a corto plazo con entidades diocesanas', 'tipo' => 'Pasivo'],
            ['codigo' => '5104', 'nombre' => 'Deudas a corto plazo con otras entidades de la Iglesia', 'tipo' => 'Pasivo'],
            ['codigo' => '5105', 'nombre' => 'Deudas a corto plazo con otras entidades vinculadas', 'tipo' => 'Pasivo'],

            // 52 Deudas a corto plazo por préstamos recibidos y otros conceptos
            ['codigo' => '52', 'nombre' => 'Deudas a corto plazo por préstamos recibidos y otros conceptos', 'tipo' => 'Pasivo'],
            ['codigo' => '520', 'nombre' => 'Deudas a corto plazo con entidades de crédito', 'tipo' => 'Pasivo'],
            ['codigo' => '5200', 'nombre' => 'Préstamos a corto plazo de entidades de crédito', 'tipo' => 'Pasivo'],
            ['codigo' => '5201', 'nombre' => 'Deudas a corto plazo por crédito dispuesto', 'tipo' => 'Pasivo'],
            ['codigo' => '521', 'nombre' => 'Deudas a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '522', 'nombre' => 'Deudas a corto plazo transformables en subvenciones, donaciones y legados', 'tipo' => 'Pasivo'],
            ['codigo' => '523', 'nombre' => 'Proveedores de inmovilizado a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '524', 'nombre' => 'Acreedores por arrendamiento financiero a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '525', 'nombre' => 'Efectos a pagar a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '527', 'nombre' => 'Intereses a corto plazo de deudas con entidades de crédito', 'tipo' => 'Pasivo'],
            ['codigo' => '528', 'nombre' => 'Intereses a corto plazo de deudas', 'tipo' => 'Pasivo'],
            ['codigo' => '529', 'nombre' => 'Provisiones a corto plazo', 'tipo' => 'Pasivo'],

            // 53 Inversiones financieras a corto plazo en partes vinculadas
            ['codigo' => '53', 'nombre' => 'Inversiones financieras a corto plazo en partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '530', 'nombre' => 'Participaciones a corto plazo en Entidades de la Iglesia', 'tipo' => 'Activo'],
            ['codigo' => '531', 'nombre' => 'Valores representativos de deuda a corto plazo en Entidades de la Iglesia', 'tipo' => 'Activo'],
            ['codigo' => '532', 'nombre' => 'Créditos a corto plazo a partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '5323', 'nombre' => 'Créditos a corto plazo a entidades diocesanas', 'tipo' => 'Activo'],
            ['codigo' => '5324', 'nombre' => 'Créditos a corto plazo a otras entidades de la Iglesia', 'tipo' => 'Activo'],
            ['codigo' => '5325', 'nombre' => 'Créditos a corto plazo a otras partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '533', 'nombre' => 'Intereses a corto plazo de valores representativos de deuda de partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '534', 'nombre' => 'Intereses a corto plazo de créditos a partes vinculadas', 'tipo' => 'Activo'],

            // 54 Otras inversiones financieras a corto plazo
            ['codigo' => '54', 'nombre' => 'Otras inversiones financieras a corto plazo', 'tipo' => 'Activo'],
            ['codigo' => '540', 'nombre' => 'Inversiones financieras a corto plazo en instrumentos de patrimonio', 'tipo' => 'Activo'],
            ['codigo' => '541', 'nombre' => 'Valores representativos de deuda a corto plazo', 'tipo' => 'Activo'],
            ['codigo' => '542', 'nombre' => 'Créditos a corto plazo', 'tipo' => 'Activo'],
            ['codigo' => '543', 'nombre' => 'Créditos a corto plazo por enajenación de inmovilizado', 'tipo' => 'Activo'],
            ['codigo' => '546', 'nombre' => 'Intereses a corto plazo de valores representativos de deuda', 'tipo' => 'Activo'],
            ['codigo' => '547', 'nombre' => 'Intereses a corto plazo de créditos', 'tipo' => 'Activo'],
            ['codigo' => '548', 'nombre' => 'Imposiciones a corto plazo', 'tipo' => 'Activo'],

            // 55 Otras cuentas no bancarias
            ['codigo' => '55', 'nombre' => 'Otras cuentas no bancarias', 'tipo' => 'Activo'],
            ['codigo' => '552', 'nombre' => 'Cuenta corriente con otras personas y entidades vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '555', 'nombre' => 'Partidas pendientes de aplicación', 'tipo' => 'Activo'],

            // 56 Fianzas y depósitos recibidos y constituidos a corto plazo
            ['codigo' => '56', 'nombre' => 'Fianzas y depósitos recibidos y constituidos a corto plazo y ajustes por periodificación', 'tipo' => 'Pasivo'],
            ['codigo' => '560', 'nombre' => 'Fianzas recibidas a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '561', 'nombre' => 'Depósitos recibidos a corto plazo', 'tipo' => 'Pasivo'],
            ['codigo' => '566', 'nombre' => 'Depósitos constituidos a corto plazo', 'tipo' => 'Activo'],

            // 57 Tesorería
            ['codigo' => '57', 'nombre' => 'Tesorería', 'tipo' => 'Activo'],
            ['codigo' => '570', 'nombre' => 'Caja, euros', 'tipo' => 'Activo'],
            ['codigo' => '571', 'nombre' => 'Caja, moneda extranjera', 'tipo' => 'Activo'],
            ['codigo' => '572', 'nombre' => 'Bancos e instituciones de crédito c/c vista, euros', 'tipo' => 'Activo'],
            ['codigo' => '573', 'nombre' => 'Bancos e instituciones de crédito c/c vista, moneda extranjera', 'tipo' => 'Activo'],
            ['codigo' => '574', 'nombre' => 'Bancos e instituciones de crédito, cuentas de ahorro, euros', 'tipo' => 'Activo'],
            ['codigo' => '575', 'nombre' => 'Bancos e instituciones de crédito, cuentas de ahorro, moneda extranjera', 'tipo' => 'Activo'],
            ['codigo' => '576', 'nombre' => 'Inversiones a corto plazo de gran liquidez', 'tipo' => 'Activo'],

            // 58 Activos no corrientes mantenidos para la venta
            ['codigo' => '58', 'nombre' => 'Activos no corrientes mantenidos para la venta y activos y pasivos asociados', 'tipo' => 'Activo'],
            ['codigo' => '580', 'nombre' => 'Inmovilizado', 'tipo' => 'Activo'],
            ['codigo' => '581', 'nombre' => 'Inversiones con entidades vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '582', 'nombre' => 'Inversiones financieras', 'tipo' => 'Activo'],
            ['codigo' => '587', 'nombre' => 'Deudas con personas y entidades vinculadas', 'tipo' => 'Pasivo'],
            ['codigo' => '589', 'nombre' => 'Otros pasivos', 'tipo' => 'Pasivo'],

            // 59 Deterioro de valor de inversiones financieras a corto plazo
            ['codigo' => '59', 'nombre' => 'Deterioro de valor de inversiones financieras a c/p y de activos no corrientes mantenidos para la venta', 'tipo' => 'Activo'],
            ['codigo' => '593', 'nombre' => 'Deterioro de valor de participaciones a corto plazo en partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '595', 'nombre' => 'Deterioro de valor de créditos a corto plazo a partes vinculadas', 'tipo' => 'Activo'],
            ['codigo' => '597', 'nombre' => 'Deterioro de valor de valores representativos de deuda a corto plazo', 'tipo' => 'Activo'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 6 — Compras y gastos
    // ──────────────────────────────────────────────────────────────
    private function grupo6(): array
    {
        return [
            // 60 Compras
            ['codigo' => '60', 'nombre' => 'Compras', 'tipo' => 'Gasto'],
            ['codigo' => '600', 'nombre' => 'Compras objetos religiosos, estampas, medallas, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6000', 'nombre' => 'Compras objetos religiosos, estampas, medallas, insignias, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '601', 'nombre' => 'Compras materiales para túnicas y otras vestimentas cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '6010', 'nombre' => 'Compras telas y otros ornamentos vestiduras cofrades', 'tipo' => 'Gasto'],
            ['codigo' => '602', 'nombre' => 'Compras de otros aprovisionamientos', 'tipo' => 'Gasto'],
            ['codigo' => '6020', 'nombre' => 'Compras otros aprovisionamientos', 'tipo' => 'Gasto'],
            ['codigo' => '607', 'nombre' => 'Trabajos realizados por otras entidades o empresas', 'tipo' => 'Gasto'],
            ['codigo' => '6070', 'nombre' => 'Trabajos realizados mantenimiento bienes cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '608', 'nombre' => 'Devoluciones de compras', 'tipo' => 'Gasto'],

            // 61 Variación de existencias
            ['codigo' => '61', 'nombre' => 'Variación de existencias', 'tipo' => 'Gasto'],
            ['codigo' => '610', 'nombre' => 'Variación de existencias de bienes destinados a la venta', 'tipo' => 'Gasto'],
            ['codigo' => '6100', 'nombre' => 'Variación de existencias de bienes destinados a la venta', 'tipo' => 'Gasto'],

            // 62 Servicios exteriores
            ['codigo' => '62', 'nombre' => 'Servicios exteriores', 'tipo' => 'Gasto'],
            ['codigo' => '621', 'nombre' => 'Arrendamientos y cánones', 'tipo' => 'Gasto'],
            ['codigo' => '6210', 'nombre' => 'Oficinas, almacenes y otros inmuebles', 'tipo' => 'Gasto'],
            ['codigo' => '6211', 'nombre' => 'Enseres cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '6212', 'nombre' => 'Otros arrendamientos', 'tipo' => 'Gasto'],
            ['codigo' => '622', 'nombre' => 'Reparaciones y conservación', 'tipo' => 'Gasto'],
            ['codigo' => '6220', 'nombre' => 'Reparación locales, almacenes y otros inmuebles', 'tipo' => 'Gasto'],
            ['codigo' => '6221', 'nombre' => 'Reparación y conservación enseres cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '6222', 'nombre' => 'Mantenimiento y conservación imágenes procesionales y de culto', 'tipo' => 'Gasto'],
            ['codigo' => '6223', 'nombre' => 'Mantenimiento páginas web, correos corporativos, redes sociales, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '623', 'nombre' => 'Servicios de profesionales independientes', 'tipo' => 'Gasto'],
            ['codigo' => '6230', 'nombre' => 'Servicios jurídicos y laborales', 'tipo' => 'Gasto'],
            ['codigo' => '6231', 'nombre' => 'Servicios económicos y asesoramiento', 'tipo' => 'Gasto'],
            ['codigo' => '624', 'nombre' => 'Transportes', 'tipo' => 'Gasto'],
            ['codigo' => '6240', 'nombre' => 'Transportes realizados a exposiciones, restauraciones, etc. (no procesiones)', 'tipo' => 'Gasto'],
            ['codigo' => '625', 'nombre' => 'Primas de seguros', 'tipo' => 'Gasto'],
            ['codigo' => '6250', 'nombre' => 'Locales propios y arrendados', 'tipo' => 'Gasto'],
            ['codigo' => '6251', 'nombre' => 'Imágenes y grupos escultóricos procesionales y de culto', 'tipo' => 'Gasto'],
            ['codigo' => '6252', 'nombre' => 'Salida procesión: ordinaria y extraordinaria', 'tipo' => 'Gasto'],
            ['codigo' => '626', 'nombre' => 'Servicios bancarios y similares', 'tipo' => 'Gasto'],
            ['codigo' => '6260', 'nombre' => 'Servicios bancarios, cobros de recibos, transferencias, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '627', 'nombre' => 'Publicidad, propaganda y relaciones públicas', 'tipo' => 'Gasto'],
            ['codigo' => '6270', 'nombre' => 'Carteles, folletos actos Cofradía y otro material publicidad y propaganda', 'tipo' => 'Gasto'],
            ['codigo' => '6271', 'nombre' => 'Atenciones protocolarias (comidas, cenas, regalos, etc.)', 'tipo' => 'Gasto'],
            ['codigo' => '628', 'nombre' => 'Suministros', 'tipo' => 'Gasto'],
            ['codigo' => '6281', 'nombre' => 'Electricidad', 'tipo' => 'Gasto'],
            ['codigo' => '6282', 'nombre' => 'Agua', 'tipo' => 'Gasto'],
            ['codigo' => '6283', 'nombre' => 'Gas', 'tipo' => 'Gasto'],
            ['codigo' => '6284', 'nombre' => 'Otros suministros', 'tipo' => 'Gasto'],
            ['codigo' => '629', 'nombre' => 'Otros servicios', 'tipo' => 'Gasto'],
            ['codigo' => '6290', 'nombre' => 'Locomoción y dietas', 'tipo' => 'Gasto'],
            ['codigo' => '6291', 'nombre' => 'Material de oficina: papelería, impresos oficiales, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6292', 'nombre' => 'Correos y teléfono', 'tipo' => 'Gasto'],
            ['codigo' => '6293', 'nombre' => 'Servicios de limpieza', 'tipo' => 'Gasto'],
            ['codigo' => '6294', 'nombre' => 'Actividades litúrgicas: sagradas formas, vino, cera, libros litúrgicos, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6295', 'nombre' => 'Actividades pastorales: catequesis, conferencias, cursillos, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6296', 'nombre' => 'Actividades asistenciales sociales (Bolsa de Caridad)', 'tipo' => 'Gasto'],
            ['codigo' => '6297', 'nombre' => 'Atenciones a Cáritas Parroquial', 'tipo' => 'Gasto'],
            ['codigo' => '6298', 'nombre' => 'Salida procesional Cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '62980', 'nombre' => 'Cera, baterías, bombillas, pilas, otros, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '62981', 'nombre' => 'Música', 'tipo' => 'Gasto'],
            ['codigo' => '62982', 'nombre' => 'Trabajos personal externo: carpinteros, electricistas, transportistas, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '62983', 'nombre' => 'Arreglos florales', 'tipo' => 'Gasto'],
            ['codigo' => '62984', 'nombre' => 'Gastos por Hermandades, Agrupaciones, Pasos, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '62985', 'nombre' => 'Salidas extraordinarias de la Cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '6299', 'nombre' => 'Otras actividades', 'tipo' => 'Gasto'],

            // 63 Tributos
            ['codigo' => '63', 'nombre' => 'Tributos', 'tipo' => 'Gasto'],
            ['codigo' => '630', 'nombre' => 'Impuesto sobre beneficios', 'tipo' => 'Gasto'],
            ['codigo' => '6300', 'nombre' => 'Impuesto sobre beneficios sujetos', 'tipo' => 'Gasto'],
            ['codigo' => '631', 'nombre' => 'Otros tributos', 'tipo' => 'Gasto'],
            ['codigo' => '6310', 'nombre' => 'IBI locales propios', 'tipo' => 'Gasto'],
            ['codigo' => '6311', 'nombre' => 'Tasas municipales', 'tipo' => 'Gasto'],
            ['codigo' => '636', 'nombre' => 'Devolución de impuestos', 'tipo' => 'Gasto'],
            ['codigo' => '638', 'nombre' => 'Ajustes positivos en la imposición sobre beneficios', 'tipo' => 'Gasto'],
            ['codigo' => '639', 'nombre' => 'Ajustes positivos en la imposición indirecta', 'tipo' => 'Gasto'],

            // 64 Gastos de personal
            ['codigo' => '64', 'nombre' => 'Gastos de personal', 'tipo' => 'Gasto'],
            ['codigo' => '640', 'nombre' => 'Sueldos y salarios personal contratado', 'tipo' => 'Gasto'],
            ['codigo' => '6400', 'nombre' => 'Sueldos y salarios personal propio', 'tipo' => 'Gasto'],
            ['codigo' => '641', 'nombre' => 'Indemnizaciones', 'tipo' => 'Gasto'],
            ['codigo' => '642', 'nombre' => 'Seguridad Social a cargo de la Cofradía', 'tipo' => 'Gasto'],
            ['codigo' => '6420', 'nombre' => 'Seguridad Social a cargo de la entidad de seglares', 'tipo' => 'Gasto'],
            ['codigo' => '649', 'nombre' => 'Retribuciones a colaboradores, voluntarios, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6490', 'nombre' => 'Gratificaciones colaboradores, voluntarios, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6491', 'nombre' => 'Estipendio sacerdotes: cultos y funciones religiosas', 'tipo' => 'Gasto'],
            ['codigo' => '6492', 'nombre' => 'Otros complementos', 'tipo' => 'Gasto'],

            // 65 Ayudas monetarias de la entidad y otros gastos de gestión
            ['codigo' => '65', 'nombre' => 'Ayudas monetarias de la entidad y otros gastos de gestión', 'tipo' => 'Gasto'],
            ['codigo' => '650', 'nombre' => 'Ayudas monetarias', 'tipo' => 'Gasto'],
            ['codigo' => '6501', 'nombre' => 'Ayudas monetarias a Entidades Diocesanas (Delegación Diocesana de HH. y CC.)', 'tipo' => 'Gasto'],
            ['codigo' => '6502', 'nombre' => 'Ayudas monetarias a la Parroquia', 'tipo' => 'Gasto'],
            ['codigo' => '6503', 'nombre' => 'Ayudas monetarias a Cáritas Parroquial', 'tipo' => 'Gasto'],
            ['codigo' => '6504', 'nombre' => 'Ayudas monetarias individuales', 'tipo' => 'Gasto'],
            ['codigo' => '651', 'nombre' => 'Ayudas no monetarias', 'tipo' => 'Gasto'],
            ['codigo' => '6511', 'nombre' => 'Prestaciones no monetarias individuales', 'tipo' => 'Gasto'],
            ['codigo' => '6512', 'nombre' => 'Prestaciones no monetarias a otras Entidades', 'tipo' => 'Gasto'],
            ['codigo' => '653', 'nombre' => 'Compensación de gastos por prestaciones de colaboración', 'tipo' => 'Gasto'],
            ['codigo' => '6530', 'nombre' => 'Compensación gastos por prestación de colaboración y voluntariado', 'tipo' => 'Gasto'],
            ['codigo' => '654', 'nombre' => 'Reembolsos de gastos al órgano de gobierno', 'tipo' => 'Gasto'],
            ['codigo' => '6540', 'nombre' => 'Reembolsos de gastos al órgano de gobierno', 'tipo' => 'Gasto'],
            ['codigo' => '655', 'nombre' => 'Pérdidas de créditos incobrables', 'tipo' => 'Gasto'],
            ['codigo' => '6550', 'nombre' => 'Pérdidas cuotas cofrades incobrables', 'tipo' => 'Gasto'],
            ['codigo' => '658', 'nombre' => 'Reintegro de subvenciones, donaciones y legados recibidos, afectos a la actividad propia', 'tipo' => 'Gasto'],
            ['codigo' => '659', 'nombre' => 'Otras pérdidas en gestión corriente', 'tipo' => 'Gasto'],
            ['codigo' => '6590', 'nombre' => 'Gastos de loterías, rifas, etc.', 'tipo' => 'Gasto'],

            // 66 Gastos financieros
            ['codigo' => '66', 'nombre' => 'Gastos financieros', 'tipo' => 'Gasto'],
            ['codigo' => '662', 'nombre' => 'Intereses de deudas', 'tipo' => 'Gasto'],
            ['codigo' => '6620', 'nombre' => 'Intereses de deudas de entidades diocesanas', 'tipo' => 'Gasto'],
            ['codigo' => '6623', 'nombre' => 'Intereses de deudas con entidades de crédito', 'tipo' => 'Gasto'],
            ['codigo' => '6624', 'nombre' => 'Intereses de deudas de otras entidades o particulares', 'tipo' => 'Gasto'],
            ['codigo' => '669', 'nombre' => 'Otros gastos financieros', 'tipo' => 'Gasto'],
            ['codigo' => '6690', 'nombre' => 'Otros gastos financieros', 'tipo' => 'Gasto'],

            // 67 Pérdidas procedentes de activos no corrientes y gastos excepcionales
            ['codigo' => '67', 'nombre' => 'Pérdidas procedentes de activos no corrientes y gastos excepcionales', 'tipo' => 'Gasto'],
            ['codigo' => '670', 'nombre' => 'Pérdidas procedentes del inmovilizado intangible', 'tipo' => 'Gasto'],
            ['codigo' => '6700', 'nombre' => 'Pérdidas por enajenación u otras causas inmovilizado intangible', 'tipo' => 'Gasto'],
            ['codigo' => '671', 'nombre' => 'Pérdidas procedentes del inmovilizado material y de bienes del Patrimonio Histórico', 'tipo' => 'Gasto'],
            ['codigo' => '6710', 'nombre' => 'Pérdidas por enajenación u otras causas inmovilizado material', 'tipo' => 'Gasto'],
            ['codigo' => '678', 'nombre' => 'Gastos excepcionales', 'tipo' => 'Gasto'],
            ['codigo' => '6780', 'nombre' => 'Gastos excepcionales no contemplados en otras partidas', 'tipo' => 'Gasto'],

            // 68 Dotaciones para amortizaciones
            ['codigo' => '68', 'nombre' => 'Dotaciones para amortizaciones', 'tipo' => 'Gasto'],
            ['codigo' => '680', 'nombre' => 'Amortización del inmovilizado intangible', 'tipo' => 'Gasto'],
            ['codigo' => '6803', 'nombre' => 'Patentes, licencias, marcas y similares', 'tipo' => 'Gasto'],
            ['codigo' => '6806', 'nombre' => 'Aplicaciones informáticas', 'tipo' => 'Gasto'],
            ['codigo' => '6809', 'nombre' => 'Otro inmovilizado intangible', 'tipo' => 'Gasto'],
            ['codigo' => '681', 'nombre' => 'Amortización del inmovilizado material', 'tipo' => 'Gasto'],
            ['codigo' => '6811', 'nombre' => 'Construcciones: locales propios de oficinas, almacenes, casas hermandad, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6816', 'nombre' => 'Mobiliario: oficina, casa de hermandad, capilla, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6817', 'nombre' => 'Equipos proceso de información: ordenadores, impresoras, etc.', 'tipo' => 'Gasto'],
            ['codigo' => '6819', 'nombre' => 'Enseres patrimoniales Cofradía', 'tipo' => 'Gasto'],

            // 69 Pérdidas por deterioro y otras dotaciones
            ['codigo' => '69', 'nombre' => 'Pérdidas por deterioro y otras dotaciones', 'tipo' => 'Gasto'],
            ['codigo' => '690', 'nombre' => 'Pérdidas por deterioro del inmovilizado intangible', 'tipo' => 'Gasto'],
            ['codigo' => '691', 'nombre' => 'Pérdidas por deterioro del inmovilizado material y de bienes del Patrimonio Histórico', 'tipo' => 'Gasto'],
            ['codigo' => '692', 'nombre' => 'Pérdidas por deterioro de las inversiones inmobiliarias', 'tipo' => 'Gasto'],
            ['codigo' => '693', 'nombre' => 'Pérdidas por deterioro de existencias', 'tipo' => 'Gasto'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  GRUPO 7 — Ventas e ingresos
    // ──────────────────────────────────────────────────────────────
    private function grupo7(): array
    {
        return [
            // 70 Ventas de mercaderías, de producción propia, de servicios, etc.
            ['codigo' => '70', 'nombre' => 'Ventas de mercaderías, de producción propia, de servicios, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '700', 'nombre' => 'Ventas de mercaderías', 'tipo' => 'Ingreso'],
            ['codigo' => '7000', 'nombre' => 'Ventas de estampas, medallas, insignias, lazos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '701', 'nombre' => 'Ventas libros, publicaciones cofradía, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7010', 'nombre' => 'Ventas publicaciones propias Cofradía: revistas, libros, folletos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7011', 'nombre' => 'Ventas publicaciones ajenas: libros y otras ediciones de análoga naturaleza', 'tipo' => 'Ingreso'],
            ['codigo' => '702', 'nombre' => 'Ventas actividades económicas: cantinas, barras, bingos, museos, exposiciones, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7020', 'nombre' => 'Ventas actividades económicas Cofradía: permanentes o esporádicas', 'tipo' => 'Ingreso'],
            ['codigo' => '7021', 'nombre' => 'Ventas actividades económicas: Hermandades, Agrupaciones, Pasos, etc. sin personalidad jurídica propia', 'tipo' => 'Ingreso'],
            ['codigo' => '705', 'nombre' => 'Prestaciones de servicios', 'tipo' => 'Ingreso'],

            // 71 Variación de existencias
            ['codigo' => '71', 'nombre' => 'Variación de existencias', 'tipo' => 'Ingreso'],
            ['codigo' => '710', 'nombre' => 'Variación de existencias de productos en curso', 'tipo' => 'Ingreso'],
            ['codigo' => '711', 'nombre' => 'Variación de existencias de productos semiterminados', 'tipo' => 'Ingreso'],
            ['codigo' => '712', 'nombre' => 'Variación de existencias de productos terminados', 'tipo' => 'Ingreso'],

            // 72 Ingresos de los fieles
            ['codigo' => '72', 'nombre' => 'Ingresos de los fieles', 'tipo' => 'Ingreso'],
            ['codigo' => '720', 'nombre' => 'Cuotas de Cofrades', 'tipo' => 'Ingreso'],
            ['codigo' => '7200', 'nombre' => 'Cuotas anuales de cofrades y hermanos', 'tipo' => 'Ingreso'],
            ['codigo' => '7201', 'nombre' => 'Cuotas de procesión', 'tipo' => 'Ingreso'],
            ['codigo' => '7202', 'nombre' => 'Cuotas de Hermandades, Agrupaciones, Pasos, etc. sin personalidad jurídica propia', 'tipo' => 'Ingreso'],
            ['codigo' => '7203', 'nombre' => 'Cuotas extraordinarias: derramas u otras cuotas de análoga naturaleza', 'tipo' => 'Ingreso'],
            ['codigo' => '721', 'nombre' => 'Ingresos de usuarios', 'tipo' => 'Ingreso'],
            ['codigo' => '7210', 'nombre' => 'Ingresos formación, conferencias, convivencias, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7211', 'nombre' => 'Ingresos formación, conferencias, convivencias, etc. de entidades sin personalidad jurídica propia', 'tipo' => 'Ingreso'],
            ['codigo' => '7212', 'nombre' => 'Ingresos por túnicas y otros ornamentos cofrades para los hermanos', 'tipo' => 'Ingreso'],
            ['codigo' => '722', 'nombre' => 'Promociones para captación de recursos', 'tipo' => 'Ingreso'],
            ['codigo' => '7220', 'nombre' => 'Ingresos por conciertos, comidas o cenas, otros eventos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '723', 'nombre' => 'Ingresos de patrocinadores y colaboraciones', 'tipo' => 'Ingreso'],
            ['codigo' => '7230', 'nombre' => 'Ingresos patrocinadores de publicaciones: revistas, libros, folletos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7231', 'nombre' => 'Ingresos de colaboradores', 'tipo' => 'Ingreso'],
            ['codigo' => '724', 'nombre' => 'Ingresos por donativos', 'tipo' => 'Ingreso'],
            ['codigo' => '7240', 'nombre' => 'Ingresos cepillos: cultos, besapiés, otras funciones religiosas, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7241', 'nombre' => 'Ingresos por donativos sin finalidad específica', 'tipo' => 'Ingreso'],
            ['codigo' => '7242', 'nombre' => 'Ingresos con ocasión de sacramentos y sacramentales', 'tipo' => 'Ingreso'],

            // 73 Trabajos realizados para la entidad
            ['codigo' => '73', 'nombre' => 'Trabajos realizados para la entidad', 'tipo' => 'Ingreso'],
            ['codigo' => '730', 'nombre' => 'Trabajos realizados para el inmovilizado intangible', 'tipo' => 'Ingreso'],
            ['codigo' => '7300', 'nombre' => 'Realización páginas web propia, redes sociales, programas informáticos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7301', 'nombre' => 'Realización trabajos declaración interés turístico (Regional, Nacional o Internacional)', 'tipo' => 'Ingreso'],
            ['codigo' => '731', 'nombre' => 'Trabajos realizados para el inmovilizado material', 'tipo' => 'Ingreso'],
            ['codigo' => '7310', 'nombre' => 'Realización de enseres para la Cofradía o Hermandad', 'tipo' => 'Ingreso'],
            ['codigo' => '7311', 'nombre' => 'Otros trabajos realizados para el inmovilizado material', 'tipo' => 'Ingreso'],

            // 74 Subvenciones
            ['codigo' => '74', 'nombre' => 'Subvenciones', 'tipo' => 'Ingreso'],
            ['codigo' => '740', 'nombre' => 'Subvenciones públicas: CCAA, Ayuntamiento, otras instituciones públicas, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7401', 'nombre' => 'Subvenciones públicas del Estado', 'tipo' => 'Ingreso'],
            ['codigo' => '7402', 'nombre' => 'Subvenciones públicas de la Comunidad Autónoma', 'tipo' => 'Ingreso'],
            ['codigo' => '7403', 'nombre' => 'Subvenciones públicas del Ayuntamiento', 'tipo' => 'Ingreso'],
            ['codigo' => '741', 'nombre' => 'Subvenciones privadas: Fundaciones, Empresas, Bancos, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7410', 'nombre' => 'Subvenciones Fundaciones propias', 'tipo' => 'Ingreso'],
            ['codigo' => '7411', 'nombre' => 'Subvenciones Fundaciones ajenas', 'tipo' => 'Ingreso'],
            ['codigo' => '7412', 'nombre' => 'Subvenciones Empresas, bancos, etc.', 'tipo' => 'Ingreso'],

            // 75 Otros ingresos de gestión
            ['codigo' => '75', 'nombre' => 'Otros ingresos de gestión', 'tipo' => 'Ingreso'],
            ['codigo' => '752', 'nombre' => 'Ingresos por arrendamientos', 'tipo' => 'Ingreso'],
            ['codigo' => '7520', 'nombre' => 'Ingresos por arrendamientos de locales', 'tipo' => 'Ingreso'],
            ['codigo' => '7521', 'nombre' => 'Ingresos por otros arrendamientos: túnicas, enseres cofradías, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '759', 'nombre' => 'Ingresos por servicios diversos', 'tipo' => 'Ingreso'],
            ['codigo' => '7590', 'nombre' => 'Ingresos por Rifas, Loterías, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7591', 'nombre' => 'Ingresos por otros servicios diversos', 'tipo' => 'Ingreso'],

            // 76 Ingresos financieros
            ['codigo' => '76', 'nombre' => 'Ingresos financieros', 'tipo' => 'Ingreso'],
            ['codigo' => '760', 'nombre' => 'Ingresos de participaciones en instrumentos de patrimonio, renta variable y fija', 'tipo' => 'Ingreso'],
            ['codigo' => '7600', 'nombre' => 'Ingresos financieros Fondos de Inversión', 'tipo' => 'Ingreso'],
            ['codigo' => '7601', 'nombre' => 'Ingresos financieros otros fondos de análoga naturaleza', 'tipo' => 'Ingreso'],
            ['codigo' => '769', 'nombre' => 'Otros ingresos financieros de cuentas corrientes, ahorro, plazo fijo, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7690', 'nombre' => 'Ingresos financieros cuentas corrientes y ahorro', 'tipo' => 'Ingreso'],
            ['codigo' => '7691', 'nombre' => 'Ingresos financieros plazos fijos y cuentas de análoga naturaleza', 'tipo' => 'Ingreso'],

            // 77 Beneficios procedentes de activos no corrientes e ingresos excepcionales
            ['codigo' => '77', 'nombre' => 'Beneficios procedentes de activos no corrientes e ingresos excepcionales', 'tipo' => 'Ingreso'],
            ['codigo' => '770', 'nombre' => 'Beneficios procedentes del inmovilizado intangible', 'tipo' => 'Ingreso'],
            ['codigo' => '7700', 'nombre' => 'Beneficio enajenación inmovilizado intangible', 'tipo' => 'Ingreso'],
            ['codigo' => '771', 'nombre' => 'Beneficios procedentes del inmovilizado material y de bienes del Patrimonio Histórico', 'tipo' => 'Ingreso'],
            ['codigo' => '7710', 'nombre' => 'Beneficio enajenación de locales y otras construcciones', 'tipo' => 'Ingreso'],
            ['codigo' => '7711', 'nombre' => 'Beneficio enajenación imágenes, grupos escultóricos, tronos, andas, etc.', 'tipo' => 'Ingreso'],
            ['codigo' => '7712', 'nombre' => 'Beneficio enajenación enseres Cofradía procesionales o de culto', 'tipo' => 'Ingreso'],
            ['codigo' => '778', 'nombre' => 'Ingresos excepcionales', 'tipo' => 'Ingreso'],
            ['codigo' => '7780', 'nombre' => 'Ingresos por indemnización de seguros', 'tipo' => 'Ingreso'],
            ['codigo' => '7781', 'nombre' => 'Otros ingresos excepcionales', 'tipo' => 'Ingreso'],
        ];
    }
}
