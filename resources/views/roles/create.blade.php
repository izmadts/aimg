@extends('layouts.app')

@section('title', 'Add Role')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Role</h1>
            <p class="text-sm text-gray-500">Create a new role and pick what it can access</p>
        </div>
        <a href="{{ route('roles.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        @include('roles.partials.form', ['assignedPermissionIds' => []])
    </form>
</div>
@endsection
