<div>
    <form wire:submit.prevent="save" class="space-y-4">

        {{-- SUCCESS ALERT --}}
        @if (session()->has('success'))
            <div class="p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- MODAL LAYOUT --}}
        <x-sccr-modal-layout title="Add New Student" :groups="[
            'tab1' => 'Personal Data',
            'tab2' => 'Academic Info',
            'tab3' => 'Contact & Address',
            'tab4' => 'Parent / Guardian',
        ]" active="tab1">

            {{-- TAB 1 --}}
            <x-slot name="tab1">
                @include('livewire.holdings.campus.siakad.student.partials.tab1')
            </x-slot>

            {{-- TAB 2 --}}
            <x-slot name="tab2">
                @include('livewire.holdings.campus.siakad.student.partials.tab2')
            </x-slot>

            {{-- TAB 3 --}}
            <x-slot name="tab3">
                @include('livewire.holdings.campus.siakad.student.partials.tab3')
            </x-slot>

            {{-- TAB 4 --}}
            <x-slot name="tab4">
                @include('livewire.holdings.campus.siakad.student.partials.tab4')
            </x-slot>


            {{-- BUTTONS SLOT --}}
            <x-slot name="buttons">

                {{-- CANCEL BUTTON --}}
                <button type="button" wire:click="confirmCancel"
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-medium focus:ring-2 focus:ring-gray-400">
                    ❌ Cancel
                </button>

                {{-- SAVE BUTTON --}}
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium focus:ring-2 focus:ring-blue-400">
                    💾 Save
                </button>

            </x-slot>

        </x-sccr-modal-layout>


        {{-- TOAST --}}
        <x-sccr-toast :show="$showCancelConfirm" type="warning" message="Are you sure you want to cancel adding this student?" />


        {{-- CANCEL CONFIRM MODAL --}}
        @if ($showCancelConfirm)
            <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded shadow-lg text-center">
                    <p class="text-lg font-semibold mb-4">Are you sure you want to cancel?</p>

                    <div class="flex justify-center space-x-4">
                        <button wire:click="cancel" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Yes, Cancel
                        </button>

                        <button wire:click="$set('showCancelConfirm', false)"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            No
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </form>
</div>
