@extends('admin/include.layout') 

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Packages</h2>
        <a href="{{ route('packages.create') }}" class="btn btn-primary">+ Create Package</a>
    </div>

    {{-- Flash Message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Package Table --}}
    <div class="card shadow">
        <div class="card-body p-0">
            @if ($packages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Package Name</th>
                                <th>Created BY</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                <tr>
                                    <td>{{ $package->id }}</td>
                                    <td>{{ $package->packageName }}</td>
                                    <td>{{ $package->created_by }}</td>
                                    <td>
                                        @if ($package->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                  <td>{{ \Carbon\Carbon::parse($package->created_at)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($package->updated_at)->format('d M Y') }}</td>

                                    <td class="text-end">
                                        <a href="{{ route('commission-list', $package->id) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        {{-- <form action="{{ route('packages.destroy', $package->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this package?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Delete</button>
                                        </form> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-3">
                    <p class="text-center text-muted">No packages found. Click "Create Package" to add one.</p>
                </div>
            @endif
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body">
                @if(session('success'))
                    <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png" alt="Success" width="80">
                    <h5 class="mt-2 text-success">{{ session('success') }}</h5>
                @elseif(session('error'))
                    <img src="https://media.istockphoto.com/id/1904567040/vector/red-cross-wrong-icon.jpg?s=612x612&w=0&k=20&c=gmkYwAWonQIwrqd1J4C-z2eV11CRyvpr5XyspjaG2KQ=" alt="Failed" width="80">
                    <h5 class="mt-2 text-dark">{{ session('error') }}</h5>
                @endif
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success') || session('error'))
            var modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        @endif
    });

@endsection
