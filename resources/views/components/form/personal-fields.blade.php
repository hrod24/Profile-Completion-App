@props([
    'user' => null,
    'isEmployeeRequired' => false,
    'existingDocuments' => [],
    'mode' => 'employee',
])

<div class="space-y-5">

    {{-- ========================================================= --}}
    {{-- IDENTITY --}}
    {{-- ========================================================= --}}
    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="1"
            data-active="true" @endif
        aria-labelledby="identity-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75
                           3.75 0 017.5 0zM4.5 20.1a7.5 7.5
                           0 0115 0A17.9 17.9 0 0112
                           21.75a17.9 17.9 0 01-7.5-1.65z" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 1
                    </p>
                @endif

                <h2 id="identity-title" class="mt-1 text-lg font-bold text-slate-900">
                    Employee Identity
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter official employee identity information.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="employee_id" class="kanmo-label">
                        Employee ID
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="text" id="employee_id" @if ($mode === 'employee') name="employee_id" @endif
                        value="{{ old('employee_id', $user->employee_id ?? '') }}" maxlength="20" readonly
                        class="kanmo-input bg-slate-100">

                    <p class="kanmo-help">
                        Employee ID cannot be changed from this form.
                    </p>
                </div>

                <div>
                    <label for="ktp_number" class="kanmo-label">
                        KTP Number
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="text" id="ktp_number" name="ktp_number"
                        value="{{ old('ktp_number', $user->ktp_number ?? '') }}" maxlength="16" inputmode="numeric"
                        required autocomplete="off"
                        class="kanmo-input
                            {{ $errors->has('ktp_number') ? 'kanmo-input-error' : '' }}"
                        placeholder="Enter your 16-digit NIK">

                    <p class="kanmo-help">
                        Make sure the number matches the valid KTP.
                    </p>

                    @error('ktp_number')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- PERSONAL PROFILE --}}
    {{-- ========================================================= --}}
    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="2"
            data-active="false" @endif
        aria-labelledby="profile-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875
                           1.875 0 112.652 2.652L10.582
                           16.07a4.5 4.5 0 01-1.897
                           1.13L6 18l.8-2.685a4.5
                           4.5 0 011.13-1.897l8.932-8.931z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125V18.75A2.25 2.25
                           0 0117.25 21H5.25A2.25
                           2.25 0 013 18.75V6.75A2.25
                           2.25 0 015.25 4.5h11.625" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 2
                    </p>
                @endif

                <h2 id="profile-title" class="mt-1 text-lg font-bold text-slate-900">
                    Personal Profile
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Complete personal information based on official documents.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                <div>
                    <label for="display_name" class="kanmo-label">
                        Full Name
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="text" id="display_name" name="display_name"
                        value="{{ old('display_name', $user->display_name ?? '') }}" maxlength="100" required
                        autocomplete="name"
                        class="kanmo-input uppercase
                            {{ $errors->has('display_name') ? 'kanmo-input-error' : '' }}"
                        placeholder="Display Name">

                    @error('display_name')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="gender" class="kanmo-label">
                        Gender
                        <span class="kanmo-required">*</span>
                    </label>

                    <select id="gender" name="gender" required
                        class="kanmo-select
                            {{ $errors->has('gender') ? 'kanmo-input-error' : '' }}">
                        <option value="">
                            Select gender
                        </option>

                        <option value="Male" @selected(old('gender', $user->gender ?? '') === 'Male')>
                            Male
                        </option>

                        <option value="Female" @selected(old('gender', $user->gender ?? '') === 'Female')>
                            Female
                        </option>
                    </select>

                    @error('gender')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="birth_place" class="kanmo-label">
                        Place of Birth
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="text" id="birth_place" name="birth_place"
                        value="{{ old('birth_place', $user->birth_place ?? '') }}" maxlength="100" required
                        class="kanmo-input uppercase
                            {{ $errors->has('birth_place') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: Jakarta">

                    @error('birth_place')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="date_of_birth" class="kanmo-label">
                        Date of Birth
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="date" id="date_of_birth" name="date_of_birth"
                        value="{{ old(
                            'date_of_birth',
                            $user && $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '',
                        ) }}"
                        required
                        class="kanmo-input
                            {{ $errors->has('date_of_birth') ? 'kanmo-input-error' : '' }}">

                    @error('date_of_birth')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="religion" class="kanmo-label">
                        Religion
                        <span class="kanmo-required">*</span>
                    </label>

                    <select id="religion" name="religion" required
                        class="kanmo-select
                            {{ $errors->has('religion') ? 'kanmo-input-error' : '' }}">
                        <option value="">
                            Select religion
                        </option>

                        @foreach (['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Catholicism', 'Sikhism', 'Other'] as $religion)
                            <option value="{{ $religion }}" @selected(old('religion', $user->religion ?? '') === $religion)>
                                {{ $religion === 'Other' ? 'Other / Lainnya' : $religion }}
                            </option>
                        @endforeach
                    </select>

                    @error('religion')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="marital_status" class="kanmo-label">
                        Marital Status
                        <span class="kanmo-required">*</span>
                    </label>

                    <select id="marital_status" name="marital_status" required
                        class="kanmo-select
                            {{ $errors->has('marital_status') ? 'kanmo-input-error' : '' }}">
                        <option value="">
                            Select status
                        </option>

                        @foreach (['Single', 'Married', 'Divorced', 'Widowed'] as $status)
                            <option value="{{ $status }}" @selected(old('marital_status', $user->marital_status ?? '') === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>

                    @error('marital_status')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="blood_group" class="kanmo-label">
                        Blood Group

                        @if ($isEmployeeRequired('blood_group'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <select id="blood_group" name="blood_group" @required($isEmployeeRequired('blood_group'))
                        class="kanmo-select
                            {{ $errors->has('blood_group') ? 'kanmo-input-error' : '' }}">
                        <option value="">
                            Select blood group
                        </option>

                        @foreach (['A+', 'B+', 'AB+', 'O+', 'A-', 'AB-', 'B-', 'O-'] as $bloodGroup)
                            <option value="{{ $bloodGroup }}" @selected(old('blood_group', $user->blood_group ?? '') === $bloodGroup)>
                                {{ $bloodGroup }}
                            </option>
                        @endforeach
                    </select>

                    @error('blood_group')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="nationality" class="kanmo-label">
                        Nationality
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="text" id="nationality" name="nationality"
                        value="{{ old('nationality', $user->nationality ?? 'Indonesia') }}" maxlength="50" required
                        class="kanmo-input uppercase
                            {{ $errors->has('nationality') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: Indonesia">

                    @error('nationality')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- CONTACT & ADDRESS --}}
    {{-- ========================================================= --}}
    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="3"
            data-active="false" @endif
        aria-labelledby="contact-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25
                           0 01-2.25 2.25h-15a2.25
                           2.25 0 01-2.25-2.25V6.75m19.5
                           0A2.25 2.25 0 0019.5
                           4.5h-15a2.25 2.25 0 00-2.25
                           2.25m19.5 0v.243a2.25 2.25
                           0 01-1.07 1.916l-7.5
                           4.615a2.25 2.25 0 01-2.36
                           0l-7.5-4.615A2.25 2.25
                           0 012.25 6.993V6.75" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 3
                    </p>
                @endif

                <h2 id="contact-title" class="mt-1 text-lg font-bold text-slate-900">
                    Contact & Address
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Use active contact and residential information.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="primary_email" class="kanmo-label">
                        Primary Email
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="email" id="primary_email" name="primary_email"
                        value="{{ old('primary_email', $user->primary_email ?? '') }}" maxlength="191" required
                        autocomplete="email"
                        class="kanmo-input
                            {{ $errors->has('primary_email') ? 'kanmo-input-error' : '' }}"
                        placeholder="name@email.com">

                    @error('primary_email')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="primary_contact_number" class="kanmo-label">
                        Primary Contact Number
                        <span class="kanmo-required">*</span>
                    </label>

                    <input type="tel" id="primary_contact_number" name="primary_contact_number"
                        value="{{ old('primary_contact_number', $user->primary_contact_number ?? '') }}"
                        maxlength="30" inputmode="tel" required autocomplete="tel"
                        class="kanmo-input
                            {{ $errors->has('primary_contact_number') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: 081234567890">

                    @error('primary_contact_number')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="emergency_full_name" class="kanmo-label">
                        Emergency Contact Name

                        @if ($isEmployeeRequired('emergency_full_name'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="emergency_full_name" name="emergency_full_name"
                        value="{{ old('emergency_full_name', $user->emergency_full_name ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('emergency_full_name'))
                        class="kanmo-input uppercase
                            {{ $errors->has('emergency_full_name') ? 'kanmo-input-error' : '' }}"
                        placeholder="Full name">

                    @error('emergency_full_name')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="emergency_contact_no" class="kanmo-label">
                        Emergency Contact Number

                        @if ($isEmployeeRequired('emergency_contact_no'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="tel" id="emergency_contact_no" name="emergency_contact_no"
                        value="{{ old('emergency_contact_no', $user->emergency_contact_no ?? '') }}" maxlength="30"
                        inputmode="tel" @required($isEmployeeRequired('emergency_contact_no'))
                        class="kanmo-input
                            {{ $errors->has('emergency_contact_no') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: 081234567890">

                    @error('emergency_contact_no')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div class="md:col-span-2">
                    <label for="current_address" class="kanmo-label">
                        Current Address
                        <span class="kanmo-required">*</span>
                    </label>

                    <textarea id="current_address" name="current_address" rows="3" required autocomplete="street-address"
                        class="kanmo-textarea uppercase
                            {{ $errors->has('current_address') ? 'kanmo-input-error' : '' }}"
                        placeholder="Street, house number, RT/RW, village, district">{{ old('current_address', $user->current_address ?? '') }}</textarea>

                    @error('current_address')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="current_provinsi" class="kanmo-label">
                        Current Province

                        @if ($isEmployeeRequired('current_provinsi'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="current_provinsi" name="current_provinsi"
                        value="{{ old('current_provinsi', $user->current_provinsi ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('current_provinsi'))
                        class="kanmo-input uppercase
                            {{ $errors->has('current_provinsi') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: DKI Jakarta">

                    @error('current_provinsi')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="current_kotamadya_kabupaten" class="kanmo-label">
                        City / Regency

                        @if ($isEmployeeRequired('current_kotamadya_kabupaten'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="current_kotamadya_kabupaten" name="current_kotamadya_kabupaten"
                        value="{{ old('current_kotamadya_kabupaten', $user->current_kotamadya_kabupaten ?? '') }}"
                        maxlength="100" @required($isEmployeeRequired('current_kotamadya_kabupaten'))
                        class="kanmo-input uppercase
                            {{ $errors->has('current_kotamadya_kabupaten') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: Jakarta Pusat">

                    @error('current_kotamadya_kabupaten')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="current_kecamatan" class="kanmo-label">
                        Current Kecamatan

                        @if ($isEmployeeRequired('current_kecamatan'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="current_kecamatan" name="current_kecamatan"
                        value="{{ old('current_kecamatan', $user->current_kecamatan ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('current_kecamatan'))
                        class="kanmo-input uppercase
                            {{ $errors->has('current_kecamatan') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: MATRAMAN">

                    @error('current_kecamatan')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="current_kelurahan" class="kanmo-label">
                        Current Kelurahan

                        @if ($isEmployeeRequired('current_kelurahan'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="current_kelurahan" name="current_kelurahan"
                        value="{{ old('current_kelurahan', $user->current_kelurahan ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('current_kelurahan'))
                        class="kanmo-input uppercase
                            {{ $errors->has('current_kelurahan') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: PAL MERIAM">

                    @error('current_kelurahan')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="current_postal_code" class="kanmo-label">
                        Current Postal Code

                        @if ($isEmployeeRequired('current_postal_code'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="current_postal_code" name="current_postal_code"
                        value="{{ old('current_postal_code', $user->current_postal_code ?? '') }}" maxlength="5"
                        inputmode="numeric" @required($isEmployeeRequired('current_postal_code'))
                        class="kanmo-input
                            {{ $errors->has('current_postal_code') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: 13140">

                    @error('current_postal_code')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div class="md:col-span-2">
                    <label
                        class="flex items-start gap-3 p-3 mb-3 transition border cursor-pointer rounded-xl border-stone-200 bg-stone-50 hover:border-kanmo-200 hover:bg-kanmo-50/40">
                        <input type="checkbox"
                            class="mt-0.5 h-4 w-4 rounded
                                   border-stone-300
                                   text-kanmo-500
                                   focus:ring-kanmo-300"
                            data-copy-address>

                        <span>
                            <span class="block text-sm font-semibold text-slate-700">
                                KTP address is the same as current address
                            </span>

                            <span class="mt-0.5 block text-xs
                                       text-slate-500">
                                Check to copy the address automatically.
                            </span>
                        </span>
                    </label>

                    <label for="ktp_address" class="kanmo-label">
                        KTP Address

                        @if ($isEmployeeRequired('ktp_address'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <textarea id="ktp_address" name="ktp_address" rows="3" @required($isEmployeeRequired('ktp_address'))
                        class="kanmo-textarea uppercase
                            {{ $errors->has('ktp_address') ? 'kanmo-input-error' : '' }}"
                        placeholder="Enter KTP address">{{ old('ktp_address', $user->ktp_address ?? '') }}</textarea>

                    @error('ktp_address')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="ktp_provinsi" class="kanmo-label">
                        KTP Province

                        @if ($isEmployeeRequired('ktp_provinsi'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="ktp_provinsi" name="ktp_provinsi"
                        value="{{ old('ktp_provinsi', $user->ktp_provinsi ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('ktp_provinsi'))
                        class="kanmo-input uppercase
                            {{ $errors->has('ktp_provinsi') ? 'kanmo-input-error' : '' }}">

                    @error('ktp_provinsi')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="ktp_kotamadya_kabupaten" class="kanmo-label">
                        KTP City / Regency

                        @if ($isEmployeeRequired('ktp_kotamadya_kabupaten'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="ktp_kotamadya_kabupaten" name="ktp_kotamadya_kabupaten"
                        value="{{ old('ktp_kotamadya_kabupaten', $user->ktp_kotamadya_kabupaten ?? '') }}"
                        maxlength="100" @required($isEmployeeRequired('ktp_kotamadya_kabupaten'))
                        class="kanmo-input uppercase
                            {{ $errors->has('ktp_kotamadya_kabupaten') ? 'kanmo-input-error' : '' }}">

                    @error('ktp_kotamadya_kabupaten')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="ktp_kecamatan" class="kanmo-label">
                        KTP Kecamatan

                        @if ($isEmployeeRequired('ktp_kecamatan'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="ktp_kecamatan" name="ktp_kecamatan"
                        value="{{ old('ktp_kecamatan', $user->ktp_kecamatan ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('ktp_kecamatan'))
                        class="kanmo-input uppercase
                            {{ $errors->has('ktp_kecamatan') ? 'kanmo-input-error' : '' }}">

                    @error('ktp_kecamatan')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="ktp_kelurahan" class="kanmo-label">
                        KTP Kelurahan

                        @if ($isEmployeeRequired('ktp_kelurahan'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="ktp_kelurahan" name="ktp_kelurahan"
                        value="{{ old('ktp_kelurahan', $user->ktp_kelurahan ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('ktp_kelurahan'))
                        class="kanmo-input uppercase
                            {{ $errors->has('ktp_kelurahan') ? 'kanmo-input-error' : '' }}">

                    @error('ktp_kelurahan')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- FAMILY & EDUCATION --}}
    {{-- ========================================================= --}}
    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="4"
            data-active="false" @endif
        aria-labelledby="education-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25L4.5 10.5
                           12 6.75l7.5 3.75L12 14.25z" />

                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 11.625v4.125c0
                           1.243 2.35 2.25 5.25
                           2.25s5.25-1.007
                           5.25-2.25v-4.125M19.5
                           10.5v5.25" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 4
                    </p>
                @endif

                <h2 id="education-title" class="mt-1 text-lg font-bold text-slate-900">
                    Family & Education
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Latest family and education information.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="mother_full_name" class="kanmo-label">
                        Mother’s Full Name

                        @if ($isEmployeeRequired('mother_full_name'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="mother_full_name" name="mother_full_name"
                        value="{{ old('mother_full_name', $user->mother_full_name ?? '') }}" maxlength="100"
                        @required($isEmployeeRequired('mother_full_name'))
                        class="kanmo-input uppercase
                            {{ $errors->has('mother_full_name') ? 'kanmo-input-error' : '' }}"
                        placeholder="Mother’s Full Name">

                    @error('mother_full_name')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="education_level" class="kanmo-label">
                        Education Level

                        @if ($isEmployeeRequired('education_level'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <select id="education_level" name="education_level" @required($isEmployeeRequired('education_level'))
                        class="kanmo-select
                            {{ $errors->has('education_level') ? 'kanmo-input-error' : '' }}">
                        <option value="">
                            Select education level
                        </option>

                        @foreach (['SMA', 'SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $level)
                            <option value="{{ $level }}" @selected(old('education_level', $user->education_level ?? '') === $level)>
                                {{ $level }}
                            </option>
                        @endforeach
                    </select>

                    @error('education_level')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="major" class="kanmo-label">
                        Major

                        @if ($isEmployeeRequired('major'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="major" name="major"
                        value="{{ old('major', $user->major ?? '') }}" maxlength="100" @required($isEmployeeRequired('major'))
                        class="kanmo-input uppercase
                            {{ $errors->has('major') ? 'kanmo-input-error' : '' }}"
                        placeholder="Example: Management">

                    @error('major')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div>
                    <label for="institution_name" class="kanmo-label">
                        Institution Name

                        @if ($isEmployeeRequired('institution_name'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="institution_name" name="institution_name"
                        value="{{ old('institution_name', $user->institution_name ?? '') }}" maxlength="150"
                        @required($isEmployeeRequired('institution_name'))
                        class="kanmo-input uppercase
                            {{ $errors->has('institution_name') ? 'kanmo-input-error' : '' }}"
                        placeholder="School or university name">

                    @error('institution_name')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div class="flex gap-5 md:col-span-2">

                    <div class="w-full">
                        <label for="education_from" class="kanmo-label">
                            Education From

                            @if ($isEmployeeRequired('education_from'))
                                <span class="kanmo-required">*</span>
                            @endif
                        </label>

                        <input type="number" min="1800" max="2100" id="education_from"
                            name="education_from" value="{{ old('education_from', $user->education_from ?? '') }}"
                            @required($isEmployeeRequired('education_from'))
                            class="kanmo-input
                                {{ $errors->has('education_from') ? 'kanmo-input-error' : '' }}"
                            placeholder="Education Start Year">

                        @error('education_from')
                            <p class="kanmo-error">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div class="w-full">
                        <label for="education_end" class="kanmo-label">
                            Education End

                            @if ($isEmployeeRequired('education_end'))
                                <span class="kanmo-required">*</span>
                            @endif
                        </label>

                        <input type="number" min="1800" max="2100" id="education_end" name="education_end"
                            value="{{ old('education_end', $user->education_end ?? '') }}" @required($isEmployeeRequired('education_end'))
                            class="kanmo-input
                                {{ $errors->has('education_end') ? 'kanmo-input-error' : '' }}"
                            placeholder="Education End Year">

                        @error('education_end')
                            <p class="kanmo-error">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- TAX --}}
    {{-- ========================================================= --}}
    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="5"
            data-active="false" @endif
        aria-labelledby="tax-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15
                           9.75m-3-7.036A11.96 11.96
                           0 013.598 6 11.99 11.99
                           0 003 9.75c0 5.592
                           3.824 10.29 9 11.623
                           5.176-1.332 9-6.03
                           9-11.622 0-1.31-.21-2.57
                           -.598-3.751A11.96 11.96
                           0 0112 2.714z" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 5
                    </p>
                @endif

                <h2 id="tax-title" class="mt-1 text-lg font-bold text-slate-900">
                    Tax
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Complete tax information.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="tax_number" class="kanmo-label">
                        Tax Number

                        @if ($isEmployeeRequired('tax_number'))
                            <span class="kanmo-required">*</span>
                        @endif
                    </label>

                    <input type="text" id="tax_number" name="tax_number"
                        value="{{ old('tax_number', $user->tax_number ?? '') }}" maxlength="30" inputmode="numeric"
                        autocomplete="off" @required($isEmployeeRequired('tax_number'))
                        class="kanmo-input
                            {{ $errors->has('tax_number') ? 'kanmo-input-error' : '' }}"
                        placeholder="Enter NPWP number">

                    @error('tax_number')
                        <p class="kanmo-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- DOCUMENT ATTACHMENT --}}
    {{-- ========================================================= --}}
    @php
        $documents = [
            [
                'field' => 'ijazah_filename',

                'type' => 'ijazah',

                'label' => 'Ijazah',
            ],

            [
                'field' => 'ktp_filename',

                'type' => 'ktp',

                'label' => 'KTP',
            ],

            [
                'field' => 'kk_filename',

                'type' => 'kk',

                'label' => 'KK',
            ],

            [
                'field' => 'npwp_filename',

                'type' => 'npwp',

                'label' => 'NPWP',
            ],
        ];
    @endphp

    <section class="kanmo-form-section scroll-mt-6"
        @if ($mode === 'employee') data-form-step
            data-step="6"
            data-active="false" @endif
        aria-labelledby="attachment-title">
        <div class="kanmo-form-section-header">
            <div class="kanmo-section-icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15
                           9.75m-3-7.036A11.96 11.96
                           0 013.598 6 11.99 11.99
                           0 003 9.75c0 5.592
                           3.824 10.29 9 11.623
                           5.176-1.332 9-6.03
                           9-11.622 0-1.31-.21-2.57
                           -.598-3.751A11.96 11.96
                           0 0112 2.714z" />
                </svg>
            </div>

            <div>
                @if ($mode === 'employee')
                    <p class="text-xs font-bold tracking-wider uppercase text-kanmo-600">
                        Step 6
                    </p>
                @endif

                <h2 id="attachment-title" class="mt-1 text-lg font-bold text-slate-900">
                    Document Attachment
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Upload or replace employee documents.
                </p>
            </div>
        </div>

        <div class="kanmo-form-section-body">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach ($documents as $document)
                    @php
                        $field = $document['field'];

                        $type = $document['type'];

                        $label = $document['label'];

                        $path = $user?->{$field};

                        $hasExistingDocument = ($existingDocuments[$field] ?? false) && filled($path);

                        $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : '';

                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);

                        $isPdf = $extension === 'pdf';

                        $documentUrl = $hasExistingDocument
                            ? ($mode === 'employee'
                                ? route('employee.form.document', ['type' => $type])
                                : route('hr-form.document', [
                                    'employeeId' => $user->employee_id,
                                    'type' => $type,
                                ]))
                            : null;
                    @endphp


                    <div>
                        <label for="{{ $field }}" class="kanmo-label">
                            {{ $label }}

                            @if ($isEmployeeRequired($field))
                                <span class="kanmo-required">
                                    *
                                </span>
                            @endif

                            <p
                                class="text-[12px]
                           font-light italic
                           text-gray-600">
                                pdf, jpg, jpeg, png
                            </p>
                        </label>


                        <input type="file" id="{{ $field }}" name="{{ $field }}"
                            class="filepond" @required($isEmployeeRequired($field) && !$hasExistingDocument)
                            data-existing-file="{{ $hasExistingDocument ? 'true' : 'false' }}"
                            data-existing-file-name="{{ $path ?? '' }}">


                        @if ($hasExistingDocument)
                            <div
                                class="flex items-center justify-between gap-4 px-3 py-3 mt-6 border rounded-xl border-emerald-200 bg-emerald-50">
                                <div class="flex items-center min-w-0 gap-3">
                                    <div
                                        class="flex items-center justify-center rounded-lg h-9 w-9 shrink-0 bg-emerald-100 text-emerald-600">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>


                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-emerald-700">
                                            {{ $label }}
                                            already saved, upload the newest {{ $label }} to update the
                                            information
                                        </p>

                                        <p class="mt-0.5
                                       truncate
                                       text-xs
                                       text-emerald-600"
                                            title="{{ basename($path) }}">
                                            {{ basename($path) }}
                                        </p>
                                    </div>
                                </div>


                                @if ($documentUrl)
                                    {{-- IMAGE --}}
                                    @if ($isImage)
                                        <button type="button"
                                            class="inline-flex
                                       shrink-0
                                       cursor-pointer
                                       items-center
                                       justify-center
                                       gap-1.5
                                       rounded
                                       border
                                       border-slate-300
                                       bg-white
                                       px-3 py-2
                                       text-xs
                                       font-bold
                                       text-slate-700
                                       shadow-sm
                                       transition
                                       hover:border-orange-300
                                       hover:bg-orange-50
                                       hover:text-orange-700"
                                            data-document-preview data-document-url="{{ $documentUrl }}"
                                            data-document-title="{{ $label }}">
                                            View
                                        </button>


                                        {{-- PDF --}}
                                    @elseif ($isPdf)
                                        <a href="{{ $documentUrl }}"
                                            class="inline-flex
                                       shrink-0
                                       items-center
                                       justify-center
                                       gap-1.5
                                       rounded
                                       border
                                       border-slate-300
                                       bg-white
                                       px-3 py-2
                                       text-xs
                                       font-bold
                                       text-slate-700
                                       shadow-sm
                                       transition
                                       hover:border-orange-300
                                       hover:bg-orange-50
                                       hover:text-orange-700">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0
                                           0l-4-4m4
                                           4l4-4M5
                                           19h14" />
                                            </svg>

                                            Download
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @endif


                        @error($field)
                            <p class="kanmo-error">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="p-4 mt-6 border rounded-2xl border-kanmo-100 bg-gradient-to-r from-kanmo-50 to-orange-50/40">
                <div class="flex items-start gap-3">
                    <div
                        class="flex items-center justify-center bg-white rounded-lg shadow-sm h-9 w-9 shrink-0 text-kanmo-600 ring-1 ring-kanmo-100">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-kanmo-900">
                            Please Confirm Employee Data
                        </p>

                        <p class="mt-1 text-xs leading-5 text-kanmo-900/70">
                            Review identity, contact,
                            education, tax, and document
                            information before saving.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="fixed inset-0 z-[100]
               hidden items-center
               justify-center
               bg-slate-950/70
               p-4 backdrop-blur-sm"
        data-document-modal aria-hidden="true">
        <div class="relative flex
                   max-h-[92vh]
                   w-full max-w-4xl
                   flex-col overflow-hidden
                   rounded-2xl bg-white
                   shadow-2xl"
            role="dialog" aria-modal="true" aria-labelledby="document-modal-title">

            {{-- HEADER --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <div class="min-w-0">
                    <p
                        class="text-[11px]
                               font-bold
                               uppercase
                               tracking-wider
                               text-slate-400">
                        Document Preview
                    </p>

                    <h3 id="document-modal-title" class="mt-1 text-base font-bold truncate text-slate-900"
                        data-document-modal-title>
                        Employee Document
                    </h3>
                </div>


                <button type="button"
                    class="flex items-center justify-center transition bg-white border cursor-pointer h-9 w-9 shrink-0 rounded-xl border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                    data-document-modal-close aria-label="Close document preview">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>


            {{-- BODY --}}
            <div
                class="flex min-h-[300px]
                       flex-1 items-center
                       justify-center
                       overflow-auto
                       bg-slate-100 p-4">
                <div class="flex items-center justify-center" data-document-loading>
                    <svg class="w-8 h-8 text-orange-500 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>

                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0
                               018-8V0C5.373
                               0 0 5.373 0
                               12h4zm2
                               5.291A7.962
                               7.962 0 014
                               12H0c0
                               3.042 1.135
                               5.824 3
                               7.938l3-2.647z"></path>
                    </svg>
                </div>


                <img src="" alt=""
                    class="hidden
                           max-h-[72vh]
                           max-w-full
                           rounded-xl
                           object-contain
                           shadow-lg"
                    data-document-modal-image>
            </div>

        </div>
    </div>

</div>
