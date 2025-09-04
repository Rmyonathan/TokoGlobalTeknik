
<form id="addItemForm">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="kode_barang">Kode Barang</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Masukkan kode barang">
                    <div id="kodeBarangDropdown" class="dropdown-menu" style="display: none; position: absolute; width: 100%;"></div>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="findItem" data-toggle="modal" data-target="#selectPanelModal">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan nama barang">
                    <div id="namaBarangDropdown" class="dropdown-menu" style="display: none; position: absolute; width: 100%;"></div>
                </div>
                <input type="hidden" id="kode_barang_id" name="kode_barang_id">
            </div>

            <div class="form-group">
                <label for="stock_tersedia">Stock Tersedia</label>
                <input type="text" class="form-control" id="stock_tersedia" name="stock_tersedia" readonly>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="harga">Harga Jual</label>
                <input type="number" class="form-control" id="harga" name="harga" required>
            </div>

            <!-- <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="panjang">Panjang (P)</label>
                        <input type="number" class="form-control" id="panjang" name="panjang" value="0" readonly>
                    </div>
                </div>
               
            </div> -->

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                        <small id="qtyError" class="text-danger" style="display:none;"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="diskon">Diskon (%)</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" value="0" min="0" max="100">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="satuanKecil">Satuan Kecil</label>
                <select class="form-control" id="satuanKecil" name="satuanKecil">
                    <option value="LBR">LBR</option>
                </select>
            </div>
            <div class="form-group">
                <label for="satuanBesar">Satuan Besar</label>
                <select class="form-control" id="satuanBesar" name="satuanBesar"></select>
            </div>

            <div class="form-group">
                <label for="ongkos_kuli">Ongkos Kuli</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="number" class="form-control" id="ongkos_kuli" name="ongkos_kuli" value="0" min="0" step="100">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-info" id="getOngkosKuliBtn" title="Ambil ongkos kuli otomatis">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">Klik tombol <i class="fas fa-magic"></i> untuk ambil ongkos kuli otomatis</small>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode</th>
                            <th>Keterangan</th>
                            <th>Harga Jual</th>
                            <th>Length</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Satuan Kecil</th>
                            <th>Satuan Besar</th>
                            <th>Disc(%)</th>
                            <th>Disc(Rp.)</th>
                            <th>Sub Total</th>
                        </tr>
                    </thead>
                    <tbody id="itemPreview">
                        <!-- Item preview will be shown here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
    // Validasi input quantity barang
    function validateQuantity() {
        const qty = parseInt($('#quantity').val()) || 0;
        const stokRaw = $('#stock_tersedia').val();
        const stok = stokRaw === '' ? null : (parseInt(stokRaw) || 0);
        let valid = true;
        let msg = '';

        if (qty <= 0) {
            valid = false;
            msg = 'Quantity harus lebih dari 0.';
        } else if (stok !== null && stok > 0 && qty > stok) {
            valid = false;
            msg = 'Quantity tidak boleh melebihi stok tersedia.';
        }

        if (!valid) {
            $('#quantity').addClass('is-invalid');
            $('#qtyError').text(msg).show();
            $('#saveItemBtn').prop('disabled', true);
        } else {
            $('#quantity').removeClass('is-invalid');
            $('#qtyError').hide();
            $('#saveItemBtn').prop('disabled', false);
        }
        return valid;
    }

    // Jalankan validasi saat quantity atau stok berubah
    $('#quantity, #stock_tersedia').on('input change', function() {
        validateQuantity();
    });

    // Jalankan validasi juga saat pilih barang baru (stok berubah)
    $(document).on('click', '.panel-item, .select-panel-btn', function() {
        setTimeout(validateQuantity, 100);
    });

    // Jalankan validasi awal saat modal dibuka
    $('#addItemModal').on('shown.bs.modal', function () {
        validateQuantity();
    });
    
    $(document).ready(function() {
        const inventoryByLength = @json($inventory['inventory_by_length']);
        // Calculate total whenever inputs change
        $('#harga, #quantity, #diskon').on('input', function() {
            updatePreview();
        });

        $('#satuanKecil, #satuanBesar').on('change', function() {
            updatePreview();
            triggerHargaOngkosUpdate();
        });

        function populateUnits(kodeBarangId, unitDasar) {
            if (!kodeBarangId) {
                $('#satuanKecil').html('<option value="LBR">LBR</option>');
                $('#satuanBesar').html('');
                return;
            }
            $.ajax({
                url: `{{ route('sales-order.available-units', '') }}/${kodeBarangId}`,
                method: 'GET',
                success: function(units) {
                    const kecil = $('#satuanKecil');
                    const besar = $('#satuanBesar');
                    kecil.empty();
                    besar.empty();
                    const base = unitDasar || 'LBR';
                    kecil.append('<option value="'+base+'">'+base+'</option>');
                    if (Array.isArray(units) && units.length > 0) {
                        units.forEach(function(u){ if (u !== base) { besar.append('<option value="'+u+'">'+u+'</option>'); } });
                    }
                    kecil.val(base);
                },
                error: function() {
                    $('#satuanKecil').html('<option value="LBR">LBR</option>');
                    $('#satuanBesar').html('');
                }
            });
        }

        function triggerHargaOngkosUpdate() {
            const kodeBarangId = $('#kode_barang_id').val();
            const satuan = $('#satuanBesar').val() || $('#satuanKecil').val();
            const customerId = $('#kode_customer').val();
            if (kodeBarangId && satuan && customerId) {
                $.ajax({
                    url: "{{ route('api.transaksi.harga-ongkos') }}",
                    method: "GET",
                    data: {
                        customer_id: customerId,
                        kode_barang_id: kodeBarangId,
                        satuan: satuan
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#harga').val(response.harga_jual);
                            $('#ongkos_kuli').val(response.ongkos_kuli);
                            updatePreview();
                        }
                    }
                });
            }
        }

        function updatePreview() {
            const kodeBarang = $('#kode_barang').val() || '-';
            const keterangan = $('#keterangan').val() || '-';
            const harga = parseInt($('#harga').val()) || 0;
            // const panjang = parseInt($('#panjang').val()) || 0;
            const lebar = parseInt($('#lebar').val()) || 0;
            const quantity = parseInt($('#quantity').val()) || 0;
            const satuanKecil = $('#satuanKecil').val();
            const satuanBesar = $('#satuanBesar').val();
            const diskon = parseInt($('#diskon').val()) || 0;

            // Calculate values
            const subTotal = harga * quantity;
            const diskonAmount = (diskon / 100) * subTotal;
            const total = subTotal - diskonAmount;

            // Update preview
            const tbody = $('#itemPreview');
            tbody.empty();

            tbody.append(`
                 <tr>
                    <td>${kodeBarang}</td>
                    <td>${keterangan}</td>
                    <td class="text-right">${formatCurrency(harga)}</td>
                    <td>-</td> <!-- kolom Length sementara -->
                    <td>${quantity}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                    <td>${satuanKecil}</td>
                    <td>${satuanBesar || '-'}</td>
                    <td>${diskon}%</td>
                    <td class="text-right">${formatCurrency(diskonAmount)}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                </tr>
            `);
        }

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        // Fungsi untuk mengambil stock (placeholder)
        function fetchStock(kodeBarang) {
            if (!kodeBarang) {
                $('#stock_tersedia').val('');
                return;
            }
            $.ajax({
                url: "{{ route('stock.get') }}",
                method: 'GET',
                data: { kode_barang: kodeBarang },
                success: function(resp) {
                    if (resp && resp.success && resp.data) {
                        const data = resp.data;
                        const good = parseFloat(data.good_stock) || 0;
                        const unit = data.satuan || 'LBR';
                        $('#stock_tersedia').val(good);
                        // Set satuan kecil dari table stocks
                        $('#satuanKecil').html('<option value="'+unit+'">'+unit+'</option>').val(unit);
                        // Populate satuan besar berdasarkan konversi, menggunakan unit dasar dari stocks
                        const kodeBarangId = $('#kode_barang_id').val();
                        populateUnits(kodeBarangId, unit);
                        // Update harga/ongkos sesuai unit terpilih
                        triggerHargaOngkosUpdate();
                    } else {
                        $('#stock_tersedia').val('');
                    }
                },
                error: function() {
                    $('#stock_tersedia').val('');
                }
            });
        }

        // Search kode Barang (Kode Barang master)
        $('#kode_barang').on('input', function() {
            const keyword = $(this).val();
            if (keyword.length > 0) {
                $.ajax({
                    url: "{{ route('kodeBarang.search') }}",
                    method: "GET",
                    data: { keyword },
                    success: function(data) {
                        let dropdown = '';
                        if (data.length > 0) {
                            data.forEach(kb => {
                                dropdown += `<a class="dropdown-item kodebarang-item" 
                                data-kode="${kb.kode_barang}" 
                                data-name="${kb.name}" 
                                data-id="${kb.id}" 
                                data-harga="${kb.harga_jual || 0}">
                                ${kb.kode_barang} - ${kb.name}</a>`;
                            });
                        } else {
                            dropdown = '<a class="dropdown-item disabled">Tidak ada barang ditemukan</a>';
                        }
                        $('#kodeBarangDropdown').html(dropdown).show();
                    },
                    error: function() {
                        alert('Gagal memuat data barang.');
                    }
                });
            } else {
                $('#kodeBarangDropdown').hide();
            }
        });

        // Pilih Kode Barang dari dropdown
        $(document).on('click', '.kodebarang-item', function() {
            const kode = $(this).data('kode');
            const nama = $(this).data('name');
            const harga = $(this).data('harga') || 0;
            const id = $(this).data('id');

            $('#kode_barang').val(kode);
            $('#nama_barang').val(nama);
            $('#harga').val(harga);
            $('#keterangan').val(nama);
            $('#kode_barang_id').val(id);

            // Ambil satuan kecil dari table stocks dan isi units
            fetchStock(kode);
            updatePreview();
            $('#kodeBarangDropdown').hide();
        });

        // Panggil fetchStock HANYA saat user klik dari dropdown
        $(document).on('click', '.panel-item', function() {
            const panelId = $(this).data('id');
            const panelName = $(this).data('name');
            const panelPrice = $(this).data('price');
            const panelLength = $(this).data('length');

            $('#kode_barang').val(panelId);
            $('#nama_barang').val(panelName);
            $('#harga').val(panelPrice);
            // $('#panjang').val(panelLength);
            $('#keterangan').val(panelName);

            // Panggil fetchStock di sini saja
            fetchStock(panelId);

            // Update preview jika ada
            updatePreview();

            $('#kodeBarangDropdown').hide();
        });

        // Hide dropdown when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#kode_barang, #kodeBarangDropdown').length) {
                $('#kodeBarangDropdown').hide();
            }
        });

        // Select panel from the modal - FIXED
        $(document).on('click', '.select-panel-btn', function() {
            const panelId = $(this).data('id');
            const panelName = $(this).data('name');
            const panelLength = $(this).data('length');
            const panelPrice = $(this).data('price');

            $('#kode_barang').val(panelId);
            $('#nama_barang').val(panelName);
            // $('#panjang').val(panelLength);
            $('#harga').val(panelPrice);
            $('#keterangan').val(panelName);
            // Explicitly fetch stock after selecting from modal
            fetchStock(panelId);
            // Update preview
            updatePreview();

            $('#selectPanelModal').modal('hide');
            
        });
        
        // Load panels into the modal
        $('#selectPanelModal').on('show.bs.modal', function() {
            $.ajax({
                url: "/api/panels/search",
                method: "GET",
                success: function(data) {
                    let rows = '';
                    data.forEach(panel => {
                        rows += `
                            <tr>
                                <td>${panel.id}</td>
                                <td>${panel.name}</td>
                                <td>${panel.length}</td>
                                <td>${panel.price}</td>
                                <td>
                                    <button type="button" class="btn btn-primary select-panel-btn"
                                        data-id="${panel.id}"
                                        data-name="${panel.name}"
                                        data-length="${panel.length}"
                                        data-price="${panel.price}">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#panelsTable tbody').html(rows);
                },
                error: function() {
                    alert('Gagal memuat data panel.');
                }
            });
        });
    });
</script>
