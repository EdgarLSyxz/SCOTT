@props(['disabled' => false])

@php
    $colorClass = 'focus:ring-black focus:border-black dark:focus:ring-white dark:focus:border-white';
    if (Auth::check()) {
        if (Auth::user()->area === 'DTH') {
            $colorClass = 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500';
        } elseif (Auth::user()->area === 'OTT') {
            $colorClass = 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
        }
    }
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white truncate leading-tight ' . $colorClass]) !!}>
