<x-layout title="Login">
    <div class="relative min-h-screen overflow-hidden bg-slate-50">
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(249,115,22,0.12),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(251,146,60,0.10),transparent_30%)]"
        ></div>

        <main class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <section
                class="grid w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.10)] lg:grid-cols-[1.05fr_0.95fr]"
            >
                {{-- Brand panel --}}
                <div
                    class="relative hidden overflow-hidden bg-gradient-to-br from-orange-600 via-orange-500 to-amber-400 px-10 py-12 text-white lg:flex lg:flex-col"
                >
                    <div
                        aria-hidden="true"
                        class="absolute -right-20 -top-20 h-72 w-72 rounded-full border-[46px] border-white/10"
                    ></div>

                    <div
                        aria-hidden="true"
                        class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"
                    ></div>

                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white/95 shadow-lg ring-1 ring-white/50"
                        >
                            <img
                                src="{{ asset('img/kanmo-logo.jpeg') }}"
                                alt="Kanmo Group"
                                class="h-full w-full object-cover"
                                onerror="this.style.display='none'; this.parentElement.textContent='K';"
                            >
                        </div>

                        <div>
                            <p class="text-xl font-extrabold tracking-wide">Kanmo Group</p>
                            <p class="mt-1 text-sm text-white/75">Employee Data Completion</p>
                        </div>
                    </div>

                    <div class="relative mt-auto max-w-lg pb-6">
                        <span
                            class="inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-bold tracking-[0.14em] text-white/90 backdrop-blur"
                        >
                            PEOPLE PROFILE PORTAL
                        </span>

                        <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight">
                            Welcome back.
                        </h1>

                        <p class="mt-4 max-w-md text-sm leading-7 text-white/80">
                            Access the employee completion dashboard, manage employee data,
                            and monitor profile progress securely in one place.
                        </p>
                    </div>
                </div>

                {{-- Login panel --}}
                <div class="px-5 py-8 sm:px-10 sm:py-12 lg:px-12">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <div
                            class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-orange-500 text-sm font-extrabold text-white"
                        >
                            <img
                                src="{{ asset('img/kanmo-logo.jpeg') }}"
                                alt="Kanmo Group"
                                class="h-full w-full object-cover"
                                onerror="this.style.display='none'; this.parentElement.textContent='K';"
                            >
                        </div>

                        <div>
                            <p class="text-base font-extrabold text-slate-900">Kanmo Group</p>
                            <p class="text-xs text-slate-500">Employee Data Completion</p>
                        </div>
                    </div>

                    <div class="mx-auto w-full max-w-md">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-orange-600">
                                Secure Login
                            </p>

                            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">
                                Log in to your account
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Log in using your Employee ID and password.
                            </p>
                        </div>

                        @if (session('status'))
                            <div
                                class="mt-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"
                                role="status"
                            >
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <p>{{ session('status') }}</p>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"
                                role="alert"
                            >
                                <div class="flex items-start gap-3">
                                    <svg
                                        class="mt-0.5 h-5 w-5 shrink-0"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 9v3.75m9-1.386c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9zM12 16.5h.008v.008H12V16.5z"
                                        />
                                    </svg>

                                    <div>
                                        <p class="font-bold">Login failed / Login gagal</p>
                                        <p class="mt-1 text-xs leading-5">
                                            Please check your Employee ID and password.
                                            Periksa kembali Employee ID dan password Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('login') }}"
                            class="mt-8 space-y-5"
                            data-login-form
                        >
                            @csrf

                            <div>
                                <label for="employee_id" class="mb-2 block text-sm font-bold text-slate-700">
                                    Employee ID / NIP
                                </label>

                                <div class="relative">
                                    <div
                                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0A17.9 17.9 0 0112 21.75a17.9 17.9 0 01-7.5-1.65z"
                                            />
                                        </svg>
                                    </div>

                                    <input
                                        type="text"
                                        id="employee_id"
                                        name="employee_id"
                                        value="{{ old('employee_id') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        inputmode="text"
                                        maxlength="20"
                                        placeholder="Enter Employee ID"
                                        class="block w-full rounded-xl border {{ $errors->has('employee_id') ? 'border-rose-400 bg-rose-50/40' : 'border-slate-300 bg-white' }} py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                    >
                                </div>

                                @error('employee_id')
                                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-sm font-bold text-slate-700">
                                    Password
                                </label>

                                <div class="relative">
                                    <div
                                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 0h10.5A2.25 2.25 0 0119.5 12.75v6A2.25 2.25 0 0117.25 21H6.75a2.25 2.25 0 01-2.25-2.25v-6a2.25 2.25 0 012.25-2.25z"
                                            />
                                        </svg>
                                    </div>

                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Enter password"
                                        class="block w-full rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/40' : 'border-slate-300 bg-white' }} py-3 pl-11 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                                    >

                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 transition hover:text-orange-600"
                                        data-password-toggle
                                        aria-label="Show password"
                                        aria-pressed="false"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            data-eye-open
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>

                                        <svg
                                            class="hidden h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            data-eye-closed
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 3l18 18M10.6 10.6A2 2 0 0013.4 13.4M9.9 5.4A10.8 10.8 0 0112 5.25c6 0 9.75 6.75 9.75 6.75a16.6 16.6 0 01-3.1 3.9M6.2 6.2C3.7 8 2.25 12 2.25 12a16.2 16.2 0 004.2 4.55A9.3 9.3 0 0012 18.75c1 0 1.95-.18 2.82-.5"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                @error('password')
                                    <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white shadow-[0_10px_24px_rgba(249,115,22,0.25)] transition hover:bg-orange-600 hover:shadow-[0_12px_28px_rgba(249,115,22,0.30)] focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:cursor-not-allowed disabled:opacity-70"
                                data-login-button
                            >
                                <svg
                                    class="hidden h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    data-login-spinner
                                    aria-hidden="true"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>

                                <span data-login-label>Log In</span>

                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </form>

                        <div
                            class="mt-8 flex items-start gap-2.5 rounded-xl border border-orange-100 bg-orange-50/70 p-3 text-xs leading-5 text-orange-900/75"
                        >
                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-orange-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                            <p>
                                For security, do not share your Employee ID or password with anyone.
                                Jangan membagikan Employee ID atau password Anda.
                            </p>
                        </div>

                        <p class="mt-7 text-center text-[11px] text-slate-400">
                            Kanmo Group · Employee Data Completion
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const form = document.querySelector("[data-login-form]");
                const passwordInput = document.querySelector("#password");
                const toggle = document.querySelector("[data-password-toggle]");
                const eyeOpen = document.querySelector("[data-eye-open]");
                const eyeClosed = document.querySelector("[data-eye-closed]");
                const loginButton = document.querySelector("[data-login-button]");
                const loginSpinner = document.querySelector("[data-login-spinner]");
                const loginLabel = document.querySelector("[data-login-label]");

                toggle?.addEventListener("click", function () {
                    const isVisible = passwordInput.type === "text";

                    passwordInput.type = isVisible ? "password" : "text";
                    toggle.setAttribute("aria-pressed", String(!isVisible));
                    toggle.setAttribute(
                        "aria-label",
                        isVisible ? "Show password" : "Hide password"
                    );

                    eyeOpen?.classList.toggle("hidden", !isVisible);
                    eyeClosed?.classList.toggle("hidden", isVisible);
                });

                form?.addEventListener("submit", function () {
                    if (!form.checkValidity()) {
                        return;
                    }

                    loginButton.disabled = true;
                    loginSpinner?.classList.remove("hidden");

                    if (loginLabel) {
                        loginLabel.textContent = "Signing In";
                    }
                });
            });
        </script>
    @endpush
</x-layout>