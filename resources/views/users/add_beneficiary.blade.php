
@extends('users.layouts.app')

@section('content')
 @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200">
            ✅ {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
            ❌ {{ session('error') }}
        </div>
    @endif



@extends('users.layouts.app')

@section('content')
<div class=" flex items-center justify-center p-6">
  <div class="bg-white shadow-2xl rounded-2xl w-full max-w-3xl overflow-hidden">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-blue-500 to-blue-700 text-white px-8 py-6 flex items-center justify-between">
      <h2 class="text-2xl font-semibold flex items-center gap-2">
        <i class="fas fa-user-plus"></i> Add Beneficiary
      </h2>
      <a href="{{ route('start_txn') }}" 
         class="text-sm bg-white text-blue-600 px-3 py-1 rounded-lg hover:bg-gray-100 transition flex items-center">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
    </div>

    <!-- Form -->
    <div class="p-8">
      <form action="{{ route('add_beneficiary.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        <input type="hidden" name="reference_key" value="{{ $reference_Key }}">
        
        <!-- Hidden Latitude & Longitude -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

      <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-user mr-1 text-blue-500"></i> Beneficiary Name
          </label>
          <input type="text" name="name" placeholder="Enter full name"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <!-- Account Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-credit-card mr-1 text-blue-500"></i> Account Number
          </label>
          <input type="text" name="account_no" placeholder="Enter account number
          <input type="text" name="account" placeholder="Enter account number"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <!-- Bank Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-university mr-1 text-blue-500"></i> Bank Name
          </label>
          <input type="text" name="bank" placeholder="Enter bank name"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <!-- IFSC Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-code mr-1 text-blue-500"></i> IFSC Code
          </label>
          <input type="text" name="ifsc" placeholder="Enter IFSC code"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <!-- Mobile -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-phone mr-1 text-blue-500 "></i> Beneficiary Mobile
          </label>
          <input type="text" name="bene_phone" placeholder="Enter mobile number"
          <input type="text" name="mobile" placeholder="Enter mobile number"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div>

        <!-- Relation -->
        {{-- <div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            <i class="fas fa-users mr-1 text-blue-500"></i> Relation (Optional)
          </label>
          <input type="text" name="relation" placeholder="E.g. Brother, Friend"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none transition">
        </div> --}}
        </div>

        <!-- Buttons -->
        <div class="col-span-1 md:col-span-2 flex justify-end gap-4 mt-6">
          <button type="reset"
            class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 transition flex items-center">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit"
            class="bg-gradient-to-r from-purple-600 via-blue-500 to-blue-700 text-white px-6 py-2 rounded-lg hover:bg-blue-700 shadow-md transition flex items-center">
            <i class="fas fa-save mr-1"></i> Save Beneficiary
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- ✅ Auto Get Location -->
<script>
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {
        document.getElementById("latitude").value = position.coords.latitude;
        document.getElementById("longitude").value = position.coords.longitude;
      },
      function(error) {
        console.error("Location access denied or unavailable:", error);
      }
    );
  } else {
    console.error("Geolocation is not supported by this browser.");
  }
</script>
@endsection
