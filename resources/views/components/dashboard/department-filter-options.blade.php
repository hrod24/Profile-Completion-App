@forelse ($departments as $index => $department)
    @php
        $checkboxId =
            'department-filter-' .
            $department['value'];
    @endphp

    <li class="flex items-start gap-2">
        <input
            id="{{ $checkboxId }}"
            type="checkbox"
            value="{{ $department['value'] }}"
            data-department-filter-checkbox
            @checked(
                in_array(
                    $department['value'],
                    $selectedDepartments,
                    true
                )
            )
            class="mt-0.5 h-4 w-4 cursor-pointer rounded
                   border-gray-300 text-kanmo-600
                   focus:ring-kanmo-300"
        >

        <label
            for="{{ $checkboxId }}"
            class="cursor-pointer text-sm font-medium text-gray-900"
        >
            <span class="block">
                {{ $department['label'] }}
            </span>

            <span class="block text-xs font-normal text-slate-400">
                {{ $department['code'] }}
            </span>
        </label>
    </li>
@empty
    <li class="text-sm text-slate-500">
        Tidak ada department untuk business unit tersebut.
    </li>
@endforelse