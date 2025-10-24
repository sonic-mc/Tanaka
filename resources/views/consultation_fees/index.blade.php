@extends('layouts.app')

@section('title', 'Consultation Fees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Consultation Fees</h1>
    <a href="{{ route('consultation_fees.create') }}" class="btn btn-primary">Add Fee</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="visually-hidden" for="age_group">Age Group</label>
                <select name="age_group" id="age_group" class="form-select">
                    <option value="">All age groups</option>
                    <option value="child" {{ request('age_group') === 'child' ? 'selected' : '' }}>Child</option>
                    <option value="adult" {{ request('age_group') === 'adult' ? 'selected' : '' }}>Adult</option>
                </select>
            </div>

            <div class="col-auto">
                <label class="visually-hidden" for="sort">Sort</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Newest</option>
                    <option value="fee_amount" {{ request('sort') === 'fee_amount' ? 'selected' : '' }}>Fee Amount</option>
                    <option value="age_group" {{ request('sort') === 'age_group' ? 'selected' : '' }}>Age Group</option>
                </select>
            </div>

            <div class="col-auto">
                <select name="direction" class="form-select">
                    <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>Desc</option>
                    <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Asc</option>
                </select>
            </div>

            <div class="col-auto">
                <button class="btn btn-outline-secondary">Filter</button>
                <a href="{{ route('consultation_fees.index') }}" class="btn btn-link">Reset</a>
            </div>
        </form>
    </div>
</div>

@if($fees->isEmpty())
    <div class="alert alert-info">No consultation fees found. <a href="{{ route('consultation_fees.create') }}">Create one</a>.</div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Age Group</th>
                    <th class="text-end">Fee Amount</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fees as $fee)
                    <tr>
                        <td>
                            @if($fee->age_group === 'child')
                                <span class="badge bg-info text-dark">Child</span>
                            @else
                                <span class="badge bg-secondary">Adult</span>
                            @endif
                        </td>
                        <td class="text-end">${{ number_format($fee->fee_amount, 2) }}</td>
                        <td style="min-width: 300px;">{{ \Illuminate\Support\Str::limit($fee->description, 80) }}</td>
                        <td class="text-end">
                            <a href="{{ route('consultation_fees.show', $fee) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('consultation_fees.edit', $fee) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <button
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-id="{{ $fee->id }}"
                                data-name="{{ ucfirst($fee->age_group) . ' - $' . number_format($fee->fee_amount, 2) }}"
                                >
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            Showing {{ $fees->firstItem() }} - {{ $fees->lastItem() }} of {{ $fees->total() }}
        </div>
        <div>
            {{ $fees->links() }}
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Fee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p id="deleteModalBody">Are you sure you want to delete this fee?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const form = document.getElementById('deleteForm');

        form.action = '{{ url()->current() }}/' + id;
        document.getElementById('deleteModalBody').textContent = 'Are you sure you want to delete "' + name + '"? This action cannot be undone.';
    });
});
</script>
@endpush
