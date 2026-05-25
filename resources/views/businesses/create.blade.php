{{-- @extends('./layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Onboard New Business</h2>

        <form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">Domain Name</label>
                <input type="text" name="domain_name" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Business ID</label>
                <input type="text" name="business_id" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Business Email</label>
                <input type="email" name="business_email" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Logo</label>
                <input type="file" name="logo" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Favicon</label>
                <input type="file" name="favicon" class="w-full border rounded p-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">City</label>
                    <input type="text" name="city" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">PIN</label>
                    <input type="text" name="pin" class="w-full border rounded p-2">
                </div>
            </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Sidebar Color</label>
                <input type="color" name="sidebar_color" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Icon Color</label>
                <input type="color" name="icon_color" class="w-full border rounded p-2">
            </div>
        </div>


            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Save Business
            </button>
        </form>
    </div>
@endsection --}}









@extends('./layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-gray-50 p-6 min-h-screen">

    <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center">Business Details</h2>

    <div class="bg-white shadow-2xl rounded-2xl p-6">
        <!-- Top section: Logo + Name -->
        <div class="flex flex-col md:flex-row items-center md:items-start md:justify-between border-b pb-6 mb-6">
            <div class="flex items-center space-x-4">
                <img src="{{ asset('img/profile.jpg') }}" alt="Logo" class="h-24 w-24 rounded-3xl">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">John Doe</h3>
                    <p class="text-gray-500">CEO</p>
                    <p class="text-gray-500 mt-1">example.com</p>
                </div>
            </div>
            <!-- Action buttons -->
            <div class="flex space-x-3 mt-4 md:mt-0">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">Approve</button>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">Reject</button>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">View</button>
            </div>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-500 font-medium">Business ID</p>
                <p class="mt-1 text-gray-800">BUS123</p>
            </div>

            <div>
                <p class="text-gray-500 font-medium">Business Email</p>
                <p class="mt-1 text-gray-800">contact@example.com</p>
            </div>

            <div>
                <p class="text-gray-500 font-medium">City</p>
                <p class="mt-1 text-gray-800">Mumbai</p>
            </div>

            <div>
                <p class="text-gray-500 font-medium">PIN</p>
                <p class="mt-1 text-gray-800">400001</p>
            </div>

            <div>
                <p class="text-gray-500 font-medium">Sidebar Color</p>
                <div class="mt-1 w-16 h-8 rounded border" style="background-color: #5c42e7"></div>
            </div>

            <div>
                <p class="text-gray-500 font-medium">Icon Color</p>
                <div class="mt-1 w-16 h-8 rounded border" style="background-color: #ffffff"></div>
            </div>

            <div class="col-span-2">
                <p class="text-gray-500 font-medium">Logo</p>
                <img src="{{ asset('img/aarpiz-logo.png') }}" alt="Logo" class="mt-2 h-24 rounded-lg shadow-md">
            </div>

            <div class="col-span-2">
                <p class="text-gray-500 font-medium">Favicon</p>
                <img src="{{ asset('img/favicon.png') }}" alt="Favicon" class="mt-2 h-16 rounded shadow-sm">
            </div>
        </div>

        <!-- Optional footer note -->
        <div class="mt-6 text-gray-500 text-sm">
            Status: <span class="font-medium text-gray-800">Pending Approval</span>
        </div>
    </div>
</div>
@endsection

