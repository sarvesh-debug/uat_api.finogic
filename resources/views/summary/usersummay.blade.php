@extends('users.layouts.app')

@section('content')

<div class="p-6">

    <!-- HEADER -->
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Summary Report</h2>

    <!-- SUMMARY -->
    @if(isset($data['summary']))
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <p class="text-sm text-gray-500">Transactions</p>
            <h3 class="text-xl font-bold">{{ $data['summary']['total_transactions'] }}</h3>
        </div>

        <div class="bg-green-100 shadow rounded-xl p-4 text-center">
            <p class="text-sm">IN</p>
            <h3 class="text-xl font-bold text-green-700">₹ {{ $data['summary']['total_in_amount'] }}</h3>
        </div>

        <div class="bg-red-100 shadow rounded-xl p-4 text-center">
            <p class="text-sm">OUT</p>
            <h3 class="text-xl font-bold text-red-700">₹ {{ $data['summary']['total_out_amount'] }}</h3>
        </div>

        <div class="bg-gray-100 shadow rounded-xl p-4 text-center">
            <p class="text-sm">Charges</p>
            <h3 class="text-xl font-bold">₹ {{ $data['summary']['total_charges'] }}</h3>
        </div>

        <div class="bg-yellow-100 shadow rounded-xl p-4 text-center">
            <p class="text-sm">TDS</p>
            <h3 class="text-xl font-bold text-yellow-700">₹ {{ $data['summary']['total_tds'] }}</h3>
        </div>

        <div class="bg-purple-100 shadow rounded-xl p-4 text-center">
            <p class="text-sm">Commission</p>
            <h3 class="text-xl font-bold text-purple-700">₹ {{ $data['summary']['total_commission'] }}</h3>
        </div>
        <!-- ✅ STATUS SUMMARY ADD-ON -->
<div class="bg-blue-100 shadow rounded-xl p-4 text-center">
    <p class="text-sm">Success</p>
    <h3 class="text-xl font-bold text-blue-700">
        ₹ {{ $data['summary']['success_amount'] ?? 0 }}
    </h3>
</div>

<div class="bg-yellow-200 shadow rounded-xl p-4 text-center">
    <p class="text-sm">Pending</p>
    <h3 class="text-xl font-bold text-yellow-700">
        ₹ {{ $data['summary']['pending_amount'] ?? 0 }}
    </h3>
</div>

<div class="bg-red-200 shadow rounded-xl p-4 text-center">
    <p class="text-sm">Failed</p>
    <h3 class="text-xl font-bold text-red-700">
        ₹ {{ $data['summary']['failed_amount'] ?? 0 }}
    </h3>
</div>

{{-- <div class="bg-purple-200 shadow rounded-xl p-4 text-center">
    <p class="text-sm">Refunded</p>
    <h3 class="text-xl font-bold text-purple-700">
        ₹ {{ $data['summary']['refunded_amount'] ?? 0 }}
    </h3>
</div> --}}
    </div>
    @endif


    <!-- FILTER FORM -->
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-7 gap-3 mb-6">

        <input type="hidden" name="filter" id="filterInput" value="{{ request('filter') }}">

        <input type="date" id="fromDate" name="from_date" value="{{ request('from_date') }}" class="border px-3 py-2 rounded">
        <input type="date" id="toDate" name="to_date" value="{{ request('to_date') }}" class="border px-3 py-2 rounded">
        <input type="text" name="txn_id" placeholder="Txn ID" value="{{ request('txn_id') }}" class="border px-3 py-2 rounded">
        <input type="text" name="service" placeholder="Service" value="{{ request('service') }}" class="border px-3 py-2 rounded">
        <input type="hidden" name="mer_id" placeholder="Merchant ID" value="{{$merId}}" class="border px-3 py-2 rounded">

        <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2">Search</button>
        <a id="exportBtn" href="#" class="bg-green-600 text-white px-4 py-2 rounded">
    Export Excel
</a>
    </form>
    

    <!-- QUICK FILTER -->
    <div class="flex gap-2 mb-6">

        <button type="button" onclick="quickFilter('today')" class="bg-gray-200 px-3 py-1 rounded">Today</button>

        <button type="button" onclick="quickFilter('yesterday')" class="bg-gray-200 px-3 py-1 rounded">Yesterday</button>

        <button type="button" onclick="quickFilter('this_month')" class="bg-gray-200 px-3 py-1 rounded">This Month</button>

        <a type="button" href="{{ route('summary.view') }}" onclick="resetFilter()" class="bg-red-500 text-white px-3 py-1 rounded">Reset</a>

    </div>


    <!-- SERVICE SUMMARY -->
    @if(isset($data['service_summary']))
    <div class="bg-white shadow rounded-xl p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3">Service Summary</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">Service</th>
                        <th class="px-4 py-2">Txn</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Charges</th>
                        <th class="px-4 py-2">Success</th>
                        <th class="px-4 py-2">Pending</th>
                        <th class="px-4 py-2">Failed</th>
                        <th class="px-4 py-2">Refunded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['service_summary'] as $s)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $s['service_name'] }}</td>
                        <td class="px-4 py-2">{{ $s['total_transactions'] }}</td>
                        <td class="px-4 py-2">₹ {{ $s['total_amount'] }}</td>
                        <td class="px-4 py-2">₹ {{ $s['total_charges'] }}</td>
                        <td class="px-4 py-2 text-green-600">₹ {{ $s['success_amount'] ?? 0 }}</td>
                        <td class="px-4 py-2 text-yellow-600">₹ {{ $s['pending_amount'] ?? 0 }}</td>
                        <td class="px-4 py-2 text-red-600">₹ {{ $s['failed_amount'] ?? 0 }}</td>
                        <td class="px-4 py-2 text-purple-600">₹ {{ $s['refunded_amount'] ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif


    <!-- TABLE -->
    <div class="bg-white shadow rounded-xl p-4">
        <div class="overflow-x-auto">

            <table class="min-w-full text-sm text-left">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <!-- <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">RemID</th> -->
                        <th class="px-4 py-2">Txn</th>
                        <th class="px-4 py-2">RefNo</th>
                        <th class="px-4 py-2">Service</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Opening</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Charges</th>
                        <th class="px-4 py-2">Closing</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    @foreach($data['data']['data'] ?? [] as $row)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $row['created_at'] }}</td>
                        <!-- <td class="px-4 py-2">{{ $row['name'] }}</td>
                        <td class="px-4 py-2 text-blue-600">{{ $row['remId'] }}</td> -->
                        <td class="px-4 py-2">{{ $row['txn_id'] }}</td>
                        <td class="px-4 py-2">{{ $row['ref_id'] }}</td>
                        <td class="px-4 py-2">{{ $row['service_name'] }}</td>
                        <td class="px-4 py-2 {{ $row['type']=='IN'?'text-green-600':'text-red-600' }}">
                            {{ $row['type'] }}
                        </td>
                        <td class="px-4 py-2">
                            @php $status = strtoupper($row['status'] ?? 'PENDING'); @endphp

                            <span class="
                                px-2 py-1 rounded text-xs font-semibold
                                {{ $status == 'SUCCESS' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $status == 'PENDING' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $status == 'FAILED' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $status == 'REFUNDED' ? 'bg-purple-100 text-purple-700' : '' }}
                            ">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-4 py-2">₹ {{ $row['opening_balance'] }}</td>
                        <td class="px-4 py-2 font-bold">₹ {{ $row['amount'] }}</td>
                        <td class="px-4 py-2 text-red-500">₹ {{ $row['charges'] }}</td>
                        <td class="px-4 py-2 text-green-600">₹ {{ $row['closing_balance'] }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <div class="mt-6 flex justify-between">
            <div id="info" class="text-sm text-gray-600">
                Showing {{ $data['data']['from'] ?? 0 }} to {{ $data['data']['to'] ?? 0 }}
                of {{ $data['data']['total'] ?? 0 }}
            </div>

            <div id="pagination" class="flex space-x-1">
                @foreach($data['data']['links'] ?? [] as $link)
                    @php
                        preg_match('/page=(\d+)/', $link['url'] ?? '', $m);
                        $page = $m[1] ?? null;
                    @endphp

                    @if($link['url'])
                        <button data-page="{{ $page }}" class="pagination-btn px-3 py-1 bg-gray-200 rounded">
                            {!! $link['label'] !!}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>

// ✅ QUICK FILTER FIX
function quickFilter(type){
    document.getElementById('filterInput').value = type;
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    document.getElementById('filterForm').submit();
}

// ✅ RESET FIX
function resetFilter(){
    document.getElementById('filterInput').value = '';
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';

    document.querySelector('input[name="txn_id"]').value = '';
    document.querySelector('input[name="service"]').value = '';
    document.querySelector('input[name="mer_id"]').value = '';

    document.getElementById('filterForm').submit();
}

// ✅ PAGINATION AJAX (FILTER KE SAATH)
document.addEventListener('click', function(e){

    if(e.target.closest('.pagination-btn')){

        let page = e.target.closest('.pagination-btn').dataset.page;

        let formData = new URLSearchParams(new FormData(document.getElementById('filterForm')));
        formData.set('page', page);

        fetch("https://uatapi.aarpiz.com/api/v1/all/summary?" + formData.toString())
        .then(res => res.json())
        .then(res => {

            renderTable(res.data.data);
            renderPagination(res.data);
            renderInfo(res.data);

        });
    }

});

function renderTable(rows){
    let html = '';

    rows.forEach(row => {
        html += `
        <tr class="border-b">
            <td class="px-4 py-2">${row.created_at}</td>
           
            <td class="px-4 py-2">${row.txn_id}</td>
            <td class="px-4 py-2">${row.service_name}</td>
            <td class="px-4 py-2 ${row.type=='IN'?'text-green-600':'text-red-600'}">${row.type}</td>
            <td class="px-4 py-2">
                <span class="
                    px-2 py-1 rounded text-xs font-semibold
                    ${row.status=='SUCCESS' ? 'bg-green-100 text-green-700' : ''}
                    ${row.status=='PENDING' ? 'bg-yellow-100 text-yellow-700' : ''}
                    ${row.status=='FAILED' ? 'bg-red-100 text-red-700' : ''}
                    ${row.status=='REFUNDED' ? 'bg-purple-100 text-purple-700' : ''}
                ">
                    ${row.status ?? 'PENDING'}
                </span>
            </td>
                        <td class="px-4 py-2">₹ ${row.opening_balance}</td>
            <td class="px-4 py-2 font-bold">₹ ${row.amount}</td>
            <td class="px-4 py-2 text-red-500">₹ ${row.charges}</td>
            <td class="px-4 py-2 text-green-600">₹ ${row.closing_balance}</td>
        </tr>`;
    });

    document.getElementById('tableBody').innerHTML = html;
}

function renderPagination(data){
    let html = '';

    data.links.forEach(link => {
        let page = link.url ? (link.url.match(/page=(\d+)/) || [])[1] : null;

        if(link.url){
            html += `<button data-page="${page}" class="pagination-btn px-3 py-1 bg-gray-200 rounded">
                ${link.label}
            </button>`;
        }
    });

    document.getElementById('pagination').innerHTML = html;
}

function renderInfo(data){
    document.getElementById('info').innerText =
        `Showing ${data.from} to ${data.to} of ${data.total}`;
}

</script>

<script>

document.getElementById('exportBtn').addEventListener('click', function(e){
    e.preventDefault();

    let formData = new URLSearchParams(new FormData(document.getElementById('filterForm')));
    
    let url = "https://uatapi.aarpiz.com/api/summary/export?" + formData.toString();

    window.open(url, '_blank');
});

</script>

@endsection