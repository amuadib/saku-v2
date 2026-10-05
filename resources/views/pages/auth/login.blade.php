<x-layouts::auth.card title="Masuk ke Akun Anda">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Masuk ke Akun Anda" description="Masukkan username dan password Anda untuk login" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="username"
                label="Username"
                :value="old('username')"
                type="text"
                required
                autofocus
                autocomplete="username"
                placeholder="Username"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    label="Password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                    viewable
                />
            </div>

            <flux:checkbox name="remember" label="Ingat Saya" :checked="old('remember')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                Masuk
            </flux:button>
        </div>
    </form>
</x-layouts::auth.card>
