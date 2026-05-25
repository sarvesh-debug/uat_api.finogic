




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- <title>Dashboard - CredXpay B2B Onboarding</title> --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

    <link rel="icon" href="{{ asset('img/credxpay-fav.png') }}" type="image/png">

    <style>
          html {
        font-size: 14px;
    }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #ee9d55;
            --primary-dark: #a35e30;
            --secondary: #85f65c;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fafc; 
        }
        .sidebar-gradient { 
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%); 
        }
        .card-gradient { 
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); 
        }
        .active-nav-item { 
            background: rgba(255, 255, 255, 0.1); 
            position: relative; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
        }
        .active-nav-item:before { 
            content: ''; 
            position: absolute; 
            left: 0; 
            top: 0; 
            height: 100%; 
            width: 4px; 
            background: white; 
            border-radius: 0 4px 4px 0; 
        }
        .notification-dot { 
            position: absolute; 
            top: -2px; 
            right: -2px; 
            width: 8px; 
            height: 8px; 
            background: #ef4444; 
            border-radius: 50%; 
        }
        .profile-avatar { 
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); 
            transition: all 0.3s ease; 
            border: 3px solid rgba(255, 255, 255, 0.2); 
        }
        .profile-avatar:hover { 
            transform: scale(1.05); 
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4); 
        }
        .nav-item { 
            transition: all 0.2s ease; 
            position: relative; 
            overflow: hidden; 
        }
        .nav-item:after { 
            content: ''; 
            position: absolute; 
            left: 0; 
            bottom: 0; 
            width: 100%; 
            height: 1px; 
            background: rgba(255, 255, 255, 0.1); 
        }
        .nav-item:hover { 
            background: rgba(255, 255, 255, 0.08); 
            padding-left: 28px; 
        }
        .nav-icon { 
            transition: all 0.3s ease; 
        }
        .nav-item:hover .nav-icon { 
            transform: scale(1.1); 
        }
        .stats-card { 
            transition: all 0.3s ease; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04); 
            border: 1px solid #e2e8f0; 
            overflow: hidden; 
            position: relative; 
        }
        .stats-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08); 
        }
        .stats-card:before { 
            content: ''; 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 4px; 
            background: linear-gradient(90deg, var(--primary), var(--secondary)); 
        }
        
        /* Mobile Sidebar Styles */
        .mobile-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            z-index: 50;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 75%;
            max-width: 300px;
        }
        .mobile-sidebar.open {
            transform: translateX(0);
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        .overlay.open {
            display: block;
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Custom scrollbar for main content */
        .main-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .main-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .main-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .main-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        @media (min-width: 768px) {
            .mobile-sidebar {
                transform: translateX(0);
                position: relative;
                width: 16rem;
                height: 100vh;
            }
            .overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden">
    <!-- Mobile Topbar -->
    <div class="md:hidden w-full bg-gradient-to-r from-indigo-700 to-indigo-800 text-white flex items-center justify-between px-4 py-3 shadow-md fixed top-0 z-30">
        <div class="flex items-center gap-2">
            <button id="menu-toggle" class="p-2 rounded-lg bg-indigo-600 focus:outline-none">
                <i id="menu-icon" class="fas fa-bars text-sm"></i>
            </button>
            <img src="{{asset('img/aarpiz-logo.png')}}"  alt="">
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <button class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                    <i class="fas fa-bell text-sm"></i>
                </button>
                <span class="notification-dot"></span>
            </div>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="overlay" class="overlay md:hidden"></div>

    <!-- Sidebar for mobile -->
    <aside class="mobile-sidebar sidebar-gradient text-white overflow-y-auto sidebar-scroll md:flex md:flex-col md:relative md:translate-x-0 md:shadow-xl">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-indigo-600 flex-shrink-0">
                <img src="{{asset('img/aarpiz-logo.png')}}" width="" alt="">
                <button id="close-sidebar" class="md:hidden text-white p-2 rounded-full bg-indigo-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto sidebar-scroll">
                <!-- Profile -->
                <div class="flex flex-col items-center py-6 px-8">
                    <div class="profile-avatar w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-3xl font-bold mb-3 text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="text-lg font-semibold mb-1">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-indigo-200 mb-3">{{ auth()->user()->email }}</div>
                    <div class="bg-indigo-800/50 rounded-full px-4 py-2 flex items-center gap-2">
                        <span class="text-indigo-200">Balance:</span>
                        <span class="font-semibold text-white">₹{{ auth()->user()->balance }}</span>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="mt-4 flex flex-col gap-1 px-4 pb-4">
                    <a href="{{ route('admin.dashboard') }}" class="nav-item dashboard-link py-3 px-4 rounded-lg font-medium flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-th w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                    {{-- <a href="{{ route('admin.addFund') }}" class="nav-item addfund-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('admin.addFund') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-wallet w-5 text-center"></i>
                        <span>Add Fund</span>
                    </a>
                    <a href="{{ route('admin.fundrequests.index') }}" class="nav-item fund-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('admin.fundrequests.index') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-money-bill-transfer w-5 text-center"></i>
                        <span>Fund</span>
                    </a>--}}
                     <a href="{{ route('manul_fund.index') }}" class="nav-item fund-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('superadmin.txn.all.admin') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-money-bill-transfer w-5 text-center"></i>
                        <span>Manual Fund</span>
                    </a> 
                   
                    <a href="{{ route('packages.index') }}" class="nav-item kyc-link py-3 px-4 rounded-lg flex items-center gap-3">
                        <i class="nav-icon fas fa-id-card w-5 text-center"></i>
                        <span>Package</span>
                    </a>
                     <a href="{{ route('commission-form.newbbps') }}" class="nav-item kyc-link py-3 px-4 rounded-lg flex items-center gap-3">
                        <i class="nav-icon fas fa-id-card w-5 text-center"></i>
                        <span>BBPS Plans</span>
                    </a>
                    <a href="{{ route('admin.ledger.index') }}" 
                    class="nav-item transactions-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('admin.ledger.index') ? 'active-nav-item' : '' }}">
                        
                        <i class="nav-icon fas fa-file-invoice-dollar w-5 text-center"></i>
                        <span>Business Report</span>
                    </a>
                    <a href="{{ route('summary.view') }}" 
                    class="nav-item transactions-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('summary.view') ? 'active-nav-item' : '' }}">
                        
                        <i class="nav-icon fas fa-file-invoice-dollar w-5 text-center"></i>
                        <span>Summary</span>
                    </a>

                      <a href="{{ route('apis.index') }}" 
                    class="nav-item transactions-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('apis.index') ? 'active-nav-item' : '' }}">
                        
                       <i class="fas fa-plug w-5 text-center"></i>
                        <span>Api Service</span>
                    </a>
                    <!-- Dropdown Parent -->
<div class="nav-item">
    <button onclick="toggleReports()" 
        class="w-full py-3 px-4 flex items-center justify-between rounded-lg">
        
        <div class="flex items-center gap-3">
            <i class="fas fa-file-alt w-5 text-center"></i>
            <span>Reports</span>
        </div>

        <i class="fas fa-chevron-down"></i>
    </button>

    <!-- Dropdown Items -->
    <div id="reportsMenu" class="hidden flex flex-col ml-6 mt-2 space-y-1">

        <a href="{{ route('transaction.admin') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('transaction.admin') ? 'active-nav-item' : '' }}">
            Payout P1
        </a>

        <a href="{{ route('transaction.adminV2') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('transaction.adminV2') ? 'active-nav-item' : '' }}">
            Payout P2
        </a>

        <a href="{{ route('pg.report.admin') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('pg.report.admin') ? 'active-nav-item' : '' }}">
            PG P
        </a>

        <a href="{{ route('pg.report.admin.pipe1') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('pg.report.admin.pipe1') ? 'active-nav-item' : '' }}">
            PG P1
        </a>

        <a href="{{ route('pg.report.admin.pipe2') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('pg.report.admin.pipe2') ? 'active-nav-item' : '' }}">
            PG P2
        </a>

        <a href="{{ route('admin.upi.reports') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.upi.reports') ? 'active-nav-item' : '' }}">
            UPI Payout
        </a>
        <a href="{{ route('admin.upi.v2.reports') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.upi.v2.reports') ? 'active-nav-item' : '' }}">
            UPI Payout P2
        </a>
        <a href="{{ route('admin.dmt.reports') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.dmt.reports') ? 'active-nav-item' : '' }}">
            DMT
        </a>

        <a href="{{ route('admin.aeps.reports') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.aeps.reports') ? 'active-nav-item' : '' }}">
            AEPS
        </a>
        <a href="{{ route('admin.aeps.reports.v2') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.aeps.reports.v2') ? 'active-nav-item' : '' }}">
            AEPSV2
        </a>
        <a href="{{ route('transaction.admin.stlm') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('transaction.admin.stlm') ? 'active-nav-item' : '' }}">
            AEPS STLM
        </a>

        <a href="{{ route('admin.bbps.reports') }}" 
            class="nav-item py-2 px-3 rounded">
            BBPS
        </a>

        <a href="{{ route('admin.refund.reports') }}" 
            class="nav-item py-2 px-3 rounded {{ request()->routeIs('admin.refund.reports') ? 'active-nav-item' : '' }}">
            Refund
        </a>

    </div>
</div>

                 <!-- NEW Menus -->
                    <div class="text-indigo-300 text-xs font-semibold px-4 py-2 uppercase tracking-wider mt-4">Business Management</div>
                    <a href="{{ route('admin.remittances.index') }}" class="nav-item onboard-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('admin.remittances.index') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-building w-5 text-center"></i>
                        <span>Onboard Business</span>
                    </a>

                     <a href="{{ route('remittances.rejected') }}" class="nav-item onboard-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('remittances.rejected') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-building w-5 text-center"></i>
                        <span>Rejected Remittance</span>
                    </a>

                    <a href="{{ route('banks.fund.request') }}" class="nav-item fundrequests-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('banks.fund.request') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-credit-card w-5 text-center"></i>
                        <span>Fund Requests</span>
                    </a>
                    <a href="{{ route('admin.lockTransactionsList') }}" class="nav-item businesslist-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('businesses.index') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-list w-5 text-center"></i>
                        <span>Lock Amount</span>
                    </a>
                    <a href="{{ route('banks.create') }}" class="nav-item banks-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('banks.create') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-landmark w-5 text-center"></i>
                        <span>Banks Details</span>
                    </a>
                     <a href="{{ route('credit.debit.v1') }}" class="nav-item businesslist-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('businesses.index') ? 'active-nav-item' : '' }}">
                        <i class="fas fa-wallet w-5 text-center"></i>
                        <span>Credit/Debit</span>
                    </a>
                     <a href="{{ route('apisSwitch.index') }}" class="nav-item banks-link py-3 px-4 rounded-lg flex items-center gap-3 {{ request()->routeIs('apisSwitch.index') ? 'active-nav-item' : '' }}">
                        <i class="nav-icon fas fa-project-diagram w-5 text-center"></i>
                        <span>API Switch</span>
                    </a>
                </nav>
            </div>
            
            <!-- Logout (fixed at bottom) -->
            <div class="px-4 py-5 border-t border-indigo-600 flex-shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-white text-indigo-700 px-4 py-3 rounded-lg hover:bg-blue-200 font-semibold flex items-center justify-center gap-2 transition-all duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto main-scroll pt-16 md:pt-0 p-4 md:p-6 mt-6">
       <!-- Dashboard Header -->
<div class="rounded-2xl text-white p-6 mb-6 shadow-lg
bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <!-- Left Section -->
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">
                Welcome, {{ auth()->user()->name }} !
            </h1>

            <p class="text-sm opacity-90">
                Financial performance • User metrics • Operational insights
            </p>

            <!-- Balance Box -->
            <div class="mt-4 flex items-center">

                <div class="bg-white/20 backdrop-blur-md rounded-xl px-4 py-3 flex items-center gap-4">
                     <!-- Balance -->
            <div class="balance-card flex items-center gap-2">

    <i class="fa-solid fa-wallet text-white-600"></i>
               <p class="text-sm opacity-90">IP Balance</p>
                        <p class="text-xl font-bold">
                            ₹<span id="ipBalance">0</span>
                        </p>
    {{-- <span class="font-medium">
        IP ₹ <span id="ipBalance">0</span>
    </span> --}}

    <!-- Refresh Button -->
    <button id="refreshBalance" class="text-blue-600 hover:text-blue-800 ml-2">
        <i class="fa-solid fa-rotate"></i>
    </button>

</div>

                    <div>
                        <p class="text-sm opacity-90">Current Balance</p>
                        <p class="text-xl font-bold">
                            ₹{{ auth()->user()->balance }}
                        </p>
                    </div>

                    <a href="{{ route('manul_fund.create') }}"
                       class="bg-white text-indigo-700 px-3 py-2 rounded-lg
                       hover:bg-indigo-100 font-semibold flex items-center gap-1 text-sm transition">

                        <i class="fas fa-plus"></i> Add Fund
                    </a>

                </div>

            </div>

        </div>


        <!-- Right Section -->
        <div class="text-right text-sm opacity-90">
            <p>LAST UPDATED</p>
            <p class="font-semibold">
                {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>

    </div>

</div>
        
        
        <!-- Content Section -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            @yield('content')
        </div>
    </main>

    <script>
        // Toggle mobile sidebar
        const menuToggle = document.getElementById('menu-toggle');
        const menuIcon = document.getElementById('menu-icon');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebar = document.querySelector('.mobile-sidebar');
        const overlay = document.getElementById('overlay');
        
        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('open');
            menuIcon.classList.remove('fa-bars');
            menuIcon.classList.add('fa-times');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeSidebarFunc() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
            menuIcon.classList.remove('fa-times');
            menuIcon.classList.add('fa-bars');
            document.body.classList.remove('overflow-hidden');
        }
        
        menuToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('open')) {
                closeSidebarFunc();
            } else {
                openSidebar();
            }
        });
        
        closeSidebar.addEventListener('click', closeSidebarFunc);
        overlay.addEventListener('click', closeSidebarFunc);
        
        // Close sidebar when a nav item is clicked (on mobile)
        if (window.innerWidth < 768) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', closeSidebarFunc);
            });
        }
        
        // Function to set active tab based on current URL
        function setActiveTab() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active-nav-item');
                }
            });
        }
        setActiveTab();
    </script>

    <script>

function fetchBalance(){

    let btn = document.getElementById('refreshBalance');

    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    // fetch('/instantpay-balance')
    //     .then(res => res.json())
    //     .then(data => {

    //         if(data.status){
    //             document.getElementById('ipBalance').innerText = data.balamce;
    //         }

    //         btn.innerHTML = '<i class="fa-solid fa-rotate"></i>';

    //     })
    //     .catch(() => {

    //         btn.innerHTML = '<i class="fa-solid fa-rotate"></i>';
    //         alert("Balance fetch failed");

    //     });
}

// Manual click
document.getElementById('refreshBalance').addEventListener('click', fetchBalance);


// Auto fetch on page load
window.onload = function(){
    fetchBalance();
};

</script>

<script>
function toggleReports() {
    const menu = document.getElementById('reportsMenu');
    menu.classList.toggle('hidden');
}
</script>
</body>
</html>
