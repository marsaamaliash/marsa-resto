<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>SCCR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100 h-screen flex flex-col overflow-hidden relative m-0 p-0">

    {{-- 1) HEADER FIX (NavBar + optional Header Module global) --}}
    <header class="flex-none flex flex-col w-full z-[60] shadow-md">
        <div class="block w-full border-none p-0 m-0">
            <x-ui.sccr-navigation />
        </div>

        @isset($header)
            <div class="block w-full bg-emerald-500 m-0 p-0 border-t-0 leading-none">
                <div class="w-full py-6 px-6 text-white">
                    <h2 class="font-bold text-xl m-0 p-0">
                        {{ $header }}
                    </h2>
                </div>
            </div>
        @endisset
    </header>

    {{-- 2) MAIN: sidebar + content --}}
    <main class="flex-1 min-h-0 overflow-hidden w-full relative">
        <div x-data="{
            sidebarOpen: (localStorage.getItem('sccr_sidebar_open') ?? '1') === '1',
            set(v) {
                this.sidebarOpen = v;
                localStorage.setItem('sccr_sidebar_open', v ? '1' : '0');
            }
        }" x-on:sccr-sidebar-toggle.window="set(!sidebarOpen)"
            x-on:sccr-sidebar-open.window="set(true)" x-on:sccr-sidebar-close.window="set(false)"
            class="h-full min-h-0 w-full flex">

            {{-- SIDEBAR --}}
            @auth
                <div x-show="sidebarOpen" x-cloak class="flex-none h-full min-h-0">
                    <livewire:layout.sccr-sidebar />
                </div>
            @endauth

            {{-- CONTENT SCROLLER --}}
            <div class="flex-1 min-w-0 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain">
                <div class="min-h-full w-full">
                    {{ $slot }}
                </div>
            </div>

        </div>
    </main>

    {{-- 3) FOOTER FIX --}}
    <footer
        class="flex-none bg-gray-700/60 text-gray-200 text-center py-4 backdrop-blur-md text-xs border-t border-white/10">
        Copyright © {{ date('Y') }} SCCR Indonesia. All rights reserved.
    </footer>

    {{-- ================= GLOBAL FLASH TOAST (redirect aware) ================= --}}
    @php($flashToast = session('toast'))
    @if (is_array($flashToast) && !empty($flashToast['message']))
        <x-ui.sccr-toast :show="true" :type="$flashToast['type'] ?? 'success'" :message="$flashToast['message'] ?? ''" />
    @endif

    @livewireScripts

    <script>
        document.addEventListener('livewire:navigated', () => {
            // init ulang script pihak ketiga kalau perlu
        });
    </script>

    {{-- GLOBAL PRINT FUNCTION --}}
    <script>
        function printSccrFlexible(kode, mode, customW, customH) {
            let pStyle = '';
            let cHtml = '';
            const qUrl = `/sso/generate-qr?q=${encodeURIComponent(kode)}&s=500`;
            const lUrl = "{{ asset('images/logoSCCR.png') }}";

            if (mode === 'label-sato') {
                pStyle = `
                    @page { size: 50mm 25mm; margin: 0; }
                    body { margin: 0; padding: 0; }
                    .cnt { width: 50mm; height: 25mm; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden; }
                    .lg { max-height: 10mm; margin-bottom: 2mm; }
                    .qr { height: 12mm; }
                    .tx { font-size: 3mm; font-weight: bold; margin-top: 1mm; font-family: sans-serif; }
                `;
                cHtml = `
                    <div class="cnt">
                        <div style="display:flex; align-items:center; gap:3mm;">
                            <img src="${lUrl}" class="lg">
                            <img src="${qUrl}" class="qr">
                        </div>
                        <div class="tx">${kode}</div>
                    </div>
                `;
            } else if (mode === 'a4-announcement') {
                pStyle = `
                    @page { size: A4; margin: 20mm; }
                    .cnt { text-align: center; font-family: sans-serif; }
                    .qr { width: 120mm; margin-top: 20mm; }
                    .title { font-size: 24pt; font-weight: bold; margin-bottom: 10mm; }
                `;
                cHtml = `
                    <div class="cnt">
                        <div class="title">PENGUMUMAN / ANNOUNCEMENT</div>
                        <img src="${qUrl}" class="qr">
                        <h1 style="margin-top:10mm;">Scan untuk Informasi Lebih Lanjut</h1>
                        <p style="font-size:18pt;">${kode}</p>
                    </div>
                `;
            } else {
                pStyle =
                    `@page { size: auto; margin: 10mm; } .cnt { text-align: center; font-family: sans-serif; } .qr { width: 80mm; }`;
                cHtml =
                    `<div class="cnt"><img src="${qUrl}" class="qr"><div style="font-size: 14pt; margin-top: 5mm;">${kode}</div></div>`;
            }

            const w = window.open('', '_blank');
            w.document.write(
                '<html><head><style>' + pStyle + '</style></head><body>' + cHtml +
                '<script>window.onload = function() { window.print(); setTimeout(() => { window.close(); }, 500); }<\/script></body></html>'
            );
            w.document.close();
        }
    </script>
</body>

</html>
