@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <h3>Edit Project</h3>

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="name">Project Name:</label>
            <input type="text" id="name" name="name" value="{{ old('name', $project->name) }}" required>
        </div>

        <div>
            <label for="manager">Manager:</label>
            <select id="manager" name="manager" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $user->id == old('manager', $project->manager) ? 'selected' : '' }}>{{ $user->name ?? $user->email }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="paid">Paid Status:</label>
            <select id="paid" name="paid" required>
                <option value="">Select Status</option>
                <option value="1" {{ old('paid', $project->paid) ? 'selected' : '' }}>Paid</option>
                <option value="0" {{ !old('paid', $project->paid) && $project->paid !== null ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        <div>
            <button type="submit">Update Project</button>
        </div>
    </form>
@endsection