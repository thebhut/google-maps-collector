<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Google Maps Collector Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">

    <div class="w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-2xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-indigo-600 items-center justify-center text-2xl shadow-lg shadow-indigo-500/30">
                🗺️
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Admin Sign In</h1>
            <p class="text-xs text-slate-400">Google Maps Business Collector Dashboard</p>
        </div>

        @if ($errors->any())
            <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" value="{{ old('email', 'admin@example.com') }}" required autofocus
                    class="w-full px-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">Password</label>
                <input type="password" name="password" value="password" required
                    class="w-full px-4 py-2.5 bg-slate-800/80 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-semibold rounded-xl text-sm shadow-lg shadow-indigo-600/30 transition-all active:scale-[0.99]">
                Sign In to Dashboard
            </button>
        </form>

        <div class="pt-4 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-500">Default Demo Credentials: <span class="text-slate-400 font-mono">admin@example.com / password</span></p>
        </div>
    </div>

</body>
</html>
