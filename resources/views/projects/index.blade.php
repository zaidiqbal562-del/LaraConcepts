@extends('layouts.app')

@section('title', 'Projects')

@section('content')

    @if(session('success'))
        <div style="color: green; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <h3>Project List</h3>
    <h3><a href="{{ route('projects.create') }}">+ Add Project</a></h3>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Manager</th>
                <th>Paid Status</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>

        <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>{{ $project->id }}</td>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->managerUser?->name ?? 'No Manager' }}</td>

                    <td>
                        @if($project->paid === null)
                            Pending
                        @elseif($project->paid)
                            Paid
                        @else
                            Unpaid
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('projects.edit', $project) }}">
                            Edit
                        </a>
                    </td>

                    <td>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                onclick="return confirm('Are you sure you want to delete this project?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No projects found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection