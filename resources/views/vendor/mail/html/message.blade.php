<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <tr>
            <td class="header">
                <a href="{{ config('app.url') }}" style="display: inline-block;" aria-label="{{ config('app.name') }}">
                    <x-app-logo class="logo" />
                </a>
            </td>
        </tr>
    </x-slot:header>

    {{-- Body --}}
    {!! $slot !!}

    {{-- Subcopy --}}
    @isset($subcopy)
    <x-slot:subcopy>
        <x-mail::subcopy>
            {!! $subcopy !!}
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
