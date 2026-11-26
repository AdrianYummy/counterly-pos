<div class="form-grid">
    <label class="field span-2">Full name<input name="full_name" value="{{ old('full_name', $customer->full_name ?? '') }}" maxlength="100" required>@error('full_name')<span class="field-error">{{ $message }}</span>@enderror</label>
    <label class="field">Email address<input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" maxlength="100" required>@error('email')<span class="field-error">{{ $message }}</span>@enderror</label>
    <label class="field">Phone <span class="hint">Optional</span><input name="phone" value="{{ old('phone', $customer->phone ?? '') }}" maxlength="20" placeholder="+63 912 345 6789">@error('phone')<span class="field-error">{{ $message }}</span>@enderror</label>
</div><div class="form-actions"><a class="button secondary" href="{{ isset($customer) ? route('customers.show', $customer) : route('customers.index') }}">Cancel</a><button class="button primary">{{ $submitLabel }}</button></div>
