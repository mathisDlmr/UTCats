<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <p class="mt-2 text-xl font-bold text-white-500 dark:text-white-400">
                    Panel Admin
                </p>
            </div>

            <div class="flex flex-col items-end gap-y-1">
                <x-filament::link
                    color="gray"
                    href="{{ url('/utcats') }}"
                    icon="heroicon-m-user-group"
                    rel="noopener noreferrer"
                >
                    Panel Public
                </x-filament::link>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>