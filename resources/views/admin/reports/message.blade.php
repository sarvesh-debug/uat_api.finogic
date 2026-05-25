@extends('layouts.app')

@section('content')

@if(session('success') || session('error'))

<div id="statusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center animate-fadeIn">

        @if(session('success'))

            <img src="https://cdn-icons-png.flaticon.com/512/5610/5610944.png"
                 class="mx-auto w-16 mb-3">

            <h3 class="text-lg font-semibold text-green-600">
                {{ session('success') }}
            </h3>

        @elseif(session('error'))

            <img src="https://cdn-icons-png.flaticon.com/512/463/463612.png"
                 class="mx-auto w-16 mb-3">

            <h3 class="text-lg font-semibold text-red-600">
                {{ session('error') }}
            </h3>

        @endif

        <button id="closeBtn"
            class="mt-6 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition">
            Close
        </button>

    </div>

</div>

@endif


<script>

document.addEventListener("DOMContentLoaded", function () {

    const closeBtn = document.getElementById('closeBtn');

    if(closeBtn){
        closeBtn.addEventListener("click", function(){
            window.location.href = "{{ route('admin.refund.reports') }}";
        });
    }

});

</script>

@endsection