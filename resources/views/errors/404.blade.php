@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Route;

    $homeUrl = url('/');
    if (Route::has('pos.index')) {
        $homeUrl = route('pos.index');
    } elseif (Route::has('products.index')) {
        $homeUrl = route('products.index');
    } elseif (Route::has('dashboard')) {
        $homeUrl = route('dashboard');
    }
@endphp

@section('title', 'Page not found')

@section('content')
<div class="max-w-xl mx-auto text-center space-y-4">
    <h1 class="text-3xl font-semibold">404 — Not Found</h1>
    <p class="text-gray-600">The page you’re looking for doesn’t exist.</p>
    <div class="space-x-2">
        <a href="{{ $homeUrl }}" class="inline-block px-4 py-2 bg-cannabis-green text-white rounded-lg">Go Home</a>
        @if (Route::has('login'))
        <a href="{{ route('login') }}" class="inline-block px-4 py-2 border rounded-lg">Sign in</a>
        @endif
    </div>
</div>
@endsection