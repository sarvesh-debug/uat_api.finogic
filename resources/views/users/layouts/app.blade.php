


<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false, activeTab: '{{ Route::currentRouteName() }}' }">
<head>
    <meta charset="UTF-8">
    {{-- <title>Dashboard - aarpiz aarpiz</title> --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Font Awesome CDN -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

   <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <style>
        html {
        font-size: 14px;
    }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f7; }

        /* Sidebar */
        .sidebar { background: linear-gradient(180deg,#4f46e5,#3730a3); box-shadow: 4px 0 20px rgba(0,0,0,0.1); }
        .nav-item { transition: all 0.25s ease; border-radius: 10px; margin-bottom: 4px; }
        .nav-item:hover { transform: translateX(5px); background-color: rgba(255,255,255,0.1); }
        .nav-item.active { background-color: rgba(255,255,255,0.25); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }

        .profile-avatar { box-shadow: 0 4px 12px rgba(0,0,0,0.2); border: 3px solid rgba(255,255,255,0.2); }

        /* Topbar */
        .topbar { background: linear-gradient(to right,#ffffff,#f3f4f6); box-shadow: 0 2px 15px rgba(0,0,0,0.08); }

        /* Balance & Status */
        .balance-card { background: linear-gradient(to right,#4f46e5,#6366f1); color: white; border-radius: 14px; padding: 10px 16px; box-shadow: 0 4px 20px rgba(79,70,229,0.2); }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-weight: 500; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; }
        .status-active { background-color: #10b98120; color: #10b981; }
        .status-pending { background-color: #f59e0b20; color: #f59e0b; }
        .status-rejected { background-color: #ef444420; color: #ef4444; }

        /* Buttons */
        .btn-primary { background: linear-gradient(to right,#4f46e5,#6366f1); color: white; box-shadow: 0 4px 12px rgba(79,70,229,0.2); transition: all 0.3s ease; }
        .btn-primary:hover { background: linear-gradient(to right,#4338ca,#4f46e5); transform: translateY(-2px); box-shadow: 0 6px 14px rgba(79,70,229,0.3); }

        .submenu { border-left: 2px solid rgba(255,255,255,0.15); padding-left: 6px; }
        .logo-text { font-weight: 700; letter-spacing: 0.5px; }
    </style>
</head>
<body class="h-screen flex overflow-hidden">

    @php
        $status = auth('remittance')->user()->status ?? 'pending';
        $statusClass = $status==='success'?'status-active':($status==='pending'?'status-pending':'status-rejected');
    @endphp

@if(auth('remittance')->check() 
    && auth('remittance')->user()->isKyc == 0 
    && Route::currentRouteName() !== 'remittances.kyc.form')

<!-- KYC Popup -->
<div id="kycModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full text-center">
        <div class="text-red-500 text-5xl mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h2 class="text-xl font-bold mb-2">⚠️ KYC Pending</h2>
        <p class="text-gray-600 mb-6">Please complete your KYC verification to continue using our services.</p>
        <a href="{{ route('remittances.kyc.form') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow font-semibold block w-full">
            Complete KYC
        </a>
    </div>
</div>

<!-- Disable background interaction -->
<style>
    body { overflow: hidden; }
</style>
@endif


    <!-- Sidebar -->
    <aside
        class="sidebar text-white w-64 flex flex-col justify-between transform lg:translate-x-0 fixed lg:static inset-y-0 left-0 z-50 transition-transform duration-300 overflow-y-auto"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div
        class="sidebar text-white w-64 flex flex-col justify-between transform md:translate-x-0 fixed md:static inset-y-0 left-0 z-50 transition-transform duration-300 overflow-y-auto"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" >
 <div class="flex items-center gap-3 px-6 py-4 border-b border-indigo-600">
                <img src="{{asset('img/aarpiz-logo.png')}}" width="" alt="">
            </div>
        @if (auth('remittance')->user()->isKyc == 1)
            <div>
            <!-- Logo -->
           

            <!-- Profile -->
            <div class="flex flex-col items-center py-6 px-4">
                <div class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-3xl font-bold mb-3 profile-avatar">
                    {{ strtoupper(substr(optional(auth('remittance')->user())->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex items-center space-x-2 mb-1">
                    <div class="text-lg font-semibold">{{ auth('remittance')->user()->name ?? 'Guest' }}</div>
                    @if(auth('remittance')->user()->isKyc == 1)
                        <span class="text-green-400 text-sm"><i class="fa-solid fa-circle-check"></i></span>
                    @endif
                </div>
                <div class="text-sm text-indigo-200 bg-indigo-900 px-3 py-1 rounded-full mt-2">
                    ID: {{ auth('remittance')->user()->remId ?? 'N/A' }}
                </div>
                <div class="text-sm text-indigo-200 mt-2">{{ auth('remittance')->user()->email ?? 'N/A' }}</div>
            </div>

            <!-- Mobile Status Badge -->
            <div class="px-4 py-3 lg:hidden">
                <div class="status-badge font-medium {{ $statusClass }}">
                    @if($status==='success') <i class="fa-solid fa-check-circle"></i> Active
                    @elseif($status==='pending') <i class="fa-solid fa-clock"></i> Pending
                    @elseif($status==='rejected') <i class="fa-solid fa-xmark-circle"></i> Rejected
                    @else <i class="fa-solid fa-question-circle"></i> Unknown @endif
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-4 flex flex-col gap-1 px-4">
                <a href="{{ route('remittances.dashboard') }}" :class="{'active': activeTab=='remittances.dashboard'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i> Dashboard
                </a>

                <a href="{{ route('remittances.kyc.status', ['id' => auth('remittance')->user()->id]) }}" :class="{'active': activeTab=='remittances.kyc.status'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-id-card-clip w-5 text-center"></i> KYC Verification
                </a>
                <a href="{{ route('remittances.add.fund') }}" :class="{'active': activeTab=='remittances.add.fund'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-wallet w-5 text-center"></i> Add Funds
                </a>
                {{-- <a href="{{ route('remittances.charges') }}" :class="{'active': activeTab=='remittances.charges'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-credit-card w-5 text-center"></i> Charges
                </a> --}}

                <!-- Dropdown History -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="nav-item w-full flex items-center justify-between py-3 px-4">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>Reports</span>
                        <i :class="open ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-sm"></i>
                    </button>
                    <div x-show="open" x-transition class="ml-6 mt-1 flex flex-col gap-1 submenu pl-2 py-1">
                        <a href="{{ route('track.transaction.rem') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Payout P1 Report</a>
                        <a href="{{ route('upiHistory') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">UPI Payout</a>
                        <a href="{{ route('remittances.add.fund.his') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Fund Report</a>
                        <a href="{{ route('pg.report') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">PG Report</a>
                        <a href="{{ route('pg1.report') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">PG1 Report</a>
                        <a href="{{ route('pg2.report') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">PG2 Report</a>
                        <a href="{{ route('track.aeps.rem') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">AEPS Report</a>
                        <a href="{{ route('track.aeps.rem.v2') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">AEPS v2 Report</a>
                        <a href="{{ route('track.dmt.rem') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">DMT Report</a>
                        <a href="{{ route('track.aeps.rem') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">BBPS Report</a>
                        <a href="{{ route('user.refund.reports') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Refund Report</a>
                        <a href="{{ route('summary.view.user') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Ledger</a>
                    </div>
                </div>

                <a href="{{ route('remittances.profile') }}" :class="{'active': activeTab=='remittances.profile'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i> Profile Settings
                </a>
                <a href="{{ route('api.docs') }}" :class="{'active': activeTab=='api.docs'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-code w-5 text-center"></i> Developer Tools
                </a>
            </nav>
        </div>
        @else
         <div>
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-indigo-600">
                {{-- <div class="bg-white p-2 rounded-xl">
                    <span class="text-indigo-700 text-xl font-bold">XP</span>
                </div>
                <span class="font-bold text-xl logo-text">aarpiz</span> --}}

                <img src="{{asset('img/white aarpiz logo1.png')}}" width="160px" alt="">
            </div>

            <!-- Profile -->
            <div class="flex flex-col items-center py-6 px-4">
                <div class="w-20 h-20 rounded-full bg-indigo-500 flex items-center justify-center text-3xl font-bold mb-3 profile-avatar">
                    {{ strtoupper(substr(optional(auth('remittance')->user())->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex items-center space-x-2 mb-1">
                    <div class="text-lg font-semibold">{{ auth('remittance')->user()->name ?? 'Guest' }}</div>
                    @if(auth('remittance')->user()->isKyc == 1)
                        <span class="text-green-400 text-sm"><i class="fa-solid fa-circle-check"></i></span>
                    @endif
                </div>
                <div class="text-sm text-indigo-200 bg-indigo-900 px-3 py-1 rounded-full mt-2">
                    ID: {{ auth('remittance')->user()->remId ?? 'N/A' }}
                </div>
                <div class="text-sm text-indigo-200 mt-2">{{ auth('remittance')->user()->email ?? 'N/A' }}</div>
            </div>

            <!-- Mobile Status Badge -->
            <div class="px-4 py-3 md:hidden">
                <div class="status-badge font-medium {{ $statusClass }}">
                    @if($status==='success') <i class="fa-solid fa-check-circle"></i> Active
                    @elseif($status==='pending') <i class="fa-solid fa-clock"></i> Pending
                    @elseif($status==='rejected') <i class="fa-solid fa-xmark-circle"></i> Rejected
                    @else <i class="fa-solid fa-question-circle"></i> Unknown @endif
                </div>
            </div>

            <!-- Navigation -->
            <!-- Navigation -->
            <nav class="mt-4 flex flex-col gap-1 px-4">
                <a href="{{ route('remittances.dashboard') }}" :class="{'active': activeTab=='remittances.dashboard'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i> Dashboard
                </a>
                {{-- <a href="{{ route('start_txn') }}" :class="{'active': activeTab=='start_txn'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-play-circle w-5 text-center"></i> Start Transaction
                </a> --}}
                <a href="{{ route('remittances.kyc.form') }}" :class="{'active': activeTab=='remittances.kyc.form'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-id-card-clip w-5 text-center"></i> KYC Verification
                </a>
                <a href="{{ route('remittances.kyc.form') }}" :class="{'active': activeTab=='remittances.kyc.form'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-wallet w-5 text-center"></i> Add Funds
                </a>
                <a href="{{ route('remittances.kyc.form') }}" :class="{'active': activeTab=='remittances.kyc.form'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-credit-card w-5 text-center"></i> Charges
                </a>

                <!-- Dropdown History -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="nav-item w-full flex items-center justify-between py-3 px-4">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left w-5 text-center"></i> History</span>
                        <i :class="open ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-sm"></i>
                    </button>
                    <div x-show="open" x-transition class="ml-6 mt-1 flex flex-col gap-1 submenu pl-2 py-1">
                        <a href="{{ route('remittances.kyc.form') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Txn History</a>
                        <a href="{{ route('remittances.kyc.form') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Fund History</a>
                        {{-- <a href="{{ route('businesses.index') }}" class="py-2 px-4 rounded-lg hover:bg-indigo-600 flex items-center gap-2 text-sm">Ledger</a> --}}
                    </div>
                </div>

                <a href="{{ route('remittances.profile') }}" :class="{'active': activeTab=='remittances.profile'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i> Profile Settings
                </a>
                <a href="{{ route('remittances.kyc.form') }}" :class="{'active': activeTab=='remittances.kyc.form'}" class="nav-item py-3 px-4 flex items-center gap-3">
                    <i class="fa-solid fa-code w-5 text-center"></i> Developer Tools
                </a>
            </nav>
        </div>
        @endif
       

        <!-- Logout -->
        <div class="px-4 py-5 border-t border-indigo-600">
            <form method="POST" action="{{ route('remittances.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-white text-indigo-700 px-4 py-3 rounded-lg hover:bg-gray-100 font-semibold flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen">
        <!-- Topbar -->
        <div class="topbar px-6 py-4 flex items-center justify-between lg:justify-end sticky top-0 z-30">
            <!--  FIXED BURGER / CROSS BUTTON -->
            <button class="lg:hidden text-indigo-700 text-xl p-2 rounded-lg hover:bg-indigo-50" @click="sidebarOpen = !sidebarOpen">
                <i :class="sidebarOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'"></i>
            </button>

            <div class="flex items-center gap-3 lg:gap-5">
                <!-- Balance -->
                <div class="balance-card px-4 py-2 flex items-center gap-2">
                    <i class="fa-solid fa-wallet"></i>
                    <span class="hidden lg:inline font-medium">Balance:</span>
                    <span class="font-bold">₹ {{ number_format(auth('remittance')->user()->amount, 2) }}</span>
                </div>
                <!-- Refresh -->
                <a href="{{ route('remittances.add.fund') }}" class="btn-primary text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-rotate"></i>
                    <span class="hidden lg:inline">Add Balance</span>
                </a>

                <!-- Desktop Status Badge -->
                <div class="hidden lg:block ml-3">
                    <div class="status-badge font-medium {{ $statusClass }}">
                        @if($status==='success') <i class="fa-solid fa-check-circle"></i> Active
                        @elseif($status==='pending') <i class="fa-solid fa-clock"></i> Pending
                        @elseif($status==='rejected') <i class="fa-solid fa-xmark-circle"></i> Rejected
                        @else <i class="fa-solid fa-question-circle"></i> Unknown @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            @yield('content')
        </main>
    </div>
</body>
</html>
