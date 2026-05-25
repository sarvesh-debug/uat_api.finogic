@php
    $superAdmin = \App\Models\SuperAdmin::find(session('superadmin_id'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- <title>Dashboard - aarpiz B2B Onboarding</title> --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome CDN -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-papb9D9Yp+T+5TdfQbA1iJ9ecV9c4L8FZVcz6B/sxE6b5Rm8i0r1j/mx3Zt4xVpTbxBqL1fPNY2Jlb6kQp8Hw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">


</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="bg-indigo-700 text-white w-64 min-h-screen flex flex-col justify-between hidden md:flex">
        <div>
            <div class="flex items-center gap-3 px-6 py-6 border-b border-indigo-600">
                <span class="text-2xl">📊</span>
                <span class="font-bold text-xl">aarpiz</span>
            </div>
            <!-- Profile -->
            <div class="flex flex-col items-center py-8">
                <div class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-3xl font-bold mb-2">
                    {{ strtoupper(substr($superAdmin->name, 0, 1)) }}
                </div>
                <div class="text-lg font-semibold">{{ $superAdmin->name }}</div>
                <div class="text-sm text-indigo-200">{{ $superAdmin->email }}</div>
            </div>
            <!-- Navigation -->
            <nav class="mt-8 flex flex-col gap-2 px-6">
                <a href="" class="py-2 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-500 font-medium">Dashboard</a>
                <a href="{{ route('superadmin.user.list') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600">Users</a>
                <a href="{{ route('superadmin.txn.all') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600">Transactions</a>
              

                <!-- NEW Menus -->
              
                <a href="{{ route('admin.fundData') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600">Fund Requests</a>
            </nav>
        </div>
        <!-- Logout -->
        <form method="POST" action="{{ route('superadmin.login.out') }}" class="px-6 py-6">
            @csrf
            <button type="submit" class="w-full bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-gray-200 font-semibold">Logout</button>
        </form>
    </aside>

    <!-- Mobile Topbar -->
    <div class="md:hidden w-full bg-indigo-700 text-white flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-2">
            <span class="text-2xl">📊</span>
            <span class="font-bold text-lg">aarpiz</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-white text-indigo-700 px-3 py-1 rounded-lg text-sm font-semibold">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        @yield('content')
        
    </main>
</body>
</html>
