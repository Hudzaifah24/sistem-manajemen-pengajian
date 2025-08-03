@php
  $class =
      'inline-flex items-center px-4 py-2 text-xs font-semibold text-white transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md dark:bg-gray-200 dark:text-gray-800 hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50';
@endphp

@if (!isset($attributes['href']))
  <button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>
    {{ $slot }}
  </button>
@else
  <a {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
  </a>
@endif
