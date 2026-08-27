<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Go Pharmacy — Maintenance</title>

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-slate-950 text-white">

    <main class="flex min-h-screen items-center justify-center px-6">

        <div class="w-full max-w-xl text-center">

            {{-- Go Pharmacy Logo --}}
            <div class="mb-8 flex justify-center">
                <img
                    src="{{ asset('images/branding/go-pharmacy-logo-transparent.png') }}"
                    alt="Go Pharmacy"
                    class="h-20 w-auto object-contain"
                >
            </div>

            
            {{-- Heading --}}
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                We'll be back soon.
            </h1>

            {{-- Message --}}
            <p class="mx-auto mt-6 max-w-lg text-base leading-7 text-slate-300">
                Our website is currently undergoing maintenance.
                We're working to make your healthcare experience
                even better.
            </p>

            {{-- Tagline --}}
            <p class="mt-8 text-sm text-slate-500">
                Good Health. Made Simple.
            </p>

        </div>

    </main>

</body>
</html>