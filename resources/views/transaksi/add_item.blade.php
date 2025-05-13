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
                <label for="harga">Harga</label>
                <input type="number" class="form-control" id="harga" name="harga" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="panjang">Panjang (P)</label>
                        <input type="number" class="form-control" id="panjang" name="panjang" value="0" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lebar">Lebar (L)</label>
                        <input type="number" class="form-control" id="lebar" name="lebar" value="0" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
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
                <label for="satuan">Satuan</label>
                <select class="form-control" id="satuan" name="satuan">
                    <option value="PCS">PCS</option>
                    <option value="MTR">MTR</option>
                    <option value="BTG">BTG</option>
                    <option value="LBR">LBR</option>
                    <option value="UNIT">UNIT</option>
                </select>
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
                            <th>Harga</th>
                            <th>Length</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Satuan</th>
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
$(document).ready(function() {
    // Calculate total whenever inputs change
    $('#harga, #quantity, #diskon').on('input', function() {
        updatePreview();
    });

    function updatePreview() {
        const kodeBarang = $('#kode_barang').val() || '-';
        const keterangan = $('#keterangan').val() || '-';
        const harga = parseInt($('#harga').val()) || 0;
        const panjang = parseInt($('#panjang').val()) || 0;
        const lebar = parseInt($('#lebar').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const satuan = $('#satuan').val();
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
                <td>${panjang}</td>
                <td>${quantity}</td>
                <td class="text-right">${formatCurrency(subTotal)}</td>
                <td>${satuan}</td>
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

    // Fungsi untuk mengambil stock - FIXED
    function fetchStock(kodeBarang) {
        // Pastikan kode barang tidak kosong
        if (!kodeBarang) {
            $('#stock_tersedia').val('');
            return;
        }
        
        // Ambil SO dari form utama jika ada (default LAMPUNG)
        let so = 'LAMPUNG';
        if (window.parent && window.parent.$) {
            so = window.parent.$('#sales').val() || 'LAMPUNG';
        }
        
        console.log(`Fetching stock for: ${kodeBarang}, SO: ${so}`);
        
        $.ajax({
            url: "/stock/get",
            method: "GET",
            data: { 
                kode_barang: kodeBarang, 
                so: so 
            },
            success: function(response) {
                console.log("Stock API Response:", response);
                if (response && response.success) {
                    $('#stock_tersedia').val(response.data.good_stock);
                } else {
                    $('#stock_tersedia').val('0');
                    console.log("Invalid response format or success=false");
                }
            },
            error: function(xhr, status, error) {
                console.error("Stock fetch error:", error);
                $('#stock_tersedia').val('0');
            }
        });
    }

    // Search kode Barang
    $('#kode_barang').on('input', function() {
        const keyword = $(this).val();
        if (keyword.length > 0) {
            $.ajax({
                url: "/api/panels/search-available",
                method: "GET",
                data: { keyword },
                success: function(data) {
                    let dropdown = '';
                    if (data.length > 0) {
                        data.forEach(panel => {
                            dropdown += `<a class="dropdown-item panel-item" 
                            data-id="${panel.group_id}" 
                            data-name="${panel.name}" 
                            data-price="${panel.price}" 
                            data-length="${panel.length}">
                            
                            ${panel.group_id} - ${panel.name} - ${panel.length} m</a>`;
                        });
                    } else {
                        dropdown = '<a class="dropdown-item disabled">Tidak ada panel ditemukan</a>';
                    }
                    $('#kodeBarangDropdown').html(dropdown).show();
                },
                error: function() {
                    alert('Gagal memuat data panel.');
                }
            });
        } else {
            $('#kodeBarangDropdown').hide();
        }
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
        $('#panjang').val(panelLength);
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
        $('#panjang').val(panelLength);
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
