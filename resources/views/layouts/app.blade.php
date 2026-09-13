<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kantong Jamu</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <link rel="icon" href="{{ asset('images/LOGO KAJA.webp') }}" type="image/webp">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/ea9f10c59f.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

    <style>

    *{
        font-family:'Poppins',sans-serif;
    }

    body{
        background:#f3f7f2;
        overflow-x:hidden;
    }

    .mobile-header,
    .sidebar-overlay{
        display:none;
    }

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    .sidebar{
        width:290px;
        height:100vh;
        position:fixed;
        background:linear-gradient(
            180deg,
            #14532d,
            #0f172a
        );
        padding:30px 20px;
        color:white;
        border-top-right-radius:30px;
        border-bottom-right-radius:30px;
        box-shadow:10px 0 40px rgba(0,0,0,0.08);
        z-index:100;
        transition:transform .25s ease;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    .logo-box{
        background:rgba(255,255,255,0.12);
        backdrop-filter:blur(10px);
        border:1px solid rgba(255,255,255,0.1);
    }

    /*
    |--------------------------------------------------------------------------
    | MENU
    |--------------------------------------------------------------------------
    */

    .menu a{
        display:flex;
        align-items:center;
        gap:14px;
        padding:16px 18px;
        border-radius:18px;
        text-decoration:none;
        color:white;
        margin-bottom:12px;
        transition:all .3s ease;
        font-weight:500;
        opacity:.85;
    }

    .menu a:hover{
        background:rgba(255,255,255,0.12);
        transform:translateX(6px);
        opacity:1;
    }

    .active-menu{
        background:linear-gradient(
            90deg,
            rgba(255,255,255,0.20),
            rgba(255,255,255,0.08)
        );
        box-shadow:
            0 10px 20px rgba(0,0,0,0.12),
            inset 0 1px 1px rgba(255,255,255,0.1);
        opacity:1 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN
    |--------------------------------------------------------------------------
    */

    .main{
        margin-left:290px;
        padding:35px;
        width:calc(100% - 290px);
        min-width:0;
        max-width:calc(100vw - 290px);
    }

    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:18px;
        margin-bottom:32px;
    }

    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .card{
        background:white;
        border-radius:28px;
        padding:24px;
        max-width:100%;
        box-shadow:
            0 10px 30px rgba(0,0,0,0.04);
        border:1px solid rgba(0,0,0,0.03);
    }

    .choices{
        min-width:0;
        max-width:100%;
    }

    .choices__inner{
        max-width:100%;
        min-height:58px !important;
        display:flex !important;
        align-items:center !important;
    }

    .choices__list--single{
        padding:0 !important;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    .choices__list--dropdown,
    .choices__list[aria-expanded]{
        border:none !important;
        border-radius:16px !important;
        box-shadow:0 18px 45px rgba(15,23,42,.14) !important;
        min-width:100% !important;
        width:max-content !important;
        max-width:min(420px, calc(100vw - 40px)) !important;
        z-index:200 !important;
    }

    .choices__list--dropdown .choices__item,
    .choices__list[aria-expanded] .choices__item{
        white-space:nowrap !important;
        word-break:normal !important;
        padding:12px 16px !important;
    }

    .inventory-filter-select .choices{
        width:280px !important;
        min-width:280px !important;
    }

    .inventory-entry-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:18px 20px;
        align-items:start;
    }

    .inventory-entry-field{
        display:flex;
        min-width:0;
        flex-direction:column;
    }

    .inventory-entry-field > input,
    .inventory-entry-field > select,
    .inventory-entry-field > .choices{
        width:100% !important;
        min-width:0 !important;
    }

    .inventory-entry-field .choices{
        margin-bottom:0;
    }

    .inventory-money-input{
        display:grid;
        grid-template-columns:30px minmax(0,1fr);
        align-items:center;
        width:100%;
        min-width:0;
        gap:8px;
    }

    .inventory-money-input input{
        width:100%;
        min-width:0;
    }

    .inventory-entry-submit{
        grid-column:span 2;
        width:100%;
        min-height:58px;
        align-self:end;
    }

    .distribution-filter-actions{
        display:grid;
        grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);
        gap:12px;
        align-items:center;
    }

    .distribution-filter-button{
        display:flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:56px;
        padding:13px 22px;
        border:0;
        border-radius:16px;
        font-weight:700;
        line-height:1.2;
        text-align:center;
        text-decoration:none;
        cursor:pointer;
        transition:transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .distribution-filter-button:hover{
        transform:translateY(-1px);
    }

    .distribution-filter-apply{
        background:#16a34a;
        color:#fff;
        box-shadow:0 8px 18px rgba(22,163,74,.18);
    }

    .distribution-filter-reset{
        min-width:150px;
        background:#eef2f4;
        color:#475569;
    }

    .distribution-filter-export{
        background:#ef4444;
        color:#fff;
        box-shadow:0 8px 18px rgba(239,68,68,.16);
    }

    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    input,
    select,
    .choices__inner{
        background:#f5f7fb !important;
        border:none !important;
        border-radius:18px !important;
        padding:16px !important;
        transition:.3s;
    }

    input:focus,
    select:focus{
        outline:none !important;
        box-shadow:0 0 0 4px rgba(34,197,94,0.15);
    }

    .date-field,
    .date-field .flatpickr-input,
    .flatpickr-wrapper{
        width:100% !important;
        min-width:0 !important;
        display:block !important;
    }

    input[type="date"],
    input.flatpickr-input{
        width:100% !important;
        min-width:0 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    .btn-green{
        background:linear-gradient(
            135deg,
            #22c55e,
            #16a34a
        );
        color:white;
        padding:14px 24px;
        border-radius:18px;
        font-weight:600;
        transition:.3s;
        border:none;
        box-shadow:0 10px 20px rgba(34,197,94,0.20);
    }

    .btn-green:hover{
        transform:translateY(-2px);
        box-shadow:0 15px 25px rgba(34,197,94,0.30);
    }

    .btn-red{
        background:linear-gradient(
            135deg,
            #ef4444,
            #dc2626
        );
        color:white;
        padding:12px 20px;
        border-radius:16px;
        border:none;
        font-weight:600;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    table{
        border-collapse:separate;
        border-spacing:0 12px;
    }

    thead tr{
        color:#64748b;
    }

    tbody tr{
        background:#fff;
        transition:.3s;
    }

    tbody tr:hover{
        transform:scale(1.01);
    }

    tbody td{
        padding:16px 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */

    .badge-success{
        background:#dcfce7;
        color:#16a34a;
        padding:10px 18px;
        border-radius:999px;
        font-weight:600;
        display:inline-block;
    }

    .badge-warning{
        background:#fef3c7;
        color:#d97706;
        padding:10px 18px;
        border-radius:999px;
        font-weight:600;
        display:inline-block;
    }

    .badge-danger{
        background:#fee2e2;
        color:#dc2626;
        padding:10px 18px;
        border-radius:999px;
        font-weight:600;
        display:inline-block;
    }

    .flatpickr-calendar{
        border:none !important;
        border-radius:18px !important;
        box-shadow:0 18px 45px rgba(15,23,42,.16) !important;
        overflow:hidden;
        font-family:'Poppins',sans-serif;
    }

    .flatpickr-months{
        padding:10px 8px 4px;
    }

    .flatpickr-current-month{
        display:block !important;
        padding-top:7px !important;
        height:34px !important;
        line-height:1 !important;
        left:12.5% !important;
        width:75% !important;
        text-align:center !important;
    }

    .flatpickr-current-month .numInputWrapper{
        display:inline-block !important;
        width:6ch !important;
        height:28px !important;
        vertical-align:top !important;
    }

    .flatpickr-current-month input.cur-year{
        background:transparent !important;
        border:none !important;
        border-radius:8px !important;
        box-shadow:none !important;
        display:inline-block !important;
        height:28px !important;
        min-height:28px !important;
        padding:0 4px !important;
        width:6ch !important;
        font-size:16px !important;
        font-weight:600;
        text-align:center;
        vertical-align:top !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months{
        background:transparent !important;
        border:none !important;
        border-radius:8px !important;
        box-shadow:none !important;
        display:inline-block !important;
        height:28px !important;
        min-height:28px !important;
        line-height:28px !important;
        margin:0 4px 0 0 !important;
        padding:0 22px 0 6px !important;
        width:auto !important;
        font-size:16px !important;
        font-weight:600;
        vertical-align:top !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months:hover,
    .flatpickr-current-month input.cur-year:hover{
        background:#f1f5f9 !important;
    }

    .flatpickr-current-month .arrowUp,
    .flatpickr-current-month .arrowDown{
        display:none !important;
    }

    .flatpickr-weekdays{
        height:36px !important;
    }

    span.flatpickr-weekday{
        color:#64748b !important;
        font-size:12px !important;
        font-weight:700 !important;
    }

    .flatpickr-days,
    .dayContainer{
        width:307px !important;
        min-width:307px !important;
        max-width:307px !important;
    }

    .flatpickr-day{
        width:39px !important;
        max-width:39px !important;
        height:39px !important;
        line-height:39px !important;
        border-radius:12px !important;
        margin:2px !important;
        font-size:13px;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange{
        background:#22c55e !important;
        border-color:#22c55e !important;
    }

    .flatpickr-today-footer{
        border-top:1px solid #eef2f7;
        padding:10px;
        text-align:center;
        background:#fff;
    }

    .flatpickr-today-button{
        background:#22c55e;
        color:white;
        border:none;
        border-radius:12px;
        padding:8px 18px;
        font-weight:700;
        cursor:pointer;
    }

    @media(max-width:1024px){
        .main{
            padding:24px;
        }

        .card{
            border-radius:22px;
            padding:20px;
        }

        .inventory-entry-grid{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }

        .inventory-entry-submit{
            grid-column:span 1;
        }
    }

    @media(max-width:900px){
        .mobile-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            position:sticky;
            top:0;
            z-index:90;
            padding:12px 16px;
            background:#0f2b20;
            color:white;
            box-shadow:0 10px 30px rgba(15,23,42,.16);
        }

        .mobile-header button{
            width:46px;
            height:46px;
            border:0;
            border-radius:14px;
            background:rgba(255,255,255,.14);
            color:white;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0;
            line-height:1;
            cursor:pointer;
        }

        .mobile-header button svg{
            width:24px;
            height:24px;
            stroke:currentColor;
            stroke-width:2.6;
            stroke-linecap:round;
        }

        .mobile-header img{
            width:170px;
            max-height:46px;
            object-fit:contain;
            background:white;
            border-radius:12px;
            padding:8px;
        }

        .sidebar{
            width:min(82vw,310px);
            transform:translateX(-110%);
            border-radius:0 26px 26px 0;
            overflow-y:auto;
        }

        .sidebar.open{
            transform:translateX(0);
        }

        .sidebar-overlay{
            display:none;
            position:fixed;
            inset:0;
            background:rgba(15,23,42,.42);
            z-index:80;
        }

        .sidebar-overlay.open{
            display:block;
        }

        .main{
            margin-left:0;
            width:100%;
            max-width:100vw;
            padding:18px;
        }

        .topbar{
            flex-direction:column;
            align-items:flex-start;
            gap:14px;
            margin-bottom:22px;
        }

        .topbar h1{
            font-size:32px;
            line-height:1.1;
        }

        .topbar > .card{
            width:100%;
            justify-content:flex-start;
        }

        .card{
            border-radius:18px;
            padding:18px;
        }

        table{
            border-spacing:0 8px;
        }

        tbody tr:hover{
            transform:none;
        }

        input,
        select,
        .choices__inner{
            min-height:52px !important;
            padding:13px !important;
        }

        .btn-green{
            width:100%;
            min-height:52px;
        }

        .inventory-filter-select{
            width:100%;
        }

        .inventory-filter-select .choices{
            width:100% !important;
            min-width:100% !important;
        }
    }

    @media(max-width:640px){
        .main{
            padding:14px;
        }

        .card{
            padding:16px;
        }

        .inventory-entry-grid{
            grid-template-columns:minmax(0,1fr);
            gap:16px;
        }

        .distribution-filter-actions{
            grid-template-columns:minmax(0,1fr);
            gap:10px;
        }

        .distribution-filter-reset{
            min-width:0;
        }

        .topbar h1{
            font-size:28px;
        }

        .logo-box{
            margin-bottom:28px !important;
        }

        .menu a{
            padding:13px 15px;
            margin-bottom:8px;
        }

        .flatpickr-calendar{
            transform:scale(.94);
            transform-origin:top left;
        }
    }

</style>

</head>

<body>

<div class="mobile-header">
    <button type="button" id="mobileMenuButton" aria-label="Open menu">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 7h16"></path>
            <path d="M4 12h16"></path>
            <path d="M4 17h16"></path>
        </svg>
    </button>

    <img src="{{ asset('images/kantong-jamu-logo.png') }}" alt="Kantong Jamu">
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-shell flex">

    <!-- SIDEBAR -->

    <div class="sidebar" id="sidebar">

        <div class="logo-box flex items-center gap-4 mb-12 p-4 rounded-3xl">

            <img
            src="{{ asset('images/kantong-jamu-logo.png') }}"
            alt="Kantong Jamu"
            class="w-52 bg-white p-3 rounded-2xl">

            <div class="sr-only">

                <h1 class="text-2xl font-bold">
                    Kantong Jamu
                </h1>

                <p class="text-sm opacity-80">
                    Internal System
                </p>

            </div>

        </div>

        <!-- MENU -->

        <div class="menu">

            <!-- DASHBOARD -->

            @if(auth()->user()->role == 'admin')

            <a href="/dashboard"
            class="{{ request()->is('dashboard') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-house"></i>

                Dashboard

            </a>

            @endif

            <!-- PRODUKSI -->

            <a href="/produksi"
            class="{{ request()->is('produksi') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-flask"></i>

                Production

            </a>

            <!-- MENU ADMIN -->

            @if(auth()->user()->role == 'admin')

            <a href="/qc"
            class="{{ request()->is('qc') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-circle-check"></i>

                QC Approval

            </a>

            <a href="/inventory"
            class="{{ request()->is('inventory') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-box"></i>

                Inventory

            </a>

            <a href="/distribution"
            class="{{ request()->is('distribution') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-truck"></i>

                Distribution

            </a>

            @endif

            <!-- PAYROLL -->

            <a href="/payroll"
            class="{{ request()->is('payroll') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-money-bill-wave"></i>

                Payroll

            </a>

            @if(auth()->user()->role == 'admin')

            <a href="/activity-log"
            class="{{ request()->is('activity-log') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-clock-rotate-left"></i>

                Activity Log

            </a>

            <a href="{{ route('workers.index') }}"
            class="{{ request()->is('workers*') ? 'active-menu' : '' }}">

                <i class="fa-solid fa-users-gear"></i>

                Manage Workers

            </a>

            @endif

        </div>

    </div>

    <!-- MAIN -->

    <div class="main">

        <!-- TOPBAR -->

        <div class="topbar">

            <div>

                <h1 class="text-4xl font-bold text-gray-800">
                    @yield('title')
                </h1>

                <p class="text-gray-500 mt-2">
                    Kantong Jamu Internal System
                </p>

            </div>

            <div class="card flex items-center gap-4">

                <img
                src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
                class="w-12 h-12 rounded-full object-cover">

                <div>

                    <h4 class="font-semibold">

                        {{ Auth::user()->name }}

                    </h4>

                    <small class="text-gray-500">

                        @if(auth()->user()->role == 'admin')

                            Administrator

                        @else

                            Worker

                        @endif

                    </small>

                    <div class="mt-2">
                        <a href="/logout" class="text-red-500 text-sm font-semibold">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a>
                    </div>

                </div>

            </div>

        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 text-red-700 px-5 py-4 rounded-2xl font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-100 text-green-700 px-5 py-4 rounded-2xl font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
const mobileMenuButton = document.getElementById('mobileMenuButton');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function closeMobileSidebar() {
    sidebar?.classList.remove('open');
    sidebarOverlay?.classList.remove('open');
}

mobileMenuButton?.addEventListener('click', function () {
    sidebar?.classList.toggle('open');
    sidebarOverlay?.classList.toggle('open');
});

sidebarOverlay?.addEventListener('click', closeMobileSidebar);

document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', closeMobileSidebar);
});

document.querySelectorAll('input[type="date"]').forEach(input => {
    flatpickr(input, {
        dateFormat: 'Y-m-d',
        allowInput: true,
        onReady: function(selectedDates, dateStr, instance) {
            if (instance.calendarContainer.querySelector('.flatpickr-today-footer')) {
                return;
            }

            const footer = document.createElement('div');
            footer.className = 'flatpickr-today-footer';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flatpickr-today-button';
            button.textContent = 'Today';

            button.addEventListener('click', function () {
                instance.setDate(new Date(), true);
                instance.close();
            });

            footer.appendChild(button);
            instance.calendarContainer.appendChild(footer);
        }
    });
});

document.querySelectorAll('select:not(.flatpickr-monthDropdown-months):not(.native-select)').forEach(select => {
    new Choices(select, {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false
    });
});
</script>

</body>
</html>
