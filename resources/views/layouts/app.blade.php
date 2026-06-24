<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - POS Supermarket</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" />
    <style>
        .content-wrapper { padding: 1rem; }
        .card { border: none; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .summary-card { border-left: 4px solid; }
        .summary-card.primary { border-left-color: #0d6efd; }
        .summary-card.success { border-left-color: #198754; }
        .summary-card.warning { border-left-color: #ffc107; }
        .summary-card.info { border-left-color: #0dcaf0; }
        .summary-card.danger { border-left-color: #dc3545; }
        .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #6c757d; }
        .badge-status { font-size: 0.75rem; }
        #scanner video { width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; background: #000; }
    </style>
    @yield('styles')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">
                    @if(Auth::user()->image_url)
                        <img src="{{ Auth::user()->image_url }}" alt=""
                            class="rounded-circle mr-1"
                            style="width:28px;height:28px;object-fit:cover;">
                    @else
                        <i class="fas fa-user-circle mr-1"></i>
                    @endif
                    {{ Auth::user()->name ?? 'Kasir' }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.index') }}" class="dropdown-item"><i class="fas fa-user mr-2"></i>Profil</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            @php $sidebarLogo = App\Models\CompanySetting::instance()->logo; @endphp
            @if($sidebarLogo)
                <img src="{{ asset('storage/' . $sidebarLogo) }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity:0.9">
            @endif
            <span class="brand-text font-weight-bold" style="color:#fff;">{{ App\Models\CompanySetting::instance()->company_name ?? 'POS Supermarket' }}</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @can('master.manage')
                    <li class="nav-item has-treeview {{ request()->routeIs('master.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('master.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Master Data
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('master.categories.index') }}" class="nav-link {{ request()->routeIs('master.categories.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kategori Produk</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('master.products.index') }}" class="nav-link {{ request()->routeIs('master.products.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Produk</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('master.units.index') }}" class="nav-link {{ request()->routeIs('master.units.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Satuan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('master.brands.index') }}" class="nav-link {{ request()->routeIs('master.brands.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Merek</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('master.suppliers.index') }}" class="nav-link {{ request()->routeIs('master.suppliers.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Supplier</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('master.warehouses.index') }}" class="nav-link {{ request()->routeIs('master.warehouses.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Gudang</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('purchasing.manage')
                    <li class="nav-item has-treeview {{ request()->routeIs('purchasing.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('purchasing.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>
                                Pembelian
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('purchasing.orders.index') }}" class="nav-link {{ request()->routeIs('purchasing.orders.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Pesanan Pembelian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('purchasing.receivings.index') }}" class="nav-link {{ request()->routeIs('purchasing.receivings.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Penerimaan Barang</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('purchasing.returns.index') }}" class="nav-link {{ request()->routeIs('purchasing.returns.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Retur Pembelian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('purchasing.requests.index') }}" class="nav-link {{ request()->routeIs('purchasing.requests.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Request Pembelian</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('stock.manage')
                    <li class="nav-item has-treeview {{ request()->routeIs('inventory.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>
                                Inventori
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('inventory.stocks.index') }}" class="nav-link {{ request()->routeIs('inventory.stocks.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stok</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.mutations.index') }}" class="nav-link {{ request()->routeIs('inventory.mutations.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Mutasi Stok</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.opname.index') }}" class="nav-link {{ request()->routeIs('inventory.opname.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stok Opname</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('pos.access')
                    <li class="nav-item has-treeview {{ request()->routeIs('pos.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>
                                Penjualan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('pos.cashier.index') }}" class="nav-link {{ request()->routeIs('pos.cashier.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kasir / POS</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('pos.history.index') }}" class="nav-link {{ request()->routeIs('pos.history.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Riwayat Penjualan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('pos.sales-returns.index') }}" class="nav-link {{ request()->routeIs('pos.sales-returns.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Retur Penjualan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('pos.shifts.index') }}" class="nav-link {{ request()->routeIs('pos.shifts.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kasir & Shift</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @canany(['promotions.manage', 'members.view'])
                    <li class="nav-item has-treeview {{ request()->routeIs('merchandise.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('merchandise.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-percentage"></i>
                            <p>
                                Merchandise
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('promotions.manage')
                            <li class="nav-item">
                                <a href="{{ route('merchandise.promotions.index') }}" class="nav-link {{ request()->routeIs('merchandise.promotions.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Promosi</p>
                                </a>
                            </li>
                            @endcan
                            @can('members.view')
                            <li class="nav-item">
                                <a href="{{ route('merchandise.members.index') }}" class="nav-link {{ request()->routeIs('merchandise.members.*') && !request()->routeIs('merchandise.credits.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Member</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('merchandise.credits.index') }}" class="nav-link {{ request()->routeIs('merchandise.credits.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Piutang Member</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    @can('expenses.manage')
                    <li class="nav-item has-treeview {{ request()->routeIs('finance.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>
                                Keuangan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('finance.expenses.index') }}" class="nav-link {{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Pengeluaran</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('reports.view')
                    <li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Laporan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Penjualan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.purchases') }}" class="nav-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pembelian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.purchase-returns') }}" class="nav-link {{ request()->routeIs('reports.purchase-returns') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Retur Pembelian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.purchase-receivings') }}" class="nav-link {{ request()->routeIs('reports.purchase-receivings') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Penerimaan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.stocks') }}" class="nav-link {{ request()->routeIs('reports.stocks') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stok</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.profit') }}" class="nav-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laba Rugi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.product-margin') }}" class="nav-link {{ request()->routeIs('reports.product-margin') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laba per Produk</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.ppn') }}" class="nav-link {{ request()->routeIs('reports.ppn') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Laporan PPN</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.sales-targets.index') }}" class="nav-link {{ request()->routeIs('reports.sales-targets.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Target Penjualan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.moving-stock') }}" class="nav-link {{ request()->routeIs('reports.moving-stock') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pergerakan Stok</p>
                            </a>
                        </li>
                        </ul>
                    </li>
                    @endcan

                    @can('settings.manage')
                    <li class="nav-item has-treeview {{ request()->routeIs('settings.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Pengaturan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('settings.company.index') }}" class="nav-link {{ request()->routeIs('settings.company.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Profil Perusahaan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.users.index') }}" class="nav-link {{ request()->routeIs('settings.users.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Manajemen User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.gift-cards.index') }}" class="nav-link {{ request()->routeIs('settings.gift-cards.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Gift Card</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('settings.activity-logs.index') }}" class="nav-link {{ request()->routeIs('settings.activity-logs.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Activity Logs</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
            </div>
        @endif
        @yield('content')

        <!-- Barcode Scanner Modal -->
        <div class="modal fade" id="scannerModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title"><i class="fas fa-camera mr-1"></i> Scan Barcode</h6>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="scanner" class="mb-2"></div>
                        <div class="text-center text-muted small mb-2" id="scannerStatus">Arahkan kamera ke barcode</div>
                        <hr>
                        <div class="input-group input-group-sm">
                            <input type="text" id="manualBarcode" class="form-control" placeholder="Atau ketik barcode..." autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="manualBarcodeBtn" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div id="scannerResult" class="mt-2 small"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            Disusun oleh <a href="https://asrar.my.id" target="_blank">overload</a>
        </div>
        <strong>&copy; {{ date('Y') }} POS Supermarket.</strong> All rights reserved.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Konfirmasi',
                text: this.dataset.confirm || 'Apakah Anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    var scannerInstance = null;
    var scannerCallback = null;
    var scannerRunning = false;

    function openScanner(callback) {
        scannerCallback = callback;
        $('#scannerResult').empty();
        $('#manualBarcode').val('');
        $('#scannerModal').modal('show');
        setTimeout(startScanner, 500);
    }

    function startScanner() {
        if (scannerRunning) return;
        var el = document.getElementById('scanner');
        if (!el) return;
        scannerInstance = new Html5Qrcode("scanner");
        scannerRunning = true;
        $('#scannerStatus').text('Mengakses kamera...');
        scannerInstance.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            function(decodedText) {
                $('#scannerStatus').text('Barcode terdeteksi!');
                stopScanner();
                lookupBarcode(decodedText);
            },
            function(errorMessage) {
                // ignore scan errors
            }
        ).catch(function(err) {
            $('#scannerStatus').text('Kamera tidak tersedia. Gunakan input manual.');
            scannerRunning = false;
        });
    }

    function stopScanner() {
        if (scannerInstance) {
            try { scannerInstance.stop(); } catch(e) {}
            scannerInstance = null;
        }
        scannerRunning = false;
    }

    function lookupBarcode(barcode) {
        if (!barcode || !barcode.trim()) return;
        var code = barcode.trim();
        $('#scannerResult').html('<span class="text-muted">Mencari <strong>' + code + '</strong>...</span>');
        $.get('{{ route('api.products.barcode', '') }}/' + encodeURIComponent(code), function(product) {
            if (scannerCallback) {
                scannerCallback(product);
                $('#scannerModal').modal('hide');
            } else {
                $('#scannerResult').html('<span class="text-success">Ditemukan: ' + product.name + '</span>');
                setTimeout(function() { $('#scannerModal').modal('hide'); }, 800);
            }
        }).fail(function() {
            $('#scannerResult').html('<span class="text-danger">Produk dengan barcode <strong>' + code + '</strong> tidak ditemukan</span>');
        });
    }

    $(document).ready(function() {
        $('#scannerModal').on('hidden.bs.modal', function() {
            stopScanner();
        });

        $('#manualBarcode').on('keypress', function(e) {
            if (e.which == 13) {
                lookupBarcode($(this).val());
            }
        });

        $('#manualBarcodeBtn').on('click', function() {
            lookupBarcode($('#manualBarcode').val());
        });
    });
</script>
@yield('scripts')
</body>
</html>