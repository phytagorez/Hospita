@extends('layouts.app')

@section('head')
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    <x-inertia::head>
        <title>{{ config('app.name', 'Hospia') }}</title>
    </x-inertia::head>
@endsection

@section('content')
    <x-inertia::app />
@endsection
