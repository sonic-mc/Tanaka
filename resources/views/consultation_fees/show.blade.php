@extends('layouts.app')

@section('title', 'Fee Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fee Details</h5>
        <div>
            <a href="{{ route('consultation_fees.edit', $consultation_fee) }}" class="btn btn-sm btn-outline-warning">Edit</a>
            <a href="{{ route('consultation_fees.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Age Group</dt>
            <dd class="col-sm-9">{{ ucfirst($consultation_fee->age_group) }}</dd>

            <dt class="col-sm-3">Fee Amount</dt>
            <dd class="col-sm-9">${{ number_format($consultation_fee->fee_amount, 2) }}</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{!! nl2br(e($consultation_fee->description ?: '—')) !!}</dd>

            <dt class="col-sm-3">Created At</dt>
            <dd class="col-sm-9">{{ $consultation_fee->created_at->format('Y-m-d H:i') }}</dd>

            <dt class="col-sm-3">Last Updated</dt>
            <dd class="col-sm-9">{{ $consultation_fee->updated_at->format('Y-m-d H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
