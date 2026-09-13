<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="{{ asset('images/LOGO KAJA.webp') }}" type="image/webp"> 
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Kantong Jamu</title>

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

<body>

<div class="min-h-screen flex">

    <!-- LEFT -->

    <div class="w-1/2 hidden lg:flex flex-col justify-center px-20 relative overflow-hidden bg-gradient-to-br from-emerald-700 via-green-600 to-lime-400">

        <!-- Blur -->

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
                        Production Management System
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

    <!-- RIGHT -->

    <div class="w-full lg:w-1/2 flex items-center justify-center bg-[#f4f7f2] p-10">

        <div class="w-full max-w-md">

            <!-- Header -->

            <div class="mb-10">

                <h2 class="text-5xl font-bold text-gray-800">
                    Register
                </h2>

                <p class="text-gray-500 mt-3 text-lg">
                    Make a new account to access the system.
                </p>

            </div>

            <!-- Card -->

            <div class="bg-white rounded-[35px] shadow-2xl p-10 border border-gray-100">

                @if($errors->any())
                    <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">

                    @csrf

                    <!-- Name -->

                    <div class="mb-5">

                        <label class="block mb-3 font-semibold text-gray-700">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                    </div>

                    <!-- Email -->

                    <div class="mb-5">

                        <label class="block mb-3 font-semibold text-gray-700">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                    </div>

                    <div class="mb-5 rounded-2xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                        New accounts are registered as Worker.
                    </div>

                    <!-- Password -->

                    <div class="mb-5">

                        <label class="block mb-3 font-semibold text-gray-700">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                    </div>

                    <!-- Confirm -->

                    <div class="mb-8">

                        <label class="block mb-3 font-semibold text-gray-700">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

                    </div>

                    <!-- Button -->

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-green-600 to-green-500 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300 text-white font-semibold p-4 rounded-2xl">

                        Create Account

                    </button>

                </form>

                <!-- Login -->

                <div class="text-center mt-8">

                    <p class="text-gray-500">

                        Already have an account?

                        <a
                            href="{{ route('login') }}"
                            class="text-green-600 font-semibold hover:text-green-700">

                            Login

                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
