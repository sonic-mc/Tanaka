@extends('layouts.app')

@section('header')
    Billing Overview
@endsection

@section('content')
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Total Amount</th>
            <th>Outstanding</th>
            <th>Last Updated</th>
        </tr>
    </thead>
    <tbody>
        @foreach($statements as $statement)
        <tr>
            <td>{{ $statement->patient->first_name }} {{ $statement->patient->last_name }}</td>
            <td>${{ number_format($statement->total_amount, 2) }}</td>
            <td>${{ number_format($statement->outstanding_balance, 2) }}</td>
            <td>{{ $statement->last_updated->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
