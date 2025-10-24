@extends('layouts.app')

@section('title', 'Edit Care Level')

@section('content')
<div class="container mt-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Edit Care Level</strong></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('care_levels.update', $care_level) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $care_level->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optional)</label>
                            <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $care_level->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('care_levels.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-primary">Update Care Level</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <aside class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Suggested care levels</strong></div>
                <div class="card-body">
                    <p class="text-muted small">Click a suggestion to replace the current form values.</p>
                    <div class="list-group">
                        @foreach($suggestions as $sName => $sDesc)
                            <button type="button" class="list-group-item list-group-item-action suggestion-item" data-name="{{ $sName }}" data-desc="{{ $sDesc }}">
                                <div class="d-flex justify-content-between">
                                    <div><strong>{{ $sName }}</strong></div>
                                </div>
                                <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($sDesc, 160) }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.suggestion-item').forEach(function(btn){
        btn.addEventListener('click', function () {
            const name = this.getAttribute('data-name') || '';
            const desc = this.getAttribute('data-desc') || '';
            const nameInput = document.getElementById('name');
            const descInput = document.getElementById('description');

            if (nameInput) nameInput.value = name;
            if (descInput) descInput.value = desc;

            this.classList.add('active');
            setTimeout(() => this.classList.remove('active'), 600);
        });
    });
});
</script>
@endpush
