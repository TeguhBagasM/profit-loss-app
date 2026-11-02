<x-guest-layout>
    <h1 class="text-lg font-semibold tracking-tight">Verifikasi email</h1>

    <div class="mt-2 text-sm text-muted">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 text-sm font-medium text-accent">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-muted underline hover:text-ink">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
