@extends('layouts.app')

@section('title', 'Reset Employee Password')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
      <h2 class="mt-2 text-center text-2xl font-bold text-gray-900">Reset Password</h2>
      <form class="mt-6 space-y-4" method="POST" action="{{ route('employees.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
          <label class="block text-sm font-medium text-gray-700">Email address</label>
          <input type="email" name="email" value="{{ old('email', $email) }}" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
          @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">New Password</label>
          <input type="password" name="password" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
          @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
          <input type="password" name="password_confirmation" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
          <button type="submit" class="w-full bg-cannabis-green text-white py-2 rounded-md hover:bg-green-700">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
