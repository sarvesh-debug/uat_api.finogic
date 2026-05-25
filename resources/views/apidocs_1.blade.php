@extends('users.layouts.app')

@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>

<style>
.page-sidebar{
    width:260px;
    min-height:calc(100vh - 80px);
    border-right:1px solid #f1f1f1;
}

.category-link{
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 14px; 
    border-radius:10px;
    color:#4b5563;
    transition:all .2s ease;
    position:relative;
}

.category-link i{
    width:20px;
    text-align:center;
}

.category-link:hover{
    background:#f3f4f6;
    color:#111827;
}

.category-link.active{
    background:#eef2ff;
    color:#4f46e5;
    font-weight:700;
}

.category-link.active::before{
    content:"";
    position:absolute;
    left:-16px;
    top:8px;
    bottom:8px;
    width:4px;
    border-radius:4px;
    background:#4f46e5;
}

.api-card{
    background:#fff;
    border:1px solid #f1f1f1;
    border-radius:14px;
    transition:.3s ease;
}
.api-card:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 35px rgba(0,0,0,0.08);
}
</style>

<div class="flex gap-6">

    <!-- SIDEBAR -->
    <div class="page-sidebar hidden lg:block bg-white rounded-xl p-4">

        <h3 class="font-semibold text-gray-800 mb-5 text-xl uppercase tracking-wide">
            Categories
        </h3>

        <nav class="space-y-2 text-lg">

            <!-- My Services -->
            <div class="category-link" data-category="myservices">
                <i class="fa-solid fa-briefcase"></i>
                <span>My Services</span>
            </div>

            @foreach($categories as $key => $cat)
                <div class="category-link" data-category="{{ $key }}">
                    <i class="{{ $cat['icon'] }}"></i>
                    <span>{{ $cat['name'] }}</span>
                </div>
            @endforeach

        </nav>
    </div>

    <!-- RIGHT CONTENT -->
    <div class="flex-1">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Developer APIs</h2>

            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <i class="fa fa-search absolute right-3 top-3 text-gray-400 text-xs"></i>
            </div>
        </div>

        <!-- SERVICE GRID -->
        <div id="serviceContainer" class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

            @foreach($categories as $key => $cat)
              @foreach($cat['services'] as $service)

    @php
        $flag = $serviceMap[$service['title']] ?? null;
        $enabled = $flag && isset($myservices->$flag) && $myservices->$flag == 1;
    @endphp

    <div class="api-card p-5 service-card"
         data-category="{{ $key }}"
         data-myservice="{{ $enabled ? '1' : '0' }}"
         data-title="{{ strtolower($service['title']) }}">

        <div class="flex justify-between items-center mb-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-code text-indigo-600"></i>
            </div>

            @if($service['status']=='active')
                <span class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-600 font-medium">
                    ● Active
                </span>
            @endif
        </div>

        <h4 class="font-semibold text-gray-800 mb-2">
            {{ $service['title'] }}
        </h4>

        <p class="text-sm text-gray-500 mb-4">
            {{ $service['desc'] }}
        </p>

        <div class="flex gap-2">
            <button class="flex-1 border border-gray-200 rounded-lg py-2 text-sm hover:bg-gray-50">
                Settings
            </button>

           <a href="{{ route('developer.service.docs', ['slug' => Str::slug($service['title'])]) }}"
class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
View Docs
</a>
        </div>

    </div>

@endforeach
            @endforeach

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const categoryLinks = document.querySelectorAll('.category-link');
    const serviceCards = document.querySelectorAll('.service-card');
    const searchInput = document.getElementById('searchInput');

    function filterCategory(category) {
        serviceCards.forEach(card => {

            if(category === 'myservices'){
                card.style.display = card.dataset.myservice === '1' ? 'block' : 'none';
            } else {
                card.style.display = card.dataset.category === category ? 'block' : 'none';
            }

        });

        categoryLinks.forEach(link => link.classList.remove('active'));
        document.querySelector(`[data-category="${category}"]`)?.classList.add('active');
    }

    if(categoryLinks.length > 0){
        filterCategory(categoryLinks[0].dataset.category);
    }

    categoryLinks.forEach(link => {
        link.addEventListener('click', function(){
            filterCategory(this.dataset.category);
        });
    });

    searchInput.addEventListener('keyup', function(){
        const value = this.value.toLowerCase();
        serviceCards.forEach(card => {
            if(card.style.display !== 'none'){
                card.style.display = card.dataset.title.includes(value) ? 'block' : 'none';
            }
        });
    });

});
</script>

@endsection