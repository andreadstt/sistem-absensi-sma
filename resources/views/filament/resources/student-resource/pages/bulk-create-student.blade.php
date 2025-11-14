<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="flex gap-3 mt-6">
            <x-filament::button type="submit" color="primary">
                Simpan Semua
            </x-filament::button>

            <x-filament::button tag="a" :href="route('filament.admin.resources.students.index')" color="gray">
                Batal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
