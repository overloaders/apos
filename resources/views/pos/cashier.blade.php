<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir / POS - POS Supermarket</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { overflow: hidden; height: 100vh; }
        .pos-layout { display: flex; height: 100vh; }
        .pos-products { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .pos-search { padding: 1rem; background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .pos-product-grid { flex: 1; overflow-y: auto; padding: 1rem; }
        .product-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 0.75rem; cursor: pointer; transition: all 0.2s; height: 100%; max-width: 100%; overflow: hidden; }
        .product-card:hover { border-color: #0d6efd; box-shadow: 0 2px 8px rgba(13,110,253,0.15); }
        .product-card .product-img { height: 60px; display: flex; align-items: center; justify-content: center; }
        .product-card .product-name { font-weight: 600; font-size: 0.85rem; margin-bottom: 0.25rem; }
        .product-card .product-price { color: #0d6efd; font-weight: 700; font-size: 0.95rem; }
        .product-card .product-stock { font-size: 0.75rem; color: #6c757d; }
        .pos-cart { width: 420px; background: #fff; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; }
        .cart-header { padding: 1rem; background: #0d6efd; color: #fff; }
        .cart-items { flex: 1; overflow-y: auto; padding: 0.5rem; }
        .cart-item { display: flex; align-items: center; padding: 0.5rem; border-bottom: 1px solid #f0f0f0; gap: 0.5rem; }
        .cart-item .item-name { flex: 1; font-size: 0.85rem; }
        .cart-item .item-price { font-weight: 600; font-size: 0.85rem; white-space: nowrap; }
        .cart-item .qty-control { display: flex; align-items: center; gap: 0.25rem; }
        .cart-item .qty-control button { width: 28px; height: 28px; border: 1px solid #dee2e6; background: #fff; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .cart-item .qty-control button:hover { background: #e9ecef; }
        .cart-footer { padding: 1rem; background: #f8f9fa; border-top: 2px solid #dee2e6; }
        .cart-footer .total-row { display: flex; justify-content: space-between; padding: 0.3rem 0; font-size: 0.9rem; }
        .cart-footer .grand-total { font-size: 1.3rem; font-weight: 700; color: #0d6efd; border-top: 2px solid #0d6efd; padding-top: 0.5rem; margin-top: 0.5rem; }
        .btn-pay { background: #198754; border-color: #198754; color: #fff; font-weight: 700; font-size: 1.1rem; padding: 0.75rem; border-radius: 8px; width: 100%; }
        .btn-pay:hover { background: #157347; border-color: #157347; color: #fff; }
        .category-tabs { display: flex; gap: 0.5rem; padding: 0.5rem 1rem; overflow-x: auto; background: #fff; border-bottom: 1px solid #e9ecef; }
        .category-tabs .btn { white-space: nowrap; font-size: 0.8rem; }
        #barcodeInput { font-size: 1.1rem; font-weight: 600; letter-spacing: 1px; }

        @media (max-width: 991.98px) {
            body { overflow: auto; height: auto; }
            .pos-layout { flex-direction: column; height: auto; min-height: 100vh; }
            .pos-products { min-height: 60vh; padding-bottom: 60px; }
            .pos-cart { width: 100%; border-left: none; border-top: 2px solid #dee2e6; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1030; max-height: 75vh; overflow: hidden; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
            .pos-cart.collapsed { transform: translateY(calc(100% - 48px)); }
            .cart-toggle-btn { position: fixed; bottom: 60px; right: 16px; z-index: 1040; display: none; width: 56px; height: 56px; border-radius: 50%; background: #0d6efd; color: #fff; border: none; box-shadow: 0 4px 12px rgba(13,110,253,0.4); font-size: 1.2rem; cursor: pointer; align-items: center; justify-content: center; }
            .cart-toggle-btn .badge { position: absolute; top: -4px; right: -4px; font-size: 0.7rem; }
            .cart-toggle-btn.show { display: flex; }
            .cart-toggle-btn.hide-when-open { display: none !important; }
            .cart-items { flex: 1; overflow-y: auto; min-height: 0; }
            .cart-header, .cart-footer { flex-shrink: 0; }
            .cart-footer .grand-total { font-size: 1.1rem; }
            .pos-product-grid { padding-bottom: 1rem; }
            .btn-pay { font-size: 1rem; padding: 0.6rem; }
            .pos-search .row > div { margin-bottom: 0.5rem; }
            .pos-search .row > div:last-child { margin-bottom: 0; }
            .product-card { padding: 0.5rem; }
            .product-card .product-name { font-size: 0.75rem; }
            .product-card .product-price { font-size: 0.85rem; }
            #memberInfo .card-body .row .col-sm-6 { margin-bottom: 0.25rem; }
        }
        
        @media (max-width: 575.98px) {
            .pos-cart { max-height: 80vh; }
            .cart-items { flex: 1; overflow-y: auto; min-height: 0; }
            .cart-item { flex-wrap: wrap; gap: 0.25rem; }
            .cart-item .item-name { flex: 0 0 100%; order: -1; margin-bottom: 0.25rem; }
            .cart-item .qty-control { flex: 1; }
            .cart-item .item-price { flex: 0 0 auto; }
            .cart-footer { padding: 0.75rem; }
            .cart-footer .total-row { font-size: 0.8rem; }
            .cart-footer .grand-total { font-size: 1rem; }
            .category-tabs .btn { font-size: 0.7rem; padding: 0.2rem 0.5rem; }
            #barcodeInput { font-size: 1rem; }
        }

        #scanner video { width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; background: #000; }
        #paymentModal .modal-body { max-height: 60vh; overflow-y: auto; }
        @media (max-width: 575.98px) {
            #paymentModal .modal-body { max-height: 55vh; }
        }
    </style>
</head>
<body>
    <div class="pos-layout">
        <div class="pos-products">
            <div class="pos-search">
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control" id="barcodeInput" placeholder="Scan Barcode..." autofocus>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="openScanner(function(p) { addToCart(p.id, p.name, p.selling_price, p.stock || 0, p.member_price || 0); })" title="Scan via Kamera">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchProduct" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="memberInput" placeholder="Cari nama/kode/telepon member..." autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="openMemberSearch()" title="Cari Member">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div id="memberInfo" class="mt-2 card border-primary" style="display:none;">
                            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-id-card mr-1"></i> <strong id="memberName"></strong></span>
                                <span id="memberLevelBadge" class="badge badge-light"></span>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Kode Member</small>
                                        <span class="font-weight-bold" id="memberCode"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Telepon</small>
                                        <span class="font-weight-bold" id="memberPhone"></span>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Diskon Member</small>
                                        <span class="text-success font-weight-bold" id="memberDiscountLabel" style="font-size:1rem;"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Poin</small>
                                        <span class="font-weight-bold text-info" id="memberPointsLabel" style="font-size:1rem;"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer py-1 text-right">
                                <button class="btn btn-sm btn-outline-danger" onclick="removeMember()">
                                    <i class="fas fa-times mr-1"></i>Hapus Member
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="category-tabs">
                <button class="btn btn-primary btn-sm active" onclick="filterCategory('')">Semua</button>
                <button class="btn btn-outline-primary btn-sm" onclick="filterCategory('makanan')">Makanan</button>
                <button class="btn btn-outline-primary btn-sm" onclick="filterCategory('minuman')">Minuman</button>
                <button class="btn btn-outline-primary btn-sm" onclick="filterCategory('rumah-tangga')">Rumah Tangga</button>
                <button class="btn btn-outline-primary btn-sm" onclick="filterCategory('kesehatan')">Kesehatan</button>
                <button class="btn btn-outline-primary btn-sm" onclick="filterCategory('kecantikan')">Kecantikan</button>
            </div>
            <div class="pos-product-grid">
                <div class="row g-2" id="productGrid">
                    @foreach($products ?? [] as $product)
                        @php 
                            $productStock = $product->stocks->sum('total_stock') ?? 0;
                            $imgUrl = $product->image ? asset('storage/' . $product->image) : null;
                        @endphp
                        <div class="col-xl-3 col-md-4 col-sm-6" data-category="{{ $product->category->slug ?? '' }}">
                        <div class="product-card" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $productStock }}, {{ $product->member_price ?? 0 }})">
                                @if($imgUrl)
                                    <div class="product-img text-center mb-1">
                                        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" style="max-height:60px;max-width:100%;object-fit:contain;">
                                    </div>
                                @endif
                                <div class="product-name">{{ \Illuminate\Support\Str::limit($product->name, 30) }}</div>
                                @php $promo = $promoByProduct[$product->id] ?? null; @endphp
                                @if($promo)
                                    <div class="product-stock text-danger font-weight-bold">Diskon {{ $promo['type'] == 'discount_percent' ? $promo['value'] . '%' : 'Rp ' . number_format($promo['value'], 0, ',', '.') }}</div>
                                @else
                                    <div class="product-stock">Stok: {{ $productStock }}</div>
                                @endif
                                <div class="product-price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                                @if($product->member_price)
                                    <div class="product-stock text-success font-weight-bold" style="font-size:0.7rem;">Member: Rp {{ number_format($product->member_price, 0, ',', '.') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    <div class="pos-cart" id="posCart">
        <div class="cart-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-shopping-cart mr-2"></i><strong>Keranjang</strong>
                <span class="badge badge-light ml-1" id="cartCountMobile">0</span>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-light d-md-none mr-1" onclick="toggleCart()" title="Sembunyikan">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <button class="btn btn-sm btn-outline-light" onclick="clearCart()">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </div>
        </div>
            <div class="cart-items" id="cartItems">
                <div class="text-center text-muted py-5" id="emptyCart">
                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block opacity-25"></i>
                    <p>Keranjang kosong</p>
                </div>
            </div>
            <div class="cart-footer">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span id="subtotal">Rp 0</span>
                </div>
                <div class="total-row">
                    <span>Diskon</span>
                    <span class="text-danger" id="discountLabel">- Rp 0</span>
                </div>
                <div id="memberDiscountInfo" class="total-row" style="font-size:0.8rem;"></div>
                <div class="total-row">
                    <span>Pajak (11%)</span>
                    <span id="tax">Rp 0</span>
                </div>
                <div class="total-row grand-total">
                    <span>TOTAL</span>
                    <span id="grandTotal">Rp 0</span>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-pay" onclick="openPayment()" id="btnPay" disabled>
                        <i class="fas fa-credit-card mr-2"></i>BAYAR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="font-weight-bold fw-semibold">Total Belanja</label>
                        <div class="form-control form-control-lg fw-bold text-primary" id="payTotal" style="font-size:1.5rem;">Rp 0</div>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold fw-semibold">Jumlah Bayar</label>
                        <input type="number" class="form-control form-control-lg" id="payAmount" min="0" oninput="calcChange()">
                    </div>
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <button class="btn btn-outline-primary" onclick="quickPay(50000)">Rp 50.000</button>
                        <button class="btn btn-outline-primary" onclick="quickPay(100000)">Rp 100.000</button>
                        <button class="btn btn-outline-primary" onclick="quickPay(200000)">Rp 200.000</button>
                        <button class="btn btn-outline-primary" onclick="quickPay(500000)">Rp 500.000</button>
                        <button class="btn btn-outline-primary" onclick="quickPay(1000000)">Rp 1.000.000</button>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold fw-semibold">Metode Pembayaran</label>
                        <select class="custom-select" id="payMethod">
                            <option value="cash">Tunai</option>
                            <option value="card">Kartu Debit/Kredit</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3" id="pointsSection" style="display:none;">
                        <label class="font-weight-bold fw-semibold">Tukar Poin</label>
                        <div class="small text-muted mb-1">Tersedia: <span id="memberPointsAvailable">0 poin</span></div>
                        <div class="input-group">
                            <input type="number" class="form-control" id="pointsToRedeem" min="0" value="0" oninput="calcChange()">
                            <div class="input-group-append">
                                <span class="input-group-text">poin (1 poin = Rp 100)</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold fw-semibold mb-0">Gift Card</label>
                            <small id="giftCardStatus" class="text-muted"></small>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="giftCardCode" placeholder="Masukkan kode gift card" oninput="resetGiftCard()">
                            <div class="input-group-append">
                                <button class="btn btn-outline-info" type="button" onclick="checkGiftCard()" id="btnCheckGiftCard">Cek</button>
                            </div>
                        </div>
                        <div id="giftCardDetail" style="display:none;">
                            <div class="small text-muted mb-1">Saldo: <span id="giftCardBalance">Rp 0</span></div>
                            <div class="input-group">
                                <input type="number" class="form-control" id="giftCardAmount" min="0" value="0" oninput="calcChange()">
                                <div class="input-group-append">
                                    <span class="input-group-text">Gunakan Gift Card</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="font-weight-bold fw-semibold">Uang Kembali</label>
                        <div class="form-control form-control-lg fw-bold" id="change" style="font-size:1.3rem;">Rp 0</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-lg fw-bold" onclick="processPayment()" id="btnProcessPay">
                        <i class="fas fa-check mr-1"></i>Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <button class="cart-toggle-btn" id="cartToggle" onclick="toggleCart()" title="Buka Keranjang">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge badge-danger" id="cartToggleCount">0</span>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const promoByProduct = @json($promoByProduct ?? []);
        const company = {!! $companyData ?? '{}' !!};
        let cart = [];
        let discount = 0;
        let selectedMemberId = null;
        let selectedMember = null;

        function addToCart(id, name, price, stock, memberPrice) {
            if (stock <= 0) {
                Swal.fire('Stok Habis', 'Produk ini sedang tidak tersedia', 'warning');
                return;
            }
            var usePrice = (selectedMember && memberPrice > 0) ? memberPrice : price;
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.qty >= stock) {
                    Swal.fire('Stok Tidak Cukup', 'Stok produk ini tidak mencukupi', 'warning');
                    return;
                }
                existing.qty++;
            } else {
                cart.push({ id, name, price: usePrice, memberPrice, regularPrice: price, qty: 1, stock });
            }
            updateCartBadge();
            renderCart();
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (item.qty > item.stock) {
                Swal.fire('Stok Tidak Cukup', '', 'warning');
                item.qty = item.stock;
            }
            updateCartBadge();
            renderCart();
        }

        function updateCartBadge() {
            var qty = cart.reduce(function(s, i) { return s + i.qty; }, 0);
            document.getElementById('cartCountMobile').textContent = qty;
            document.getElementById('cartToggleCount').textContent = qty;
            var btn = document.getElementById('cartToggle');
            if (window.innerWidth <= 991.98) {
                btn.classList.toggle('show', qty > 0);
            }
            if (qty > 0) showCartMobile();
            updateCartToggleVisibility();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            updateCartBadge();
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            Swal.fire({
                title: 'Hapus Keranjang?',
                text: 'Semua item akan dihapus',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                confirmButtonColor: '#dc3545'
            }).then(r => {
                if (r.isConfirmed) {
                    cart = [];
                    discount = 0;
                    document.getElementById('pointsToRedeem').value = 0;
                    updateCartBadge();
                    renderCart();
                }
            });
        }

        function renderCart() {
            updateCartBadge();
            const container = document.getElementById('cartItems');
            if (cart.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-shopping-cart fa-3x mb-3 d-block opacity-25"></i><p>Keranjang kosong</p></div>';
                document.getElementById('btnPay').disabled = true;
                document.getElementById('pointsToRedeem').value = 0;
                document.getElementById('subtotal').textContent = 'Rp 0';
                document.getElementById('discountLabel').textContent = '- Rp 0';
                document.getElementById('tax').textContent = 'Rp 0';
                document.getElementById('grandTotal').textContent = 'Rp 0';
                document.getElementById('memberDiscountInfo').innerHTML = '';
                return;
            }
            container.innerHTML = cart.map(item => {
                const promo = promoByProduct[item.id];
                const promoLabel = promo ? '<small class="text-danger">Diskon ' + (promo.type === 'discount_percent' ? promo.value + '%' : 'Rp ' + Number(promo.value).toLocaleString('id-ID')) + '</small>' : '';
                    var isMemberPrice = selectedMember && item.memberPrice > 0 && item.price === item.memberPrice;
                    var priceLabel = isMemberPrice ? '<small class="text-success font-weight-bold">[Member] Rp ' + item.price.toLocaleString('id-ID') + '</small>' : '<small class="text-muted">Rp ' + item.price.toLocaleString('id-ID') + '</small>';
                    return `
                    <div class="cart-item">
                        <div class="item-name">
                            <div>${item.name}</div>
                            ${priceLabel}
                            ${promoLabel}
                        </div>
                        <div class="qty-control">
                            <button onclick="updateQty(${item.id}, -1)">-</button>
                            <span class="px-2 fw-bold">${item.qty}</span>
                            <button onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                        <div class="item-price">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</div>
                        <button class="btn btn-sm text-danger" onclick="removeFromCart(${item.id})"><i class="fas fa-times"></i></button>
                    </div>`;
                }).join('');
                document.getElementById('btnPay').disabled = false;
                calcTotal();
        }

        function calcTotal() {
            const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            let discountTotal = 0;
            cart.forEach(function(item) {
                const promo = promoByProduct[item.id];
                if (promo) {
                    const itemSubtotal = item.price * item.qty;
                    if (promo.type === 'discount_percent') {
                        discountTotal += itemSubtotal * (promo.value / 100);
                    } else if (promo.type === 'discount_amount') {
                        discountTotal += Math.min(parseFloat(promo.value), itemSubtotal);
                    }
                }
            });
            discount = discountTotal;
            var taxable = subtotal - discount;

            var memberDiscountAmount = 0;
            var pointsDiscount = 0;
            var pointsUsed = 0;

            if (cart.length > 0) {
                if (selectedMember && selectedMember.discount_percent > 0) {
                    memberDiscountAmount = Math.round(taxable * selectedMember.discount_percent / 100);
                }
                var afterMemberDiscount = taxable - memberDiscountAmount;

                pointsUsed = parseInt(document.getElementById('pointsToRedeem') ? document.getElementById('pointsToRedeem').value : 0) || 0;
                if (selectedMember && pointsUsed > 0) {
                    var maxPoints = parseInt(selectedMember.points) || 0;
                    pointsUsed = Math.min(pointsUsed, maxPoints);
                    pointsDiscount = pointsUsed * 100;
                    afterMemberDiscount = Math.max(0, afterMemberDiscount - pointsDiscount);
                }
            } else {
                var afterMemberDiscount = 0;
            }

            const tax = Math.round(afterMemberDiscount * 0.11);
            const total = afterMemberDiscount + tax;
            document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('discountLabel').textContent = '- Rp ' + discount.toLocaleString('id-ID');

            var discountDetails = '';
            if (memberDiscountAmount > 0) {
                discountDetails += '<div class="text-success small">Diskon Member (' + selectedMember.discount_percent + '%): -Rp ' + memberDiscountAmount.toLocaleString('id-ID') + '</div>';
            }
            if (pointsDiscount > 0) {
                discountDetails += '<div class="text-info small">Tukar Poin (' + pointsUsed + ' poin): -Rp ' + pointsDiscount.toLocaleString('id-ID') + '</div>';
            }
            document.getElementById('memberDiscountInfo').innerHTML = discountDetails;

            document.getElementById('tax').textContent = 'Rp ' + tax.toLocaleString('id-ID');
            document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');

            return { total: total, subtotal: subtotal, promoDiscount: discount, memberDiscount: memberDiscountAmount, pointsDiscount: pointsDiscount, pointsUsed: pointsUsed, tax: tax };
        }

        function openPayment() {
            var totals = calcTotal();
            document.getElementById('payTotal').textContent = 'Rp ' + totals.total.toLocaleString('id-ID');
            document.getElementById('payTotal').className = 'form-control form-control-lg font-weight-bold text-primary';
            document.getElementById('payAmount').value = '';
            document.getElementById('change').textContent = 'Rp 0';
            document.getElementById('change').className = 'form-control form-control-lg font-weight-bold';
            resetGiftCard();

            if (selectedMember) {
                document.getElementById('pointsSection').style.display = 'block';
                document.getElementById('memberPointsAvailable').textContent = selectedMember.points + ' poin (Rp ' + (selectedMember.points * 100).toLocaleString('id-ID') + ')';
                document.getElementById('pointsToRedeem').max = selectedMember.points;
                document.getElementById('pointsToRedeem').value = 0;
            } else {
                document.getElementById('pointsSection').style.display = 'none';
                document.getElementById('pointsToRedeem').value = 0;
            }

            $('#paymentModal').modal('show');
            setTimeout(() => document.getElementById('payAmount').focus(), 300);
        }

        function quickPay(amount) {
            document.getElementById('payAmount').value = amount;
            calcChange();
        }

        function calcChange() {
            var totals = calcTotal();
            const paid = parseInt(document.getElementById('payAmount').value) || 0;
            const gcAmount = parseInt(document.getElementById('giftCardAmount').value) || 0;
            const remaining = totals.total - gcAmount;
            const change = paid - Math.max(0, remaining);
            document.getElementById('change').textContent = 'Rp ' + Math.max(0, change).toLocaleString('id-ID');
            document.getElementById('change').className = 'form-control form-control-lg fw-bold ' + (change < 0 ? 'text-danger' : 'text-success');
        }

        let validatedGiftCard = null;

        function checkGiftCard() {
            const code = document.getElementById('giftCardCode').value.trim();
            if (!code) {
                Swal.fire('Masukkan Kode', 'Silakan masukkan kode gift card', 'warning');
                return;
            }
            document.getElementById('btnCheckGiftCard').disabled = true;
            document.getElementById('btnCheckGiftCard').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            fetch('{{ route("pos.cashier.validateGiftCard") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ code })
            })
            .then(r => r.json())
            .then(result => {
                if (result.valid) {
                    validatedGiftCard = result.gift_card;
                    document.getElementById('giftCardDetail').style.display = 'block';
                    document.getElementById('giftCardBalance').textContent = 'Rp ' + result.gift_card.current_balance.toLocaleString('id-ID');
                    document.getElementById('giftCardStatus').innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Gift Card valid</span>';
                    var currentTotal = calcTotal().total;
                    document.getElementById('giftCardAmount').max = result.gift_card.current_balance;
                    document.getElementById('giftCardAmount').value = Math.min(result.gift_card.current_balance, currentTotal);
                    calcChange();
                } else {
                    resetGiftCard();
                    Swal.fire('Gift Card Tidak Valid', result.message || 'Gift card tidak ditemukan', 'error');
                }
            })
            .catch(() => {
                resetGiftCard();
                Swal.fire('Error', 'Gagal memvalidasi gift card', 'error');
            })
            .finally(() => {
                document.getElementById('btnCheckGiftCard').disabled = false;
                document.getElementById('btnCheckGiftCard').innerHTML = 'Cek';
            });
        }

        function resetGiftCard() {
            validatedGiftCard = null;
            document.getElementById('giftCardDetail').style.display = 'none';
            document.getElementById('giftCardBalance').textContent = 'Rp 0';
            document.getElementById('giftCardAmount').value = 0;
            document.getElementById('giftCardStatus').innerHTML = '';
            calcChange();
        }

        function processPayment() {
            var totals = calcTotal();
            const gcAmount = parseInt(document.getElementById('giftCardAmount').value) || 0;
            const remaining = totals.total - gcAmount;
            const paid = parseInt(document.getElementById('payAmount').value) || 0;
            if (gcAmount > 0 && !validatedGiftCard) {
                Swal.fire('Validasi Gift Card', 'Silakan cek kode gift card terlebih dahulu', 'warning');
                return;
            }
            if (gcAmount > totals.total) {
                Swal.fire('Melebihi Total', 'Jumlah gift card tidak boleh melebihi total belanja', 'warning');
                return;
            }
            if (paid < Math.max(0, remaining)) {
                Swal.fire('Jumlah Bayar Kurang', '', 'warning');
                return;
            }
            @if(!$currentShift)
                Swal.fire('Shift Belum Dibuka', 'Silakan buka shift terlebih dahulu', 'warning');
                return;
            @else
            const items = cart.map(i => ({ product_id: i.id, quantity: i.qty, price: i.price }));
            const data = {
                items: items,
                payment_method: document.getElementById('payMethod').value,
                amount_paid: paid,
                shift_id: {{ $currentShift->id }},
                _token: '{{ csrf_token() }}'
            };
            if (validatedGiftCard) {
                data.gift_card_code = validatedGiftCard.code;
                data.gift_card_amount = gcAmount;
            }
            if (selectedMemberId) {
                data.member_id = selectedMemberId;
                if (totals.pointsUsed > 0) {
                    data.points_redeemed = totals.pointsUsed;
                }
            }
            document.getElementById('btnProcessPay').disabled = true;
            document.getElementById('btnProcessPay').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
            fetch('{{ route("pos.cashier.processSale") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    if (result.sale) {
                        setTimeout(function() { printReceipt(result.sale); }, 200);
                    }
                    Swal.fire({
                        title: 'Pembayaran Berhasil!',
                        text: 'Transaksi telah diproses',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#198754',
                        timer: 3000,
                        timerProgressBar: true
                    })                    .then(() => {
                        cart = [];
                        discount = 0;
                        validatedGiftCard = null;
                        document.getElementById('payAmount').value = '';
                        document.getElementById('payTotal').textContent = 'Rp 0';
                        document.getElementById('change').textContent = 'Rp 0';
                        document.getElementById('pointsToRedeem').value = 0;
                        document.getElementById('giftCardCode').value = '';
                        document.getElementById('giftCardDetail').style.display = 'none';
                        document.getElementById('giftCardBalance').textContent = 'Rp 0';
                        document.getElementById('giftCardAmount').value = 0;
                        document.getElementById('giftCardStatus').innerHTML = '';
                        renderCart();
                        $('#paymentModal').modal('hide');
                        refreshProducts();
                    });
                } else {
                    Swal.fire('Error', result.message || 'Gagal memproses transaksi', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Gagal menghubungi server', 'error');
            })
            .finally(() => {
                document.getElementById('btnProcessPay').disabled = false;
                document.getElementById('btnProcessPay').innerHTML = '<i class="fas fa-check mr-1"></i>Proses Pembayaran';
            });
            @endif
        }

        function updateCartToggleVisibility() {
            if (window.innerWidth > 991.98) return;
            var cart = document.getElementById('posCart');
            var btn = document.getElementById('cartToggle');
            var isCollapsed = cart.classList.contains('collapsed');
            btn.classList.toggle('hide-when-open', !isCollapsed);
        }

        function toggleCart() {
            var cart = document.getElementById('posCart');
            var isCollapsed = cart.classList.toggle('collapsed');
            document.querySelector('.cart-header .fa-chevron-down').style.transform = isCollapsed ? 'rotate(180deg)' : '';
            updateCartToggleVisibility();
        }

        function showCartMobile() {
            if (window.innerWidth <= 991.98) {
                var cart = document.getElementById('posCart');
                cart.classList.remove('collapsed');
                document.querySelector('.cart-header .fa-chevron-down').style.transform = '';
            }
        }

        document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const barcode = this.value.trim();
                if (!barcode) return;
                fetch('{{ route("pos.cashier.searchProduct") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ query: barcode })
                })
                    .then(r => r.json())
                    .then(products => {
                        if (products.length > 0) {
                            const p = products[0];
                            addToCart(p.id, p.name, p.selling_price, p.stocks ? p.stocks.reduce((s, st) => s + (st.total_stock || 0), 0) : 0, p.member_price);
                        } else {
                            Swal.fire('Produk Tidak Ditemukan', '', 'warning');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Gagal memindai barcode', 'error'));
                this.value = '';
            }
        });

        let searchTimeout;
        document.getElementById('searchProduct').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            searchTimeout = setTimeout(() => {
                if (q.length < 2) { location.reload(); return; }
                fetch('{{ route("pos.cashier.searchProduct") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ query: q })
                })
                    .then(r => r.json())
                    .then(products => {
                        const grid = document.getElementById('productGrid');
                        if (products.length === 0) {
                            grid.innerHTML = '<div class="col-12 text-center text-muted py-4">Produk tidak ditemukan</div>';
                            return;
                        }
                        grid.innerHTML = products.map(p => {
                            const stock = p.stocks ? p.stocks.reduce((s, st) => s + (st.total_stock || 0), 0) : 0;
                            const promo = promoByProduct[p.id];
                            const promoHtml = promo ? '<div class="product-stock text-danger fw-bold">Diskon ' + (promo.type === 'discount_percent' ? promo.value + '%' : 'Rp ' + Number(promo.value).toLocaleString('id-ID')) + '</div>' : '<div class="product-stock">Stok: ' + stock + '</div>';
                            var priceHtml = '<div class="product-price">Rp ' + p.selling_price.toLocaleString('id-ID') + '</div>';
                            if (p.member_price > 0) {
                                priceHtml = '<div class="product-price">Rp ' + p.selling_price.toLocaleString('id-ID') + ' <small class="text-success font-weight-bold">[Member: Rp ' + Number(p.member_price).toLocaleString('id-ID') + ']</small></div>';
                            }
                            var imgHtml = p.image ? '<div class="product-img text-center mb-1"><img src="/storage/' + p.image + '" style="max-height:60px;max-width:100%;object-fit:contain;"></div>' : '';
                            return `<div class="col-xl-3 col-md-4 col-sm-6">
                                <div class="product-card" onclick="addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.selling_price}, ${stock}, ${p.member_price || 0})">
                                    ${imgHtml}
                                    <div class="product-name">${p.name.length > 30 ? p.name.substr(0, 30) + '...' : p.name}</div>
                                    ${promoHtml}
                                    ${priceHtml}
                                </div>
                            </div>`;
                        }).join('');
                    });
            }, 300);
        });

        let memberResultsContainer = null;
        document.getElementById('memberInput').addEventListener('input', function() {
            memberSearch(this.value.trim());
        });
        document.getElementById('memberInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = this.value.trim();
                if (q.length >= 1) memberSearch(q);
            }
        });
        document.getElementById('memberInput').addEventListener('blur', function() {
            setTimeout(function() {
                if (memberResultsContainer) memberResultsContainer.remove();
                memberResultsContainer = null;
            }, 200);
        });
        document.getElementById('memberInput').addEventListener('focus', function() {
            const q = this.value.trim();
            if (q.length >= 1) memberSearch(q);
        });

        function memberSearch(q) {
            if (memberResultsContainer) memberResultsContainer.remove();
            memberResultsContainer = null;
            if (q.length < 1) {
                if (!selectedMemberId) {
                    document.getElementById('memberInfo').style.display = 'none';
                }
                return;
            }
            fetch('{{ route("pos.cashier.getMembers") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ query: q })
            })
            .then(r => r.json())
            .then(members => {
                if (members.length === 0) return;
                memberResultsContainer = document.createElement('div');
                memberResultsContainer.className = 'list-group position-absolute shadow-sm border rounded';
                memberResultsContainer.style.cssText = 'z-index:1050;width:100%;max-height:250px;overflow-y:auto;background:#fff;';
                var inputGroup = document.querySelector('#memberInput').closest('.input-group');
                inputGroup.parentNode.style.position = 'relative';
                members.forEach(function(m) {
                    var item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action py-2 px-3';
                    item.innerHTML = '<div class="d-flex justify-content-between align-items-center"><div><strong>' + m.name + '</strong><br><small class="text-muted">' + m.code + ' | ' + m.phone + '</small></div><div class="text-right"><span class="badge badge-info">' + m.level_label + '</span><br><small class="text-success font-weight-bold">Diskon ' + m.discount_percent + '%</small></div></div>';
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectMember(m);
                        if (memberResultsContainer) memberResultsContainer.remove();
                        memberResultsContainer = null;
                    });
                    memberResultsContainer.appendChild(item);
                });
                inputGroup.parentNode.appendChild(memberResultsContainer);
            });
        }

        function selectMember(m) {
            selectedMemberId = m.id;
            selectedMember = m;
            document.getElementById('memberInput').value = m.name + ' (' + m.code + ')';
            document.getElementById('memberInfo').style.display = 'block';
            document.getElementById('memberName').textContent = m.name;
            document.getElementById('memberCode').textContent = m.code;
            document.getElementById('memberPhone').textContent = m.phone || '-';
            document.getElementById('memberLevelBadge').textContent = m.level_label;
            if (m.discount_percent > 0) {
                document.getElementById('memberDiscountLabel').textContent = 'Diskon ' + m.discount_percent + '%';
            } else {
                document.getElementById('memberDiscountLabel').textContent = 'Tidak ada diskon';
            }
            document.getElementById('memberPointsLabel').textContent = m.points + ' poin (Rp ' + Number(m.points_rupiah).toLocaleString('id-ID') + ')';
            recalcCartPrices();
        }

        function removeMember() {
            selectedMemberId = null;
            selectedMember = null;
            document.getElementById('memberInput').value = '';
            document.getElementById('memberInfo').style.display = 'none';
            recalcCartPrices();
        }

        function openMemberSearch() {
            Swal.fire({
                title: 'Cari Member',
                html: '<input type="text" id="swal-member-search" class="form-control mb-3" placeholder="Ketik nama/kode/telepon..." autofocus>' +
                      '<div id="swal-member-results" class="list-group" style="max-height:300px;overflow-y:auto;"></div>',
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                didOpen: function() {
                    var input = document.getElementById('swal-member-search');
                    input.focus();
                    input.addEventListener('input', function() {
                        var q = this.value.trim();
                        if (q.length < 1) {
                            document.getElementById('swal-member-results').innerHTML = '';
                            return;
                        }
                        fetch('{{ route("pos.cashier.getMembers") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ query: q })
                        })
                        .then(r => r.json())
                        .then(members => {
                            var container = document.getElementById('swal-member-results');
                            if (members.length === 0) {
                                container.innerHTML = '<div class="text-muted text-center py-3">Member tidak ditemukan</div>';
                                return;
                            }
                            container.innerHTML = members.map(function(m) {
                                return '<a href="#" class="list-group-item list-group-item-action py-2" data-member=\'' + JSON.stringify(m).replace(/'/g, "&#39;") + '\'>' +
                                    '<div class="d-flex justify-content-between align-items-center"><div><strong>' + m.name + '</strong><br><small class="text-muted">' + m.code + ' | ' + m.phone + '</small></div><div class="text-right"><span class="badge badge-info">' + m.level_label + '</span><br><small class="text-success font-weight-bold">Diskon ' + m.discount_percent + '%</small></div></div></a>';
                            }).join('');
                            container.querySelectorAll('a').forEach(function(el) {
                                el.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    var m = JSON.parse(this.dataset.member);
                                    selectMember(m);
                                    Swal.close();
                                });
                            });
                        });
                    });
                }
            });
        }

        function recalcCartPrices() {
            cart.forEach(function(item) {
                if (selectedMember && item.memberPrice > 0) {
                    item.price = item.memberPrice;
                } else {
                    item.price = item.regularPrice;
                }
            });
            renderCart();
        }

        function printReceipt(sale) {
            const receiptId = 'receipt-iframe';
            let iframe = document.getElementById(receiptId);
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = receiptId;
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                document.body.appendChild(iframe);
            }
            let html = '<html><head><title>Struk Belanja</title>';
            html += '<style>body{font-family:monospace;font-size:12px;width:280px;margin:0 auto;padding:10px}';
            html += '.header{text-align:center;margin-bottom:10px}';
            html += '.header h3{margin:0;font-size:16px}';
            html += '.line{border-top:1px dashed #000;margin:5px 0}';
            html += '.item{display:flex;justify-content:space-between}';
            html += '.total{font-weight:bold;font-size:14px}';
            html += '.footer{text-align:center;margin-top:10px;font-size:10px}</style></head><body>';
            html += '<div class="header">';
            if (company.logo) {
                html += '<div style="text-align:center;margin-bottom:8px"><img src="' + (window.location.origin + '/storage/' + company.logo) + '" alt="Logo" style="max-height:60px;max-width:100%;border-radius:8px;"></div>';
            }
            html += '<h3>' + (company.company_name || 'POS Supermarket') + '</h3>';
            html += '<p>' + (company.address || '') + '</p>';
            html += '<p>Telp: ' + (company.phone || '') + '</p>';
            if (company.receipt_header) {
                html += '<div class="line"></div>';
                html += '<p>' + company.receipt_header + '</p>';
            }
            html += '</div>';
            html += '<div class="line"></div>';
            html += '<p>No: ' + sale.code + '<br>';
            html += new Date().toLocaleString('id-ID') + '</p>';
            if (sale.member) {
                html += '<p>Member: ' + sale.member.name + ' (' + sale.member.code + ')<br>';
                html += 'Level: ' + sale.member.membership_level.toUpperCase() + '</p>';
            }
            html += '<div class="line"></div>';
            if (sale.items && sale.items.length) {
                sale.items.forEach(function(it) {
                    const name = it.product ? (it.product.name || it.product.barcode || 'Produk') : 'Produk';
                    html += '<div class="item"><span>' + name.substr(0, 20) + '</span><span>x' + it.quantity + '</span></div>';
                    html += '<div class="item"><span></span><span>Rp ' + Number(it.subtotal).toLocaleString('id-ID') + '</span></div>';
                });
            }
            html += '<div class="line"></div>';
            html += '<div class="item"><span>Subtotal</span><span>Rp ' + Number(sale.subtotal).toLocaleString('id-ID') + '</span></div>';
            if (sale.discount_amount > 0) html += '<div class="item"><span>Diskon Promo</span><span>-Rp ' + Number(sale.discount_amount - (sale.member_discount || 0)).toLocaleString('id-ID') + '</span></div>';
            if ((sale.member_discount || 0) > 0) html += '<div class="item"><span>Diskon Member</span><span>-Rp ' + Number(sale.member_discount).toLocaleString('id-ID') + '</span></div>';
            if ((sale.points_discount || 0) > 0) html += '<div class="item"><span>Tukar Poin</span><span>-Rp ' + Number(sale.points_discount).toLocaleString('id-ID') + '</span></div>';
            if (sale.tax_amount > 0) html += '<div class="item"><span>Pajak 11%</span><span>Rp ' + Number(sale.tax_amount).toLocaleString('id-ID') + '</span></div>';
            html += '<div class="item total"><span>TOTAL</span><span>Rp ' + Number(sale.total).toLocaleString('id-ID') + '</span></div>';
            if (sale.gift_card_amount > 0) html += '<div class="item"><span>Gift Card</span><span>-Rp ' + Number(sale.gift_card_amount).toLocaleString('id-ID') + '</span></div>';
            html += '<div class="item"><span>Bayar</span><span>Rp ' + Number(sale.amount_paid).toLocaleString('id-ID') + '</span></div>';
            html += '<div class="item"><span>Kembali</span><span>Rp ' + Number(sale.change_amount).toLocaleString('id-ID') + '</span></div>';
            if (sale.points_earned > 0) html += '<p class="text-center">Poin didapat: ' + sale.points_earned + '</p>';
            if (sale.points_redeemed > 0) html += '<p class="text-center">Poin dipakai: ' + sale.points_redeemed + '</p>';
            html += '<div class="line"></div>';
            html += '<div class="footer">';
            html += '<p>' + (company.receipt_message || 'Terima Kasih') + '</p>';
            if (company.receipt_footer) {
                html += '<p>' + company.receipt_footer + '</p>';
            }
            html += '<p>Barang yang sudah dibeli tidak dapat dikembalikan</p>';
            html += '</div>';
            html += '</body></html>';
            iframe.srcdoc = html;
            iframe.onload = function() {
                setTimeout(function() { iframe.contentWindow.print(); }, 300);
            };
        }

        function refreshProducts() {
            fetch('{{ route("pos.cashier.searchProduct") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ query: '' })
            })
            .then(r => r.json())
            .then(products => {
                const grid = document.getElementById('productGrid');
                grid.innerHTML = products.map(p => {
                    const stock = p.stocks ? p.stocks.reduce((s, st) => s + (st.total_stock || 0), 0) : 0;
                    const promo = promoByProduct[p.id];
                    const promoHtml = promo ? '<div class="product-stock text-danger fw-bold">Diskon ' + (promo.type === 'discount_percent' ? promo.value + '%' : 'Rp ' + Number(promo.value).toLocaleString('id-ID')) + '</div>' : '<div class="product-stock">Stok: ' + stock + '</div>';
                    var priceHtml = '<div class="product-price">Rp ' + p.selling_price.toLocaleString('id-ID') + '</div>';
                    if (p.member_price > 0) {
                        priceHtml = '<div class="product-price">Rp ' + p.selling_price.toLocaleString('id-ID') + ' <small class="text-success font-weight-bold">[Member: Rp ' + Number(p.member_price).toLocaleString('id-ID') + ']</small></div>';
                    }
                    var imgHtml = p.image ? '<div class="product-img text-center mb-1"><img src="/storage/' + p.image + '" style="max-height:60px;max-width:100%;object-fit:contain;"></div>' : '';
                    return `<div class="col-xl-3 col-md-4 col-sm-6" data-category="">
                        <div class="product-card" onclick="addToCart(${p.id}, '${(p.name || '').replace(/'/g, "\\'")}', ${p.selling_price}, ${stock}, ${p.member_price || 0})">
                            ${imgHtml}
                            <div class="product-name">${p.name.length > 30 ? p.name.substr(0, 30) + '...' : p.name}</div>
                            ${promoHtml}
                            ${priceHtml}
                        </div>
                    </div>`;
                }).join('');
            });
        }

        function filterCategory(cat) {
            document.querySelectorAll('#productGrid > div').forEach(el => {
                el.style.display = (!cat || el.dataset.category === cat) ? '' : 'none';
            });
            document.querySelectorAll('.category-tabs .btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
        }

        if (window.innerWidth <= 991.98) {
            document.getElementById('posCart').classList.add('collapsed');
            updateCartToggleVisibility();
        }
    </script>

    <!-- Scanner Modal -->
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
            $.get('{{ route("api.products.barcode", "") }}/' + encodeURIComponent(code), function(product) {
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
</body>
</html>
