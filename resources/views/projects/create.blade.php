@extends('layouts.app')

@section('title', 'Create Project')
@section('content')

    <div>
        <h3>Create New Project</h3>

        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            <div>
                <label for="name">Project Name:</label>
                <input type="text" id="name" name="name" required>
            </div>  

            
            <div>
                <label for="manager">Manager:</label>
                <select id="manager" name="manager" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name ?? 'No name' }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="paid">Paid Status:</label>
                <select id="paid" name="paid" required>
                    <option value="">Select Status</option> 
                    <option value="1">Paid</option>
                    <option value="0">Unpaid</option>
                </select>
            <div>
                <button type="submit">Create Project</button>
            </div>
        </form>
    </div>
@endsection