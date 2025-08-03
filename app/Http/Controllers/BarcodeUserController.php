<?php

namespace App\Http\Controllers;

use App\BarcodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Barcode;

class BarcodeUserController extends Controller
{
    public function download($barcodeId)
    {
        $barcode = Barcode::find($barcodeId);
        $barcodeFile = (new BarcodeGenerator(width: 1280, height: 1280))->generateQrCode($barcode->value);
        return response($barcodeFile)->withHeaders([
            'Content-Type' => 'aplication/octet-stream',
            'Content-Disposition' => 'attachment; filename=' . ($barcode->name ?? $barcode->value) . '.png',
        ]);
    }
}
