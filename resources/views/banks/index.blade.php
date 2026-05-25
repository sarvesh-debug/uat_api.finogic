{{-- @extends('layouts.app')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Left: Add Bank Form -->
    <div class="w-full">
        <div class="bg-gradient-to-r from-red-100 to-red-50 shadow-lg rounded-xl border-t-4 border-red-500">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <i class="bx bxs-bank text-red-500 text-3xl mr-3"></i>
                    <h5 class="text-2xl font-bold text-red-600">Add New Bank</h5>
                </div>

                <!-- Success Message -->
                @if(session('msg'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4 border-l-4 border-green-500">
                    {!! session('msg') !!}
                </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4 border-l-4 border-red-500">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('banks.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <!-- Bank Name -->
                    <div>
                        <label class="block font-medium text-gray-700">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                               placeholder="Enter Bank Name"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Account Holder Name -->
                    <div>
                        <label class="block font-medium text-gray-700">Account Holder</label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name') }}"
                               placeholder="Enter Account Holder Name"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Account Number -->
                    <div>
                        <label class="block font-medium text-gray-700">Account Number</label>
                        <input type="text" name="account_no" value="{{ old('account_no') }}"
                               placeholder="Enter Account Number"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- IFSC -->
                    <div>
                        <label class="block font-medium text-gray-700">IFSC Code</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc') }}"
                               placeholder="Enter IFSC Code"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Status -->
                    <div class="col-span-2">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-400 focus:outline-none">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="col-span-2">
                        <button type="submit"
                                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition-all duration-300">
                            Save Bank
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Bank List Table -->
    <div class="w-full">
        <div class="bg-gradient-to-r from-blue-100 to-blue-50 shadow-lg rounded-xl border-t-4 border-blue-500">
            <div class="p-6">
                <h5 class="text-2xl font-bold text-blue-600 mb-4 flex items-center">
                    <i class="bx bxs-bank mr-2"></i>Bank List
                </h5>
                <div class="overflow-x-auto rounded-lg">
                    <table class="min-w-full border border-gray-200 divide-y divide-gray-200 text-sm bg-white shadow-md rounded-lg">
                        <thead class="bg-blue-100 text-blue-800 font-semibold">
                        <tr>
                            <th class="px-4 py-2 text-left">Bank Name</th>
                            <th class="px-4 py-2 text-left">Account No</th>
                            <th class="px-4 py-2 text-left">IFSC</th>
                            <th class="px-4 py-2 text-left">Holder Name</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($banks as $bank)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-4 py-2">{{ $bank->bank_name }}</td>
                            <td class="px-4 py-2">{{ $bank->account_no }}</td>
                            <td class="px-4 py-2">{{ $bank->ifsc }}</td>
                            <td class="px-4 py-2">{{ $bank->account_holder_name }}</td>
                            <td class="px-4 py-2">
                                @if($bank->status)
                                    <span class="text-green-600 font-semibold">Active</span>
                                @else
                                    <span class="text-red-600 font-semibold">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex space-x-3">
                <button onclick="openEditModal({{ $bank->id }}, '{{ $bank->bank_name }}', '{{ $bank->account_no }}', '{{ $bank->ifsc }}', '{{ $bank->account_holder_name }}', '{{ $bank->status }}')"
                        class="text-blue-500 hover:text-blue-700">
                   ✏️
                </button>
                <form action="{{ route('banks.destroy', $bank->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700">
                          🗑️
                    </button>   
                </form>
            </td>

                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Edit Modal (Improved UX) -->
<div id="editModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-blue-600">Edit Bank</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editId">

            <div class="grid grid-cols-1 gap-4">
                <input type="text" id="editBankName" name="bank_name" placeholder="Bank Name" class="border rounded-lg px-3 py-2 w-full">
                <input type="text" id="editAccountNo" name="account_no" placeholder="Account Number" class="border rounded-lg px-3 py-2 w-full">
                <input type="text" id="editIfsc" name="ifsc" placeholder="IFSC Code" class="border rounded-lg px-3 py-2 w-full">
                <input type="text" id="editHolder" name="account_holder_name" placeholder="Account Holder Name" class="border rounded-lg px-3 py-2 w-full">
                <select id="editStatus" name="status" class="border rounded-lg px-3 py-2 w-full">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, bankName, accountNo, ifsc, holder, status) {
        document.getElementById("editModal").classList.remove("hidden");
        document.getElementById("editId").value = id;
        document.getElementById("editBankName").value = bankName;
        document.getElementById("editAccountNo").value = accountNo;
        document.getElementById("editIfsc").value = ifsc;
        document.getElementById("editHolder").value = holder;
        document.getElementById("editStatus").value = status.toLowerCase();


        let route = "{{ route('banks.update',':id') }}";
        document.getElementById('editForm').action = route.replace(':id', id);
    }

    function closeEditModal() {
        document.getElementById("editModal").classList.add("hidden");
    }
</script>
@endsection --}}







@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Left: Add Bank Form -->
    <div class="w-full">
        <div class="bg-gradient-to-r from-red-50 to-red-100 shadow-xl rounded-2xl border-t-4 border-blue-800">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <i class="bx bxs-bank text-red-500 text-4xl mr-3"></i>
                    <h5 class="text-2xl font-bold text-blue-800">Add New Bank</h5>
                </div>

                <!-- Success Message -->
                @if(session('msg'))
                    <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4 border-l-4 border-green-500 shadow-sm">
                        {!! session('msg') !!}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 border-l-4 border-red-500 shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('banks.store') }}" 
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <!-- Bank Name -->
                    <div class="col-span-1">
                        <label class="block font-medium text-gray-700">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                               placeholder="Enter Bank Name"
                               class="mt-1 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Account Holder Name -->
                    <div class="col-span-1">
                        <label class="block font-medium text-gray-700">Account Holder</label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name') }}"
                               placeholder="Enter Account Holder Name"
                               class="mt-1 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Account Number -->
                    <div class="col-span-1">
                        <label class="block font-medium text-gray-700">Account Number</label>
                        <input type="text" name="account_no" value="{{ old('account_no') }}"
                               placeholder="Enter Account Number"
                               class="mt-1 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- IFSC -->
                    <div class="col-span-1">
                        <label class="block font-medium text-gray-700">IFSC Code</label>
                        <input type="text" name="ifsc" value="{{ old('ifsc') }}"
                               placeholder="Enter IFSC Code"
                               class="mt-1 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                    </div>

                    <!-- Status -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="status" 
                                class="mt-1 w-full border rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="col-span-1 md:col-span-2">
                        <button type="submit"
                                class="w-full bg-blue-800 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition-all duration-300">
                            Save Bank
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Bank List Table -->
    <div class="w-full">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 shadow-xl rounded-2xl border-t-4 border-blue-800">
            <div class="p-6">
                <h5 class="text-2xl font-bold text-blue-800 mb-4 flex items-center">
                    <i class="bx bxs-bank mr-2 text-3xl"></i> Bank List
                </h5>

                <div class="overflow-x-auto rounded-lg shadow-md">
                    <table class="min-w-full border border-gray-200 divide-y divide-gray-200 text-sm bg-white rounded-lg">
                        <thead class="bg-blue-100 text-blue-800 font-semibold">
                        <tr>
                            <th class="px-4 py-3 text-left">Bank Name</th>
                            <th class="px-4 py-3 text-left">Account No</th>
                            <th class="px-4 py-3 text-left">IFSC</th>
                            <th class="px-4 py-3 text-left">Holder Name</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @forelse($banks as $bank)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-4 py-3 font-medium">{{ $bank->bank_name }}</td>
                            <td class="px-4 py-3">{{ $bank->account_no }}</td>
                            <td class="px-4 py-3">{{ $bank->ifsc }}</td>
                            <td class="px-4 py-3">{{ $bank->account_holder_name }}</td>
                            <td class="px-4 py-3">
                                @if($bank->status === 'active')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Active</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Inactive</span>
                                @endif
                            </td>
                          
                            <td class="px-4 py-3 flex justify-center space-x-4">
                                <button onclick="openEditModal({{ $bank->id }}, '{{ $bank->bank_name }}', '{{ $bank->account_no }}', '{{ $bank->ifsc }}', '{{ $bank->account_holder_name }}', '{{ $bank->status }}')"
                                        class="text-blue-500 hover:text-blue-700 text-lg">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('banks.destroy', $bank->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-lg">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">No banks added yet.</td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 relative">
        <h2 class="text-2xl font-bold mb-4 text-blue-600 flex items-center">
            <i class="bx bxs-edit mr-2 text-2xl"></i> Edit Bank
        </h2>
        <form id="editForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editId">

            <input type="text" id="editBankName" name="bank_name" placeholder="Bank Name" class="border rounded-lg px-3 py-2 w-full shadow-sm">
            <input type="text" id="editAccountNo" name="account_no" placeholder="Account Number" class="border rounded-lg px-3 py-2 w-full shadow-sm">
            <input type="text" id="editIfsc" name="ifsc" placeholder="IFSC Code" class="border rounded-lg px-3 py-2 w-full shadow-sm">
            <input type="text" id="editHolder" name="account_holder_name" placeholder="Account Holder Name" class="border rounded-lg px-3 py-2 w-full shadow-sm">
            
            <select id="editStatus" name="status" class="border rounded-lg px-3 py-2 w-full shadow-sm">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, bankName, accountNo, ifsc, holder, status) {
        document.getElementById("editModal").classList.remove("hidden");
        document.getElementById("editId").value = id;
        document.getElementById("editBankName").value = bankName;
        document.getElementById("editAccountNo").value = accountNo;
        document.getElementById("editIfsc").value = ifsc;
        document.getElementById("editHolder").value = holder;
        document.getElementById("editStatus").value = status.toLowerCase();

        let route = "{{ route('banks.update',':id') }}";
        document.getElementById('editForm').action = route.replace(':id', id);
    }

    function closeEditModal() {
        document.getElementById("editModal").classList.add("hidden");
    }
</script>
@endsection


