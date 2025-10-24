<div class="mb-3">
    <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
    <select name="age_group" id="age_group" class="form-select @error('age_group') is-invalid @enderror" required>
        <option value="">Select an age group</option>
        <option value="child" {{ old('age_group', $consultation_fee->age_group ?? '') === 'child' ? 'selected' : '' }}>Child</option>
        <option value="adult" {{ old('age_group', $consultation_fee->age_group ?? '') === 'adult' ? 'selected' : '' }}>Adult</option>
    </select>
    @error('age_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="fee_amount" class="form-label">Fee Amount <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text">$</span>
        <input
            type="number"
            step="0.01"
            min="0"
            name="fee_amount"
            id="fee_amount"
            class="form-control @error('fee_amount') is-invalid @enderror"
            value="{{ old('fee_amount', isset($consultation_fee) ? number_format($consultation_fee->fee_amount, 2, '.', '') : '') }}"
            required
        >
        @error('fee_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="form-text">Use decimal format, e.g., 150.00</div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $consultation_fee->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-success">{{ $buttonText ?? 'Save' }}</button>
    <a href="{{ route('consultation_fees.index') }}" class="btn btn-secondary">Cancel</a>
</div>
