
@extends('users.layouts.app')

@section('content')
<div class="bg-gray-100 p-6">

    <!-- Flash Messages -->
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>aarpiz | Beneficiaries</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-6">

  <!-- Business Profile Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
      <h3 class="text-sm text-gray-500"><i class="fa-solid fa-phone text-blue-500 mr-2"></i>Mobile Number</h3>
      <p class="text-md font-semibold text-gray-800">{{ $data['phone'] }}</p>

      <h3 class="text-sm text-gray-500 mt-3"><i class="fa-solid fa-user text-green-500 mr-2"></i>Name</h3>
      <p class="text-md font-semibold text-gray-800">{{ $data['remittance_name'] }}</p>

      <h3 class="text-sm text-gray-500 mt-3"><i class="fa-solid fa-id-card text-purple-500 mr-2"></i>RT No</h3>
      <p class="text-md font-semibold text-gray-800">{{ $data['remittance_id'] }}</p>
      <p class="text-md font-semibold text-gray-800">9621122159</p>

      <h3 class="text-sm text-gray-500 mt-3"><i class="fa-solid fa-user text-green-500 mr-2"></i>Name</h3>
      <p class="text-md font-semibold text-gray-800">ZENKLICK SOLUTION PRIVATE LIMITED</p>

      <h3 class="text-sm text-gray-500 mt-3"><i class="fa-solid fa-id-card text-purple-500 mr-2"></i>PAN No</h3>
      <p class="text-md font-semibold text-gray-800">IEKPP9632F</p>
    </div>

    <div class="bg-white p-5 rounded-xl shadow hover:shadow-lg transition">
      <h3 class="text-sm text-gray-500"><i class="fa-solid fa-arrow-trend-up text-orange-500 mr-2"></i>Today Transaction</h3>
      <div class="flex gap-4 mt-3">
        <div class="flex items-center gap-2 bg-green-100 px-4 py-2 rounded-lg text-green-700 font-semibold shadow-sm">
          <i class="fa-solid fa-indian-rupee-sign"></i> 100.00
        </div>
        <div class="flex items-center gap-2 bg-blue-100 px-4 py-2 rounded-lg text-blue-700 font-semibold shadow-sm">
          <i class="fa-solid fa-indian-rupee-sign"></i> 1066.60
        </div>
      </div>
    </div>
  </div>

  <!-- Search + Add Beneficiary -->
  <div class="flex items-center justify-between mb-4">
    <div class="relative w-1/2">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
      <input id="searchInput" type="text" placeholder="Search beneficiary..."
        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring focus:ring-blue-300 outline-none shadow-sm">
    </div>

    <a href="{{ route('add_beneficiary', ['reference_Key' => $data['reference_key']]) }}">
      <button class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg ml-2 shadow">
        <i class="fa-solid fa-user-plus"></i> Add Beneficiary
      </button>
    </a>
   
   <a href="{{route('add_beneficiary')}}"> <button class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg ml-2 shadow">
      <i class="fa-solid fa-user-plus"></i> Add Beneficiary
    </button> </a>
  </div>

  <!-- Beneficiaries Table -->
  <div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Account</th>
          <th class="px-4 py-3">Bank</th>
          <th class="px-4 py-3">IFSC</th>
          <th class="px-4 py-3">Beneficiary Mobile</th>
          <th class="px-4 py-3 text-center">Send Money</th>
          <th class="px-4 py-3 text-center">Delete</th>
        </tr>
      </thead>
      <tbody id="beneficiariesTable" class="divide-y"></tbody>
    </table>
  </div>

  <!-- ✅ Send Money Modal -->
  <div id="sendModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 relative">
      <h2 class="text-xl font-bold mb-4">Send Money</h2>

      <form method="POST" action="{{ route('send_money')  }}">
        @csrf
        <input type="hidden" name="apikey" value="123456">

        <!-- TxnAmount Editable -->
        <div class="mb-3">
          <label class="block text-gray-600">Txn Amount</label>
          <input type="number" name="txnAmount" class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-300" required>
        </div>

        <!-- Readonly + hidden fields -->
        <div class="mb-3">
          <label class="block text-gray-600">Mobile No</label>
          <input type="text" id="modalMobile" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
          <input type="hidden" name="mobileNo" id="hiddenMobile">
        </div>

        <div class="mb-3">
          <label class="block text-gray-600">Account No</label>
          <input type="text" id="modalAccount" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
          <input type="hidden" name="accountNo" id="hiddenAccount">
        </div>

        <div class="mb-3">
          <label class="block text-gray-600">IFSC</label>
          <input type="text" id="modalIfsc" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
          <input type="hidden" name="ifscCode" id="hiddenIfsc">
        </div>

        <div class="mb-3">
          <label class="block text-gray-600">Bank</label>
          <input type="text" id="modalBank" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
          <input type="hidden" name="bankName" id="hiddenBank">
        </div>

        <div class="mb-3">
          <label class="block text-gray-600">Holder Name</label>
          <input type="text" id="modalHolder" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
          <input type="hidden" name="accountHolderName" id="hiddenHolder">
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 mt-6">
          <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">Proceed</button>
        </div>
      </form>

      <button type="button" onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
  </div>
</div>

<!-- ✅ Scripts -->
<script>
  const beneficiaries = @json($data['beneficiaries']);
  const tableBody = document.getElementById("beneficiariesTable");
  const searchInput = document.getElementById("searchInput");

  function renderTable(data) {
    tableBody.innerHTML = "";
    data.forEach((b, index) => {
      const row = `
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3">${b.beneName}</td>
          <td class="px-4 py-3">${b.baneAccount}</td>
          <td class="px-4 py-3">${b.baneBankName}</td>
          <td class="px-4 py-3">${b.baneIFSC}</td>
          <td class="px-4 py-3">${b.baneMobileNo}</td>
          <td class="px-4 py-3 text-center">
            <button onclick='openModal(${JSON.stringify(b)})' 
              class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow">
              <i class="fa-solid fa-paper-plane"></i> Send
            </button>
          </td>
          <td class="px-4 py-3 text-center">
            <button onclick="deleteBeneficiary('${b.baneAccount}')"
              class="flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow">
              <i class="fa-solid fa-trash"></i> Delete
            </button>
          </td>
        </tr>
      `;
      tableBody.innerHTML += row;
    });
  }

  renderTable(beneficiaries);

  searchInput.addEventListener("input", (e) => {
    const value = e.target.value.toLowerCase();
    const filtered = beneficiaries.filter(b =>
      b.beneName.toLowerCase().includes(value) ||
      b.baneAccount.includes(value) ||
      b.baneBankName.toLowerCase().includes(value) ||
      b.baneIFSC.toLowerCase().includes(value) ||
      b.baneMobileNo.includes(value)
    );
    renderTable(filtered);
  });

  function openModal(b) {
    document.getElementById("modalMobile").value = b.baneMobileNo;
    document.getElementById("hiddenMobile").value = b.baneMobileNo;

    document.getElementById("modalAccount").value = b.baneAccount;
    document.getElementById("hiddenAccount").value = b.baneAccount;

    document.getElementById("modalIfsc").value = b.baneIFSC;
    document.getElementById("hiddenIfsc").value = b.baneIFSC;

    document.getElementById("modalBank").value = b.baneBankName;
    document.getElementById("hiddenBank").value = b.baneBankName;

    document.getElementById("modalHolder").value = b.beneName;
    document.getElementById("hiddenHolder").value = b.beneName;

    document.getElementById("sendModal").classList.remove("hidden");
  }

  function closeModal() {
    document.getElementById("sendModal").classList.add("hidden");
  }

  function deleteBeneficiary(account) {
    if(confirm("Are you sure you want to delete this beneficiary?")) {
      alert("Delete API call here for account: " + account);
    }
  }
</script>
@endsection
=======
  <script>
    // Demo JSON data
    const beneficiaries = [
      {
        name: "XYZ ABCD",
        account: "35426005177",
        bank: "Kotak Mahindra Bank",
        ifsc: "KKBKORTGSMI",
        mobile: "9621122159"
      },
      {
        name: "Anshika Tiwari",
        account: "1850215330",
        bank: "Kotak Mahindra Bank",
        ifsc: "KKBK0005040",
        mobile: "9305987447"
      },
      {
        name: "Tejendra Baghel",
        account: "3539810009378",
        bank: "BOB",
        ifsc: "BARBOGOVARD",
        mobile: "7055283212"
      },
       {
        name: "Md Jasim",
        account: "3539810009334",
        bank: "SBI",
        ifsc: "SBI0004664",
        mobile: "7055283215"
      },
       {
        name: "Sarvesh Pal",
        account: "3539810009392",
        bank: "Gramin Bank",
        ifsc: "gramin839",
        mobile: "7055283202"
      }, {
        name: "Tejendra Baghel",
        account: "3539810009378",
        bank: "BOB",
        ifsc: "BARBOGOVARD",
        mobile: "7055283214"
      }
    ];

    const tableBody = document.getElementById("beneficiariesTable");
    const searchInput = document.getElementById("searchInput");

    // Render function
    function renderTable(data) {
      tableBody.innerHTML = "";
      data.forEach(b => {
        const row = `
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">${b.name}</td>
            <td class="px-4 py-3">${b.account}</td>
            <td class="px-4 py-3">${b.bank}</td>
            <td class="px-4 py-3">${b.ifsc}</td>
            <td class="px-4 py-3">${b.mobile}</td>
            <td class="px-4 py-3 text-center">
              <button class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow">
                <i class="fa-solid fa-paper-plane"></i> Send
              </button>
            </td>
            <td class="px-4 py-3 text-center">
              <button class="flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow">
                <i class="fa-solid fa-trash"></i> Delete
              </button>
            </td>
          </tr>
        `;
        tableBody.innerHTML += row;
      });
    }

    // Initial render
    renderTable(beneficiaries);

    // Search filter
    searchInput.addEventListener("input", (e) => {
      const value = e.target.value.toLowerCase();
      const filtered = beneficiaries.filter(b =>
        b.name.toLowerCase().includes(value) ||
        b.account.includes(value) ||
        b.bank.toLowerCase().includes(value) ||
        b.ifsc.toLowerCase().includes(value) ||
        b.mobile.includes(value)
      );
      renderTable(filtered);
    });
  </script>

</body>
</html>
@endsection






{{-- @extends('users.layouts.app')
@section('content')
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>aarpiz | Beneficiary Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
        }
        
        .card {
            transition: all 0.3s ease;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .table-row {
            transition: background-color 0.2s ease;
        }
        
        .table-row:hover {
            background-color: #f8f9fa;
        }
        
        .search-box:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Beneficiary Management</h1>
            <p class="text-gray-600 mt-2">Manage your beneficiaries and make transfers easily</p>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-xl card">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Balance</p>
                        <p class="text-2xl font-bold mt-1">₹1066.60</p>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm bg-white bg-opacity-20 px-2 py-1 rounded-full">Available</span>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl card">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Today's Transactions</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">₹100.00</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-exchange-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs text-gray-500">5 transactions today</span>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl card">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Business Account</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">ZENKLICK SOLUTION</p>
                        <p class="text-xs text-gray-600 mt-1">PAN: IEKPP9632F</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-building text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Info Section -->
        <div class="bg-white p-6 rounded-xl card mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Business Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Mobile Number</p>
                    <p class="font-medium">9621122159</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Business Name</p>
                    <p class="font-medium">ZENKLICK SOLUTION PRIVATE LIMITED</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">PAN Number</p>
                    <p class="font-medium">IEKPP9632F</p>
                </div>
            </div>
        </div>

        <!-- Search and Actions Section -->
        <div class="bg-white p-6 rounded-xl card mb-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative w-full md:w-1/2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search beneficiary by name, account, or bank..."
                        class="search-box pl-10 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-300 outline-none transition">
                </div>
                <button class="bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 text-white font-medium px-5 py-3 rounded-lg flex items-center btn w-full md:w-auto justify-center">
                    <i class="fas fa-plus-circle mr-2"></i> Add Beneficiary
                </button>
            </div>
        </div>

        <!-- Beneficiaries Table Section -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Saved Beneficiaries</h2>
                <p class="text-sm text-gray-600">Your trusted recipients for quick money transfers</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IFSC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="beneficiariesTable">
                        <!-- Table content will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span id="showingCount" class="font-medium">0</span> of <span id="totalCount" class="font-medium">0</span> beneficiaries
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Previous
                        </button>
                        <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Demo data in JSON format
        const beneficiariesData = {
            "beneficiaries": [
                {
                    "id": 1,
                    "name": "XYZ ABCD",
                    "account": "35426005177",
                    "bank": "Kotak Mahindra Bank",
                    "ifsc": "KKBKORTGSMI",
                    "mobile": "9621122159"
                },
                {
                    "id": 2,
                    "name": "Anshika Tiwari",
                    "account": "1850215330",
                    "bank": "Kotak Mahindra Bank",
                    "ifsc": "KKBK0005040",
                    "mobile": "9305987447"
                },
                {
                    "id": 3,
                    "name": "Tejendra Baghel",
                    "account": "3539810009378",
                    "bank": "BOB",
                    "ifsc": "BARBOGOVARD",
                    "mobile": "7055283212"
                },
                {
                    "id": 4,
                    "name": "Sarvosh Pal",
                    "account": "1850208636",
                    "bank": "Kotak Mahindra Bank",
                    "ifsc": "KKBK0005040",
                    "mobile": "9621122159"
                },
                {
                    "id": 5,
                    "name": "Sarvosh Pal",
                    "account": "75105131427",
                    "bank": "BARODA UP BANK",
                    "ifsc": "BARBQBUPGBX",
                    "mobile": "9621122159"
                }
            ]
        };

        // Function to render beneficiaries table
        function renderBeneficiaries(data) {
            const tableBody = document.getElementById('beneficiariesTable');
            tableBody.innerHTML = '';
            
            data.forEach(beneficiary => {
                const row = document.createElement('tr');
                row.className = 'table-row';
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="bg-indigo-100 p-2 rounded-full mr-3">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">${beneficiary.name}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${beneficiary.account}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${beneficiary.bank}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${beneficiary.ifsc}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${beneficiary.mobile}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg flex items-center btn send-btn">
                                <i class="fas fa-paper-plane mr-1 text-xs"></i> Send
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg flex items-center btn delete-btn">
                                <i class="fas fa-trash-alt mr-1 text-xs"></i> Delete
                            </button>
                        </div>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Update counts
            document.getElementById('showingCount').textContent = data.length;
            document.getElementById('totalCount').textContent = beneficiariesData.beneficiaries.length;
        }

        // Function to filter beneficiaries based on search input
        function filterBeneficiaries(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            
            const filteredData = beneficiariesData.beneficiaries.filter(beneficiary => {
                return (
                    beneficiary.name.toLowerCase().includes(searchTerm) ||
                    beneficiary.account.toLowerCase().includes(searchTerm) ||
                    beneficiary.bank.toLowerCase().includes(searchTerm) ||
                    beneficiary.ifsc.toLowerCase().includes(searchTerm) ||
                    beneficiary.mobile.toLowerCase().includes(searchTerm)
                );
            });
            
            renderBeneficiaries(filteredData);
        }

        // Initial render
        document.addEventListener('DOMContentLoaded', function() {
            renderBeneficiaries(beneficiariesData.beneficiaries);
            
            // Add event listener for search input
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                filterBeneficiaries(this.value);
            });
            
            // Add event listeners for buttons (example)
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('send-btn')) {
                    const row = e.target.closest('tr');
                    const name = row.querySelector('.font-medium').textContent;
                    alert(`Send money to ${name} functionality would be implemented here.`);
                }
                
                if (e.target.classList.contains('delete-btn')) {
                    const row = e.target.closest('tr');
                    const name = row.querySelector('.font-medium').textContent;
                    if (confirm(`Are you sure you want to delete ${name} from your beneficiaries?`)) {
                        alert(`${name} would be deleted from beneficiaries.`);
                    }
                }
            });
        });
    </script>
</body>
</html>

@endsection --}}
