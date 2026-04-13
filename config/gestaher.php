<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gastos bancarios por devolución de recibo (SEPA)
    |--------------------------------------------------------------------------
    |
    | Importe orientativo que se suma al recibo devuelto en comunicaciones al
    | hermano cuando el banco no informa comisión en el fichero (camt.053).
    |
    */
    'remesa_gastos_devolucion_default_eur' => (float) env('REMESA_GASTOS_DEVOLUCION_DEFAULT', 5),

];
