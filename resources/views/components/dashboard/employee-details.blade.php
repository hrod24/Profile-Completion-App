@php
    $allFields = collect($groups)->flatMap(fn(array $group) => $group['fields']);

    $totalFields = $allFields->count();
    $filledFields = $allFields->where('filled', true)->count();
    $missingFields = max($totalFields - $filledFields, 0);

    $completion = $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;

    $employeeName = $employee->display_name ?: 'Name not available';

    $picName = $employee->pic->name ?? 'PIC not assigned';

    $source = $employee->sourceData->source ?? 'Source not available';

    $statusText = (int) ($employee->active ?? 0) === 1 ? 'Active' : 'Blocked';

    $statusClass =
        (int) ($employee->active ?? 0) === 1
            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
            : 'bg-rose-50 text-rose-700 ring-rose-200';

    /*
     * Folder setiap attachment pada storage/app/public.
     */
    $attachmentFolders = [
        'ijazah_filename' => 'employee-documents/ijazah',
        'ktp_filename' => 'employee-documents/ktp',
        'kk_filename' => 'employee-documents/kk',
        'npwp_filename' => 'employee-documents/npwp',
    ];

    $imageExtensions = ['jpg', 'jpeg', 'png'];
@endphp

<div class="space-y-5" data-employee-details-panel>
    {{-- Employee identity summary --}}
    <section class="overflow-hidden rounded-2xl border
               border-slate-300 bg-white">
        <div
            class="flex flex-col gap-4 border-b border-slate-300
                   bg-slate-50/80 px-5 py-5 sm:flex-row
                   sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center
                           justify-center rounded-2xl bg-orange-50
                           text-lg font-extrabold text-orange-600
                           ring-1 ring-inset ring-orange-100">
                    {{ strtoupper(substr(trim($employeeName), 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <h3 class="truncate text-base font-extrabold
                               text-slate-900">
                        {{ $employeeName }}
                    </h3>

                    <div
                        class="mt-1 flex flex-wrap items-center
                               gap-x-3 gap-y-1 text-xs text-slate-500">
                        <span>
                            Employee ID:
                            <strong class="font-mono text-slate-700">
                                {{ $employee->employee_id }}
                            </strong>
                        </span>

                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>

                        <span>
                            PIC:
                            <strong class="text-slate-700">
                                {{ $picName }}
                            </strong>
                        </span>

                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>

                        <span>
                            Source:
                            <strong class="text-slate-700">
                                {{ $source }}
                            </strong>
                        </span>
                    </div>
                </div>
            </div>

            <span
                class="inline-flex w-fit rounded-full px-3 py-1
                       text-xs font-bold ring-1 ring-inset
                       {{ $statusClass }}">
                {{ $statusText }}
            </span>
        </div>

        <div class="grid gap-px bg-slate-200 sm:grid-cols-3">
            <div class="bg-white px-5 py-4">
                <p class="text-[10px] font-bold uppercase
                           tracking-wider text-slate-400">
                    Completion
                </p>

                <p class="mt-1 text-2xl font-extrabold text-orange-600">
                    {{ $completion }}%
                </p>
            </div>

            <div class="bg-white px-5 py-4">
                <p class="text-[10px] font-bold uppercase
                           tracking-wider text-slate-400">
                    Filled Fields
                </p>

                <p class="mt-1 text-2xl font-extrabold text-emerald-600">
                    {{ $filledFields }}
                </p>
            </div>

            <div class="bg-white px-5 py-4">
                <p class="text-[10px] font-bold uppercase
                           tracking-wider text-slate-400">
                    Missing Fields
                </p>

                <p class="mt-1 text-2xl font-extrabold text-rose-600">
                    {{ $missingFields }}
                </p>
            </div>
        </div>

        <div class="px-5 py-4">
            <div class="h-2 overflow-hidden rounded-full bg-slate-300" role="progressbar"
                aria-label="Employee profile completion" aria-valuemin="0" aria-valuemax="100"
                aria-valuenow="{{ $completion }}">
                <div class="h-full rounded-full bg-orange-500" style="width: {{ $completion }}%"></div>
            </div>

            <p class="mt-2 text-xs text-slate-500">
                {{ $filledFields }} of {{ $totalFields }} fields are completed.
            </p>
        </div>
    </section>

    {{-- Filter toolbar --}}
    <section
        class="sticky top-0 z-10 rounded-2xl border
               border-slate-300 bg-white/95 p-3
               shadow-sm backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row
                   lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-md">
                <svg class="pointer-events-none absolute left-3.5
                           top-1/2 h-4 w-4 -translate-y-1/2
                           text-slate-400"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.4a6.75
                           6.75 0 11-13.5 0 6.75 6.75
                           0 0113.5 0z" />
                </svg>

                <input type="search"
                    class="block w-full rounded-xl border
                           border-slate-300 bg-white py-2.5
                           pl-10 pr-4 text-sm text-slate-900
                           outline-none transition
                           placeholder:text-slate-400
                           focus:border-orange-500
                           focus:ring-4 focus:ring-orange-100"
                    placeholder="Search field or value..." data-details-search>
            </div>

            <div class="inline-flex w-full rounded-xl
                       bg-slate-100 p-1 lg:w-auto" role="group"
                aria-label="Filter employee fields">
                <button type="button"
                    class="flex-1 rounded-lg bg-white px-4 py-2
                           text-xs font-bold cursor-pointer text-slate-900 shadow-sm
                           lg:flex-none"
                    data-details-filter="all" aria-pressed="true">
                    All
                </button>

                <button type="button"
                    class="flex-1 rounded-lg px-4 py-2
                           text-xs font-bold text-slate-500
                           transition cursor-pointer hover:text-rose-600 lg:flex-none"
                    data-details-filter="missing" aria-pressed="false">
                    Missing
                    <span class="ml-1 text-rose-600">
                        {{ $missingFields }}
                    </span>
                </button>

                <button type="button"
                    class="flex-1 rounded-lg px-4 py-2
                           text-xs font-bold text-slate-500
                           transition cursor-pointer hover:text-emerald-600 lg:flex-none"
                    data-details-filter="filled" aria-pressed="false">
                    Filled
                    <span class="ml-1 text-emerald-600">
                        {{ $filledFields }}
                    </span>
                </button>
            </div>
        </div>
    </section>

    {{-- Data groups --}}
    <div class="space-y-4" data-details-groups>
        @foreach ($groups as $groupIndex => $group)
            @php
                $groupFields = collect($group['fields']);
                $groupFilled = $groupFields->where('filled', true)->count();
                $groupMissing = $groupFields->count() - $groupFilled;
            @endphp

            @if ($groupFields->isNotEmpty())
                <details
                    class="group overflow-hidden rounded-2xl
                           border border-slate-300 bg-white"
                    data-details-group @if ($groupIndex === 0) open @endif>
                    <summary
                        class="flex cursor-pointer list-none
                               items-center justify-between gap-4
                               px-5 py-4 transition
                               hover:bg-slate-50">
                        <div class="min-w-0">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center
                                           rounded-xl bg-orange-50
                                           text-xs font-extrabold
                                           text-orange-600">
                                    {{ $groupIndex + 1 }}
                                </span>

                                <div class="min-w-0">
                                    <h3
                                        class="truncate text-sm font-bold
                                               text-slate-900">
                                        {{ $group['title'] }}
                                    </h3>

                                    @if ($group['description'])
                                        <p
                                            class="mt-0.5 truncate
                                                   text-xs text-slate-500">
                                            {{ $group['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-xs font-bold text-slate-700">
                                    {{ $groupFilled }} / {{ $groupFields->count() }}
                                </p>

                                <p class="text-[10px] text-slate-400">
                                    {{ $groupMissing }} missing
                                </p>
                            </div>

                            <svg class="h-5 w-5 text-slate-400
                                       transition-transform
                                       group-open:rotate-180"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </div>
                    </summary>

                    <div class="border-t border-slate-300">
                        @foreach ($group['fields'] as $field)
                            @php
                                $searchText = strtolower(
                                    implode(' ', [$field['label'], $field['name'], $field['value'] ?? '']),
                                );

                                /*
                                 * Gunakan nama kolom, bukan label.
                                 */
                                $isAttachment = array_key_exists($field['name'], $attachmentFolders);

                                $attachment = null;

                                if ($isAttachment && $field['filled']) {
                                    /*
                                     * Nilai database dapat berupa:
                                     *
                                     * ijazah_23721.jpg
                                     *
                                     * atau:
                                     *
                                     * employee-documents/ijazah/ijazah_23721.jpg
                                     */
                                    $storedValue = trim((string) $field['value']);

                                    /*
                                     * Normalisasi separator Windows menjadi URL separator.
                                     */
                                    $normalizedPath = str_replace('\\', '/', $storedValue);

                                    $normalizedPath = ltrim($normalizedPath, '/');

                                    /*
                                     * Hilangkan awalan storage/ jika tersimpan di database.
                                     */
                                    $normalizedPath = preg_replace('#^storage/#i', '', $normalizedPath);

                                    /*
                                     * Jika database hanya menyimpan nama file,
                                     * tambahkan folder berdasarkan jenis attachment.
                                     */
                                    if (str_contains($normalizedPath, '/')) {
                                        $relativePath = $normalizedPath;
                                    } else {
                                        $relativePath = $attachmentFolders[$field['name']] . '/' . $normalizedPath;
                                    }

                                    $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

                                    $attachment = [
                                        'name' => basename($relativePath),

                                        'path' => $relativePath,

                                        'url' =>
                                            asset('storage/' . $relativePath) .
                                            '?v=' .
                                            $employee->updated_at->timestamp,

                                        'extension' => $extension,

                                        'is_image' => in_array($extension, $imageExtensions, true),

                                        'is_pdf' => $extension === 'pdf',

                                        'exists' => \Illuminate\Support\Facades\Storage::disk('public')->exists(
                                            $relativePath,
                                        ),
                                    ];
                                }
                            @endphp

                            <div class="grid gap-2 border-b
                                       border-slate-100 px-5 py-4
                                       last:border-b-0
                                       md:grid-cols-[minmax(220px,32%)_minmax(0,1fr)_110px]
                                       md:items-start md:gap-5
                                       {{ $field['filled'] ? 'bg-white' : 'border-l-4 border-l-rose-400 bg-rose-50/45' }}"
                                data-details-row data-details-status="{{ $field['filled'] ? 'filled' : 'missing' }}"
                                data-details-search-text="{{ $searchText }}">
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-bold
                                               text-slate-700">
                                        {{ $field['label'] }}
                                    </p>
                                </div>

                                <div class="min-w-0">
                                    <div class="min-w-0">
                                        {{-- Field belum terisi --}}
                                        @if (!$field['filled'])
                                            <div
                                                class="inline-flex items-center gap-2 rounded-lg
                   bg-white/70 px-3 py-2 text-sm
                   font-semibold text-rose-600
                   ring-1 ring-inset ring-rose-200">
                                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5
                       a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>

                                                No data available
                                            </div>

                                            {{-- Field attachment --}}
                                        @elseif ($isAttachment)
                                            @if (!$attachment || !$attachment['exists'])
                                                <div
                                                    class="inline-flex items-center gap-2
                       rounded-lg bg-rose-50 px-3 py-2
                       text-sm font-semibold text-rose-700
                       ring-1 ring-inset ring-rose-200">
                                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5
                           a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>

                                                    File not found in storage
                                                </div>

                                                <p
                                                    class="mt-2 break-all font-mono
                       text-[10px] text-slate-400">
                                                    {{ $attachment['path'] ?? $field['value'] }}
                                                </p>

                                                {{-- JPG, JPEG, PNG --}}
                                            @elseif ($attachment['is_image'])
                                                <div class="max-w-xl">
                                                    <a href="{{ $attachment['url'] }}" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="group/image block overflow-hidden
                           rounded-xl border border-slate-200
                           bg-slate-50 p-2 transition
                           hover:border-orange-300
                           hover:shadow-md"
                                                        title="Open full-size image">
                                                        <img src="{{ $attachment['url'] }}"
                                                            alt="{{ $field['label'] }}" loading="lazy"
                                                            class="mx-auto max-h-80 w-auto max-w-full
                               rounded-lg object-contain transition
                               group-hover/image:scale-[1.01]">
                                                    </a>

                                                    <div
                                                        class="mt-2 flex flex-col gap-2
                           sm:flex-row sm:items-center
                           sm:justify-between">
                                                        <p class="truncate text-xs font-semibold
                               text-slate-500"
                                                            title="{{ $attachment['name'] }}">
                                                            {{ $attachment['name'] }}
                                                        </p>

                                                        <a href="{{ $attachment['url'] }}" target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex w-fit items-center
                               gap-1.5 text-xs font-bold
                               text-orange-600 transition
                               hover:text-orange-700">
                                                            Open Image

                                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M13.5 6H18m0 0v4.5M18
                                   6l-7.5 7.5M6 8.25v9.75h9.75" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>

                                                {{-- PDF --}}
                                            @elseif ($attachment['is_pdf'])
                                                <div
                                                    class="flex max-w-xl flex-col gap-3
                       rounded-xl border border-red-200
                       bg-red-50/60 p-4 sm:flex-row
                       sm:items-center sm:justify-between">
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <div
                                                            class="flex h-11 w-11 shrink-0
                               items-center justify-center
                               rounded-xl bg-red-100
                               text-xs font-extrabold text-red-700">
                                                            PDF
                                                        </div>

                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-bold
                                   text-slate-900"
                                                                title="{{ $attachment['name'] }}">
                                                                {{ $attachment['name'] }}
                                                            </p>

                                                            <p class="mt-0.5 text-xs text-slate-500">
                                                                PDF document
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <a href="{{ $attachment['url'] }}"
                                                        download="{{ $attachment['name'] }}"
                                                        class="inline-flex min-h-10 shrink-0
                           items-center justify-center gap-2
                           rounded-xl bg-red-600 px-4
                           text-xs font-bold text-white
                           transition hover:bg-red-700
                           focus:outline-none focus:ring-4
                           focus:ring-red-200">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4
                               4l4-4M5 19.5h14" />
                                                        </svg>

                                                        Download PDF
                                                    </a>
                                                </div>

                                                {{-- Format tidak didukung --}}
                                            @else
                                                <div
                                                    class="rounded-xl border border-amber-200
                       bg-amber-50 p-4">
                                                    <p class="text-sm font-bold text-amber-800">
                                                        Unsupported file format
                                                    </p>

                                                    <p class="mt-1 text-xs text-amber-700">
                                                        File extension:
                                                        {{ strtoupper($attachment['extension']) }}
                                                    </p>
                                                </div>
                                            @endif

                                            {{-- Field biasa --}}
                                        @else
                                            <p class="text-sm font-semibold text-slate-900">
                                                {{ $field['value'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="md:text-right">
                                    @if ($field['filled'])
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-emerald-50 px-2.5
                                                   py-1 text-[10px]
                                                   font-bold uppercase
                                                   tracking-wide
                                                   text-emerald-700
                                                   ring-1 ring-inset
                                                   ring-emerald-200">
                                            Filled
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full
                                                   bg-rose-100 px-2.5
                                                   py-1 text-[10px]
                                                   font-bold uppercase
                                                   tracking-wide
                                                   text-rose-700
                                                   ring-1 ring-inset
                                                   ring-rose-200">
                                            Not Filled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach
    </div>

    <div class="hidden rounded-2xl border border-dashed
               border-slate-300 bg-slate-50 px-6 py-12
               text-center"
        data-details-empty>
        <h3 class="text-sm font-bold text-slate-900">
            No matching fields
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Try a different search keyword or filter.
        </p>
    </div>
</div>
