@props(['active' => false])

<a
    {{ $attributes }}
    class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
           {{ $active
               ? 'bg-indigo-700 text-white'
               : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'
           }}"
>
    {{ $slot }}
</a>
