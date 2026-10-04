<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        return view('portfolio.certificates.index', [
            'certificates' => Certificate::ordered()->get(),
        ]);
    }
}
