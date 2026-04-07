<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    public function generate(Request $request)
    {
        // Ambil data yang akan dijadikan QR dari parameter 'q'
        $data = $request->query('q');

        // parameter size, default 200 jika tidak diisi
        $size = $request->query('s', 200);

        if (! $data) {
            return response('No Data', 400);
        }

        // Generate QR Code sebagai SVG
        // Format SVG sangat ringan dan tajam untuk di-print
        $qrCode = QrCode::size($size)
            ->margin(1)
            ->generate($data);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }
}
