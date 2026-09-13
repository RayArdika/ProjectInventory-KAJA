<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Kantong Jamu</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            font-family:'Poppins',sans-serif;
        }

        body{
            overflow-x:hidden;
            overflow-y:auto;
        }

    </style>

</head>
<link rel="icon" href="{{ asset('images/LOGO KAJA.webp') }}" type="image/webp">
<body>
    <div class="min-h-screen flex">

        <!-- LEFT SIDE -->

        <div class="w-1/2 hidden lg:flex flex-col justify-center px-20 relative overflow-hidden bg-gradient-to-br from-green-700 via-green-600 to-emerald-400">

            <!-- Blur Circle -->

            <div class="absolute w-96 h-96 bg-white/10 rounded-full -top-20 -left-20 blur-3xl"></div>

            <div class="absolute w-80 h-80 bg-white/10 rounded-full bottom-0 right-0 blur-3xl"></div>

            <!-- Content -->

            <div class="relative z-10">

                <div class="flex items-center gap-4 mb-8">

                    <img
                        src="{{ asset('images/kantong-jamu-logo.png') }}"
                        alt="Kantong Jamu"
                        class="w-72 bg-white p-4 rounded-3xl shadow-2xl">

                    <div class="sr-only">

                        <h1 class="text-5xl font-bold text-white">
                            Kantong Jamu
                        </h1>

                        <p class="text-white/80 mt-2 text-lg">
                            Internal Production Management System
                        </p>

                    </div>

                </div>

                <div class="space-y-6 mt-14">

                    <div class="bg-white/10 backdrop-blur-lg p-6 rounded-3xl border border-white/10">

                        <h3 class="text-white text-2xl font-semibold mb-2">
                            Monitoring Production
                        </h3>

                        <p class="text-white/70">
                            Real-time monitoring of stock and production.
                        </p>

                    </div>

                    <div class="bg-white/10 backdrop-blur-lg p-6 rounded-3xl border border-white/10">

                        <h3 class="text-white text-2xl font-semibold mb-2">
                            Inventory Automation
                        </h3>

                        <p class="text-white/70">
                            Stock product automatically updates in real-time.
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- RIGHT SIDE -->

        <div class="w-full lg:w-1/2 flex items-center justify-center bg-[#f4f7f2] p-10">

            <div class="w-full max-w-md">

                <!-- Header -->

                <div class="mb-10">

                    <h2 class="text-5xl font-bold text-gray-800">
                        Welcome
                    </h2>

                    <p class="text-gray-500 mt-3 text-lg">
                        Login to access the Kantong Jamu dashboard
                    </p>

                </div>

                <!-- Card -->

                <div class="bg-white rounded-[35px] shadow-2xl p-10 border border-gray-100">

                    @if(session('success'))
                        <div role="status" class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div
                            role="alert"
                            class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                            The email or password you entered is incorrect.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">

                        @csrf

                        <!-- Email -->

                        <div class="mb-6">

                            <label class="block mb-3 font-semibold text-gray-700">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                        </div>

                        <!-- Password -->

                        <div class="mb-6">

                            <label class="block mb-3 font-semibold text-gray-700">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                required
                                class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                        </div>

                        <!-- Remember -->

                        <div class="flex items-center mb-8">

                            <label class="flex items-center gap-2">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-gray-300 text-green-600 shadow-sm">

                                <span class="text-gray-600">
                                    Remember me
                                </span>

                            </label>

                        </div>

                        <!-- Button -->

                        <button
                            type="submit"
                            class="w-full bg-gradient-to-r from-green-600 to-green-500 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300 text-white font-semibold p-4 rounded-2xl">

                            Login Sekarang

                        </button>

                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500">
                            Don't have a worker account?
                            <a href="{{ route('register') }}" class="font-semibold text-green-600 hover:text-green-700">
                                Register
                            </a>
                        </p>
                        <p class="mt-2 text-xs text-gray-400">
                            Registration creates a Worker account only.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>
</body>
</html>
