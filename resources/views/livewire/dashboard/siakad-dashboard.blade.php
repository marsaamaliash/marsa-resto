<x-ui.sccr-card wire:key="siakad-dashboard">

    {{-- Header --}}
    <div class="relative py-4 bg-blue-600 rounded-b-3xl shadow-lg text-white z-10">
        <div class="px-4 mb-2">
            <h1 class="text-3xl font-bold">Dashboard Sistem Akademik</h1>
            <p class="text-lg">Pengelolaan Data Akademik</p>
        </div>

        <x-ui.sccr-breadcrumb :items="[
            ['label' => 'Main Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campus', 'url' => route('dashboard.campus')],
            // ['label' => 'Siakad', 'url' => route('dashboard.siakad-dashboard')],
        ]" />

    </div>

    {{-- Room List --}}
    {{-- <div class="px-4 py-6 space-y-4">
        <h2 class="text-xl font-semibold">Room Anda</h2>
        <ul class="space-y-2">
            @forelse ($rooms as $room)
                <li class="bg-white shadow rounded px-4 py-2">
                    <div class="flex justify-between items-center">
                        <div>
                            <strong>{{ $room->name }}</strong> — {{ $room->kurikulum }} ({{ $room->semester }})
                        </div>
                        <a href="{{ route('campus.lms.room.manage', $room->id) }}">
                            <x-ui.sccr-button variant="secondary">Kelola</x-ui.sccr-button>
                        </a>
                    </div>
                </li>
            @empty
                <li class="text-gray-500">Belum ada room LMS yang Anda buat.</li>
            @endforelse
        </ul>
    </div> --}}
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 justify-center">

            <!-- Student -->
            <a href="{{ route('holdings.campus.siakad.student.students-table') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-employee.jpg') }}" alt="Student"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a>

            <!-- Lecturer -->
            {{-- <a href="{{ route('holdings.campus.siakad.lecturer-table') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-ga.png') }}" alt="GA"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a> --}}


            <!-- IR -->
            {{-- <a href="{{ route('holdings.hq.sdm.hr.employee-table') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-doc.png') }}" alt="IR"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a> --}}

            <!-- Project -->
            {{-- <a href="{{ route('holdings.hq.sdm.hr.employee-table') }}"
                class="relative rounded-2xl overflow-hidden shadow-lg transform hover:scale-105 transition duration-300 w-60 h-40">
                <img src="{{ asset('images/tb-sdm-project.png') }}" alt="Project"
                    class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition"></div>
            </a> --}}

        </div>
    </div>
</x-ui.sccr-card>
