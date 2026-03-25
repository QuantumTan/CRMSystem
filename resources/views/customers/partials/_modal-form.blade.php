@php $openModal = $editing || (request('edit') && $errors->any()); @endphp

<div class="modal fade {{ $openModal ? 'show' : '' }}" id="customerModal" tabindex="-1"
    style="{{ $openModal ? 'display:block;' : '' }}" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ $editing ? 'Edit Customer' : 'Add Customer' }}</h5>
                <a href="{{ route('customers.index') }}" class="btn-close"></a>
            </div>

            <form method="POST"
                action="{{ $editing ? route('customers.update', $editing->id) : route('customers.store') }}">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div class="modal-body">

                    @foreach ([['first_name', 'First Name', 'text', 'Juan', true], ['last_name', 'Last Name', 'text', 'dela Cruz', true], ['email', 'Email', 'email', 'juan@example.com', true], ['phone', 'Phone', 'text', '+63 9XX XXX XXXX', false]] as [$name, $label, $type, $placeholder, $required])
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                {{ $label }}
                                @if ($required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="{{ $type }}" name="{{ $name }}"
                                class="form-control @error($name) is-invalid @enderror"
                                value="{{ old($name, $editing?->$name) }}" placeholder="{{ $placeholder }}"
                                {{ $required ? 'required' : '' }}>
                            @error($name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Address</label>
                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                            placeholder="City, Province">{{ old('address', $editing?->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $text)
                                <option value="{{ $val }}"
                                    {{ old('status', $editing?->status) === $val ? 'selected' : '' }}>
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer flex-column flex-sm-row">
                    <a href="{{ route('customers.index') }}"
                        class="btn btn-light w-100 w-sm-auto mb-2 mb-sm-0">Cancel</a>
                    <button type="submit" class="btn btn-dark w-100 w-sm-auto m-0">
                        <i class="bi bi-save"></i>
                        {{ $editing ? 'Update Customer' : 'Save Customer' }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@if ($openModal)
    <div class="modal-backdrop fade show"></div>
    <style>
        body {
            overflow: hidden;
            padding-right: 0 !important;
        }
    </style>
@endif
