<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InformesPanelController extends Controller
{
    public function index(): View
    {
        return view('informes.index');
    }
}
