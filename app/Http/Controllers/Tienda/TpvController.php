<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TpvController extends Controller
{
    public function __invoke(): View
    {
        return view('tienda.tpv.index');
    }
}
