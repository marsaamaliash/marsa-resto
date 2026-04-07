<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Print Label Bulk - SSO Version</title>
    <style>
        @page {
            size: 100mm 25mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .row {
            display: flex;
            width: 100mm;
            height: 25mm;
            page-break-after: always;
        }

        .label {
            width: 50mm;
            height: 25mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 0 auto;
            gap: 4mm;
            margin-top: 3mm;
        }

        .logo {
            max-height: 14mm;
            width: auto;
        }

        /* Mengatur ukuran gambar QR dari Route SSO */
        .qr img {
            height: 14mm;
            width: 14mm;
            display: block;
        }

        .bottom {
            text-align: center;
            font-weight: bold;
            font-size: 4mm;
            margin-bottom: 2mm;
            font-family: sans-serif;
        }
    </style>
</head>

<body onload="window.print(); setTimeout(() => window.close(), 1000);">
    @foreach ($items->chunk(2) as $pair)
        <div class="row">
            @foreach ($pair as $item)
                <div class="label">
                    <div class="top">
                        {{-- Gunakan Logo Perusahaan --}}
                        <img src="{{ asset('images/logoSCCR.png') }}" alt="Logo" class="logo" />

                        {{-- Menggunakan Route SSO sesuai keinginan Anda --}}
                        <div class="qr">
                            <img src="{{ route('sso.qr.generate', ['q' => $item->kode_label, 's' => 200]) }}"
                                alt="QR">
                        </div>
                    </div>
                    <div class="bottom">{{ $item->kode_label }}</div>
                </div>
            @endforeach

            {{-- Jika jumlah ganjil, tambahkan label kosong di sisi kanan --}}
            @if ($pair->count() == 1)
                <div class="label"></div>
            @endif
        </div>
    @endforeach
</body>

</html>
