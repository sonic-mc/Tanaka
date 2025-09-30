@extends('layouts.app')

@section('title', 'Admit Patient')
@section('page-title', 'Admit New Patient')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admissions.store') }}" method="POST">
            @csrf
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Date of Birth</label>
                    <input type="date" name="dob" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Admission Date</label>
                    <input type="date" name="admission_date" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Admission Reason</label>
                    <textarea name="admission_reason" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Assign Nurse</label>
                    <select name="assigned_nurse_id" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($nurses as $nurse)
                            <option value="{{ $nurse->id }}">{{ $nurse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Room Number</label>
                    <input type="text" name="room_number" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Care Level</label>
                    <select name="current_care_level_id" class="form-select">
                        <option value="">-- Select --</option>
                        @foreach($careLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- ✅ Next of Kin Details --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Next of Kin Name</label>
                    <input type="text" name="next_of_kin_name" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Relationship</label>
                    <select name="next_of_kin_relationship" class="form-select">
                        <option value="">-- Select Relationship --</option>
                        <option value="spouse">Spouse</option>
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="sibling">Sibling</option>
                        <option value="guardian">Guardian</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Next of Kin Contact Number</label>
                    <input type="text" name="next_of_kin_contact_number" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Next of Kin Email</label>
                    <input type="email" name="next_of_kin_email" class="form-control">
                </div>

            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Admit Patient</button>
            </div>
        </form>
    </div>
</div>
@endsection
