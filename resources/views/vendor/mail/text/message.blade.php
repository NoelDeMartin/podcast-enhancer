<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        {{ config('app.name') }}: {{ config('app.url') }}
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} Noel De Martin
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
