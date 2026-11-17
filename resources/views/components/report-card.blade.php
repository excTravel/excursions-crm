@props(['label', 'value', 'color' => 'blue'])

@php
$colors = [
'blue' => 'text-blue-600',
'green' => 'text-green-600',
'red' => 'text-red-600',
'amber' => 'text-amber-500',
'purple' => 'text-purple-600',
'gray' => 'text-gray-600',
'emerald' => 'text-emerald-600',
'teal' => 'text-teal-600',
'indigo' => 'text-indigo-600',
];
@endphp

<div class="voyage-report-card p-5 bg-white rounded-xl shadow-sm border border-gray-200">
    <h3 class="text-sm font-medium text-gray-500">{{ $label }}</h3>
    <p class="mt-2 text-2xl font-bold {{ $colors[$color] ?? 'text-gray-800' }}">{{ $value }}</p>
</div>
