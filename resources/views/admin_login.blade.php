<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0b0c10]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TokenGlade — Admin Login</title>
    <meta name="robots" content="noindex, nofollow">
    <!-- Use Tailwind CDN for simple login form styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center text-white">
    <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                TokenGlade Admin
            </h1>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl p-4 mb-6 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="username" class="block text-xs text-gray-500 font-bold uppercase tracking-wider mb-2">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    id="username" 
                    required 
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-500 transition font-medium"
                    placeholder="Enter admin username"
                    value="{{ old('username') }}"
                >
            </div>

            <div>
                <label for="password" class="block text-xs text-gray-500 font-bold uppercase tracking-wider mb-2">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    required 
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-500 transition font-medium"
                    placeholder="••••••••"
                >
            </div>

            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    name="remember" 
                    id="remember" 
                    class="rounded bg-gray-950 border-gray-800 text-purple-500 focus:ring-0 focus:ring-offset-0"
                >
                <label for="remember" class="ml-2 text-xs text-gray-400 select-none">Remember this device</label>
            </div>

            <button 
                type="submit" 
                class="w-full font-bold px-6 py-3.5 rounded-xl text-white bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] hover:opacity-90 transition shadow-lg shadow-purple-500/10"
            >
                Log In
            </button>
        </form>
    </div>
</body>
</html>
