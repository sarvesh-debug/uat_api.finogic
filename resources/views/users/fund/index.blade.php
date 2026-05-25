


@extends('users.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-4 flex items-center p-4 text-green-800 rounded-lg bg-green-100 shadow-lg" role="alert">
            <i class="fa-solid fa-circle-check"></i> <span class="ml-2">{{ session('success') }}</span>
            <button type="button" class="ml-auto text-green-600 hover:text-green-900" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center p-4 text-red-800 rounded-lg bg-red-100 shadow-lg" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i> <span class="ml-2">{{ session('error') }}</span>
            <button type="button" class="ml-auto text-red-600 hover:text-red-900" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
    @if ($errors->any())
    <div class="mb-4 p-4 rounded-lg bg-yellow-100 text-yellow-800 shadow-lg" role="alert">
        <div class="flex items-center">
            <i class="fa-solid fa-triangle-exclamation"></i> <span class="ml-2 font-semibold">Please fix the following:</span>
            <button type="button" class="ml-auto text-yellow-600 hover:text-yellow-900" onclick="this.parentElement.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <ul class="mt-2 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="flex justify-center">
        <div class="w-full max-w-3xl bg-white shadow-xl rounded-2xl overflow-hidden border border-blue-200">

            <!-- Card Header -->
            <div class=" text-white text-center py-5 shadow-md" style="background-color: #433BC4">
                <h4 class="text-lg sm:text-xl font-semibold"><i class="fa-solid fa-wallet mr-1"></i> Add Fund</h4>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-5">
                <form action="{{ route('remittances.add.fund.post') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Bank Select -->
                    <div>
                        <label for="bankSelect" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-university mr-1"></i> Select Bank Account</label>
                        <select id="bankSelect" name="bank" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" selected disabled>-- Choose Bank --</option>
                            @foreach ($bankDetails as $bank)
                                <option value="{{ $bank->id }}"
                                    data-ifsc="{{ $bank->ifsc }}"
                                    data-account-no="{{ $bank->account_no }}"
                                    data-charges="{{ $bank->charges ?? '' }}"
                                    data-tds="{{ $bank->tds ?? '' }}"
                                    data-transaction-type="{{ $bank->transaction_type ?? '' }}">
                                    {{ $bank->bank_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Auto Fill Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="ifscField" class="hidden">
                            <label class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-code-branch mr-1"></i> IFSC Code</label>
                            <input type="text" id="ifscInput" name="ifsc" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>
                        <div id="accountNoField" class="hidden">
                            <label class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-hashtag mr-1"></i> Account Number</label>
                            <input type="text" id="accountNoInput" name="account_no" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="chargesField" class="hidden">
                            <label class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-coins mr-1"></i> Charges % (₹)</label>
                            <input type="text" id="chargesInput" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                            <small id="txnTypeText" class="text-gray-500 hidden"><i class="fa-solid fa-exchange-alt mr-1"></i> Transaction Type: <span id="txnTypeValue"></span></small>
                        </div>
                        <div id="tdsField" class="hidden">
                            <label class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-file-invoice-dollar mr-1"></i> TDS % (₹)</label>
                            <input type="text" id="tdsInput" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amountInput" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-money-bill-wave mr-1"></i> Amount</label>
                        <input type="number" id="amountInput" name="amount" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- UTR -->
                    <div>
                        <label for="utrInput" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-key mr-1"></i> Transaction ID / UTR</label>
                        <input type="text" id="utrInput" name="utr" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="dateInput" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-calendar-alt mr-1"></i> Date</label>
                        <input type="date" id="dateInput" name="date" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Mode -->
                    <div>
                        <label for="modeSelect" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-exchange-alt mr-1"></i> Mode of Transaction</label>
                        <select id="modeSelect" name="mode" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" selected disabled>-- Select Mode --</option>
                            <option value="IMPS">IMPS</option>
                            <option value="NEFT">NEFT</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>

                    <!-- Upload Slip -->
                    <div>
                        <label for="slipImage" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-file-upload mr-1"></i> Upload Slip Images</label>
                        <input type="file" id="slipImage" name="slip_image"  required class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <!-- Remark -->
                    <div>
                        <label for="remarkInput" class="block text-gray-700 font-medium mb-1"><i class="fa-solid fa-comment mr-1"></i> Remark</label>
                        <input type="text" id="remarkInput" name="remark" placeholder="Optional remark" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit" class="w-full hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-300" style="background-color:#433BC4;">
                            <i class="fa-solid fa-paper-plane mr-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
document.getElementById('bankSelect').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];

    const ifsc = selectedOption.getAttribute('data-ifsc');
    const accountNo = selectedOption.getAttribute('data-account-no');
    const charges = selectedOption.getAttribute('data-charges');
    const tds = selectedOption.getAttribute('data-tds');
    const txnType = selectedOption.getAttribute('data-transaction-type');

    if (ifsc) { document.getElementById('ifscField').classList.remove('hidden'); document.getElementById('ifscInput').value = ifsc; }
    if (accountNo) { document.getElementById('accountNoField').classList.remove('hidden'); document.getElementById('accountNoInput').value = accountNo; }
    if (charges) { document.getElementById('chargesField').classList.remove('hidden'); document.getElementById('chargesInput').value = charges; }
    if (tds) { document.getElementById('tdsField').classList.remove('hidden'); document.getElementById('tdsInput').value = tds; }
    if (txnType) { document.getElementById('txnTypeText').classList.remove('hidden'); document.getElementById('txnTypeValue').textContent = txnType; }
});
</script>
@endsection
