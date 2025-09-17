// Purchase Transaction JavaScript - New Version with Inline Form
$(document).ready(function () {
    // Initialize variables
    let items = [];
    let grandTotal = 0;

    // Toggle small/large item forms by mode pembelian
    $(document).on('change', '#mode_pembelian', function() {
        const mode = $(this).val();
        if (mode === 'besar') {
            $('#cardSmallItems').hide();
            $('#cardLargeItems').show();
        } else {
            $('#cardLargeItems').hide();
            $('#cardSmallItems').show();
        }
    });
    // Search suppliers
    $("#supplier").on("input", function () {
        console.log("Supplier input changed:", $(this).val());
        const keyword = $(this).val();
        if (keyword.length > 0) {
            console.log(
                "About to send AJAX request to:",
                window.supplierSearchUrl
            );
            $.ajax({
                url: window.supplierSearchUrl,
                method: "GET",
                data: { keyword },
                success: function (data) {
                    console.log("Supplier search results:", data);
                    let dropdown = "";
                    if (data.length > 0) {
                        data.forEach((supplier) => {
                            dropdown += `<a class="dropdown-item supplier-item" data-kode="${supplier.kode_supplier}" data-name="${supplier.nama}">${supplier.kode_supplier} - ${supplier.nama}</a>`;
                        });
                    } else {
                        dropdown =
                            '<a class="dropdown-item disabled">Tidak ada supplier ditemukan</a>';
                    }
                    $("#supplierDropdown").html(dropdown).show();
                },
                error: function (xhr, status, error) {
                    console.error(
                        "Error searching suppliers:",
                        xhr.responseText
                    );
                    console.error("Status:", status);
                    console.error("Error:", error);
                    alert("Terjadi kesalahan saat mencari supplier.");
                },
            });
        } else {
            $("#supplierDropdown").hide();
        }
    });

    // Select Supplier
    $(document).on("click", ".supplier-item", function () {
        const kodeSupplier = $(this).data("kode");
        const namaSupplier = $(this).data("name");
        $("#kode_supplier").val(kodeSupplier); // Isi input hidden dengan kode supplier
        $("#supplier").val(`${kodeSupplier} - ${namaSupplier}`); // Tampilkan kode dan nama supplier di input utama
        $("#supplierDropdown").hide();
    });

    // Hide dropdown when clicking outside
    $(document).click(function (e) {
        if (
            !$(e.target).closest(
                "#supplier, #supplierDropdown, #kode_barang, #kodeBarangDropdown"
            ).length
        ) {
            $("#supplierDropdown").hide();
            $("#kodeBarangDropdown").hide();
        }
    });

    // Toggle discount inputs
    $("#discount_checkbox").change(function () {
        $("#discount_percent").prop("disabled", !this.checked);
        calculateTotals();
    });

    $("#ppn_checkbox").change(function () {
        calculateTotals();
    });

    // Calculate input changes
    $("#discount_percent").on("input", function () {
        calculateTotals();
    });

    // Handle item-barang change (small form)
    $(document).on('change', '.item-barang', function() {
        const row = $(this).closest('.item-row');
        const selectedOption = $(this).find('option:selected');
        const kodeBarangId = $(this).val();
        
        if (kodeBarangId) {
            // Get data from selected option
            const harga = selectedOption.data('harga') || 0;
            const unitDasar = selectedOption.data('unit-dasar') || 'LBR';
            const kodeBarang = selectedOption.data('kode') || '';
            const namaBarang = selectedOption.data('nama') || '';
            
            // Set harga
            row.find('.item-harga').val(harga);
            
            // Set satuan kecil
            const satuanKecilSelect = row.find('.item-satuan-kecil');
            satuanKecilSelect.empty();
            satuanKecilSelect.append(`<option value="${unitDasar}">${unitDasar}</option>`);
            row.find('.item-satuan').val(unitDasar);
            
            // Get available units for satuan besar (small form)
            $.ajax({
                url: `${window.availableUnitsUrl}/${kodeBarangId}`,
                method: 'GET',
                success: function (units) {
                    const satuanBesarSelect = row.find('.item-satuan-besar');
                    satuanBesarSelect.empty();
                    // satuanBesarSelect.append('<option value="">Pilih Satuan Besar</option>');
                    
                    if (units && units.length > 0) {
                        units.forEach(unit => {
                            if (unit !== unitDasar) {
                                satuanBesarSelect.append(`<option value="${unit}">${unit}</option>`);
                            }
                        });
                    }
                    // Fetch conversion list and store in row data
                    $.ajax({
                        url: `${window.unitConversionListUrl}/${kodeBarangId}/list`,
                        method: 'GET',
                        success: function(list){
                            const map = {};
                            (list || []).forEach(it => { map[it.unit_turunan] = parseFloat(it.nilai_konversi||0); });
                            row.data('conversionMap', map);
                        }
                    });
                },
                error: function () {
                    console.log('Error fetching available units');
                }
            });
            
            // Calculate total
            calculateItemTotal(row);
        }
    });

    // Handle qty, harga, satuan changes
    $(document).on('input', '.item-qty', function() {
        const row = $(this).closest('.item-row');
        calculateItemTotal(row);
    });

    $(document).on('input', '.item-harga', function() {
        const row = $(this).closest('.item-row');
        calculateItemTotal(row);
    });

    $(document).on('change', '.item-satuan-kecil', function() {
        const row = $(this).closest('.item-row');
        const unit = $(this).val();
        row.find('.item-satuan').val(unit);
        calculateItemTotal(row);
    });

    $(document).on('change', '.item-satuan-besar', function() {
        const row = $(this).closest('.item-row');
        const unit = $(this).val();
        row.find('.item-satuan').val(unit);
        calculateItemTotal(row);
    });

    // ========== Large form handlers ==========
    // Change barang on large form
    $(document).on('change', '.item-barang-large', function() {
        const row = $(this).closest('.item-row-large');
        const selectedOption = $(this).find('option:selected');
        const kodeBarangId = $(this).val();
        if (!kodeBarangId) return;

        const harga = selectedOption.data('harga') || 0;
        const unitDasar = selectedOption.data('unit-dasar') || 'LBR';

        // Set harga
        row.find('.item-harga-large').val(harga);

        // Fetch available large units (exclude unit dasar)
        $.ajax({
            url: `${window.availableUnitsUrl}/${kodeBarangId}`,
            method: 'GET',
            success: function (units) {
                const select = row.find('.item-satuan-besar-large');
                select.empty();
                if (units && units.length > 0) {
                    units.forEach(unit => {
                        if (unit !== unitDasar) {
                            select.append(`<option value="${unit}">${unit}</option>`);
                        }
                    });
                }
                // Set first as default hidden satuan
                const first = select.find('option').first().val() || '';
                row.find('.item-satuan-large').val(first);
                // Fetch conversion list and store in row data
                $.ajax({
                    url: `${window.unitConversionListUrl}/${kodeBarangId}/list`,
                    method: 'GET',
                    success: function(list){
                        const map = {};
                        (list || []).forEach(it => { map[it.unit_turunan] = parseFloat(it.nilai_konversi||0); });
                        row.data('conversionMap', map);
                    }
                });
            },
            error: function(){
                console.log('Error fetching available units');
            }
        });

        calculateItemTotalLarge(row);
    });

    // Change selected large unit
    $(document).on('change', '.item-satuan-besar-large', function(){
        const row = $(this).closest('.item-row-large');
        row.find('.item-satuan-large').val($(this).val());
        calculateItemTotalLarge(row);
    });

    // Qty/Harga inputs on large form
    $(document).on('input', '.item-qty-large, .item-harga-large', function(){
        const row = $(this).closest('.item-row-large');
        calculateItemTotalLarge(row);
    });

    function calculateItemTotalLarge(row) {
        const qty = parseFloat(row.find('.item-qty-large').val()) || 0;
        const harga = parseFloat(row.find('.item-harga-large').val()) || 0;
        const selectedLarge = row.find('.item-satuan-besar-large').val();
        const conversionMap = row.data('conversionMap') || {};
        const factor = selectedLarge && conversionMap[selectedLarge] ? parseFloat(conversionMap[selectedLarge]) : 1;
        
        // FIX: Untuk satuan besar, konversi ke satuan dasar dengan MENGALIKAN
        const effectiveQty = qty * factor;
        const total = Math.round(effectiveQty * harga); // Pembulatan ke integer
        
        row.find('.item-total-large').val(total);
    }

    // Calculate item total
    function calculateItemTotal(row) {
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const harga = parseFloat(row.find('.item-harga').val()) || 0;
        const chosenUnit = row.find('.item-satuan').val();
        const selectedLarge = row.find('.item-satuan-besar').val();
        const conversionMap = row.data('conversionMap') || {};
        const shouldConvert = selectedLarge && chosenUnit === selectedLarge;
        const factor = shouldConvert && conversionMap[selectedLarge] ? parseFloat(conversionMap[selectedLarge]) : 1;
        // FIX: Untuk satuan besar, konversi ke satuan dasar dengan MENGALIKAN
        const effectiveQty = shouldConvert ? qty * factor : qty;
        const total = Math.round(effectiveQty * harga); // Pembulatan ke integer
        row.find('.item-total').val(total);
    }

    // Add Item Button
    $('#addItemBtn').click(function() {
        const row = $(this).closest('.item-row');
        const kodeBarangSelect = row.find('.item-barang');
        const selectedOption = kodeBarangSelect.find('option:selected');
        
        const kodeBarang = selectedOption.data('kode');
        const namaBarang = selectedOption.data('nama');
        const merek = selectedOption.data('merek') || '';
        const ukuran = selectedOption.data('ukuran') || '';
        const kodeBarangId = kodeBarangSelect.val();
        const keterangan = row.find('#keterangan').val();
        const harga = parseFloat(row.find('.item-harga').val()) || 0;
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const satuan = row.find('.item-satuan').val();
        const selectedLarge = row.find('.item-satuan-besar').val();
        const usedLarge = selectedLarge && (satuan === selectedLarge);
        const satuanBesar = selectedLarge || '';
        const diskon = parseFloat(row.find('#diskon').val()) || 0;
        const panjang = parseFloat(row.find('#panjang').val()) || 0;

        if (!kodeBarangId || !kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data barang!');
            return;
        }

        // Calculate total with conversion only if chosen unit equals selected large unit
        const conversionMap = row.data('conversionMap') || {};
        const factor = usedLarge && conversionMap[selectedLarge] ? parseFloat(conversionMap[selectedLarge]) : 1;
        const effectiveQty = qty * factor;
        const subtotal = harga * effectiveQty;
        const diskonAmount = (subtotal * diskon) / 100;
        const total = subtotal - diskonAmount;

        const newItem = {
            kodeBarang,
            namaBarang,
            merek,
            ukuran,
            ukuran,
            keterangan,
            harga: harga,
            qty: effectiveQty, // base units for stock
            displayQty: qty, // shown in table with chosen unit
            satuan,
            satuanBesar,
            diskon,
            panjang,
            total
        };

        items.push(newItem);
        renderItems();
        calculateTotals();

        // Reset form
        row.find('select, input').val('');
        row.find('.item-satuan-kecil').html('<option value="LBR">LBR</option>');
        row.find('.item-satuan-besar').empty();
        row.find('.item-satuan').val('LBR');
    });

    // Add item from large form
    $('#addItemBtnLarge').click(function(){
        const row = $(this).closest('.item-row-large');
        const kodeBarangSelect = row.find('.item-barang-large');
        const selectedOption = kodeBarangSelect.find('option:selected');

        const kodeBarang = selectedOption.data('kode');
        const namaBarang = selectedOption.data('nama');
        const merek = selectedOption.data('merek') || '';
        const kodeBarangId = kodeBarangSelect.val();
        const keterangan = row.find('#keterangan_large').val();
        const harga = parseFloat(row.find('.item-harga-large').val()) || 0;
        const qty = parseFloat(row.find('.item-qty-large').val()) || 0;
        const satuan = row.find('.item-satuan-large').val();
        const satuanBesar = row.find('.item-satuan-besar-large').val();
        const diskon = parseFloat(row.find('#diskon_large').val()) || 0;
        const panjang = parseFloat(row.find('#panjang_large').val()) || 0;

        if (!kodeBarangId || !kodeBarang || !namaBarang || !harga || !qty || !satuan) {
            alert('Mohon lengkapi data barang (satuan besar)!');
            return;
        }

        // Calculate total with conversion (large unit always selected)
        const conversionMap = row.data('conversionMap') || {};
        const factor = (satuan && conversionMap[satuan]) ? parseFloat(conversionMap[satuan]) : 1;
        // FIX: Untuk satuan besar, konversi ke satuan dasar dengan MENGALIKAN
        const effectiveQty = qty * factor;
        const subtotal = harga * effectiveQty;
        const diskonAmount = (subtotal * diskon) / 100;
        const total = subtotal - diskonAmount;

        const newItem = {
            kodeBarang,
            namaBarang,
            merek,
            keterangan,
            harga: harga,
            qty: effectiveQty, // base units for stock
            displayQty: qty, // shown in table with chosen unit
            satuan, // selected large unit
            satuanBesar, // keep explicit large unit for table column
            diskon,
            panjang,
            total
        };

        items.push(newItem);
        renderItems();
        calculateTotals();

        // Reset large form
        row.find('select, input').val('');
        row.find('.item-satuan-besar-large').empty();
        row.find('.item-satuan-large').val('');
    });

    // Function to render items table
    function renderItems() {
        const tbody = $('#itemsList');
        tbody.empty();

        items.forEach((item, index) => {
            tbody.append(`
                <tr>
                    <td>${item.kodeBarang}</td>
                    <td>${item.namaBarang}</td>
                    <td>${item.merek || '-'}</td>
                    <td>${item.ukuran || '-'}</td>
                    <td>${item.keterangan || '-'}</td>
                    <td class="text-right">${formatCurrency(item.harga)}</td>
                    <td>${(item.displayQty ?? item.qty)} ${item.satuan || 'LBR'}</td>
                    <td>${item.satuanBesar || '-'}</td>
                    <td class="text-right">${formatCurrency(item.total)}</td>
                    <td class="text-center">${item.panjang || '-'}</td>
                    <td class="text-right">${item.diskon || 0}%</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Remove item handling
        $(".remove-item").click(function () {
            const index = $(this).data("index");
            items.splice(index, 1);
            renderItems();
            calculateTotals();
        });
    }

    // Calculate all totals
    function calculateTotals() {
        // Calculate subtotal
        const subtotal = items.reduce((sum, item) => {
            const itemDiskon = (item.total * item.diskon) / 100;
            return sum + (item.total - itemDiskon);
        }, 0);

        $("#total").val(formatCurrency(subtotal));

        // Calculate discount
        let discountAmount = 0;
        if ($("#discount_checkbox").is(":checked")) {
            const discountPercent =
                parseFloat($("#discount_percent").val()) || 0;
            discountAmount = (subtotal * discountPercent) / 100;
        }
        $("#discount_amount").val(formatCurrency(discountAmount));

        // Calculate PPN (only if enabled by hidden rate)
        let ppnAmount = 0;
        if ($("#ppn_checkbox").is(":checked")) {
            const rate = parseFloat($("#ppn_rate_hidden").val() || '0') || 0;
            ppnAmount = ((subtotal - discountAmount) * rate) / 100;
        }
        $("#ppn_amount").val(formatCurrency(ppnAmount));

        // Calculate grand total with rounding
        grandTotal = Math.round(subtotal - discountAmount + ppnAmount);
        $("#grand_total").val(formatCurrency(grandTotal));
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat("id-ID").format(amount);
    }

    // Save transaction
    $("#saveTransaction").click(function () {
    if (confirm("Apakah Anda yakin ingin menyimpan?")) {
        if (!$("#kode_supplier").val()) {
            alert("Pilih supplier dari daftar yang tersedia!");
            return;
        }

        if (items.length === 0) {
            alert("Tidak ada barang yang ditambahkan!");
            return;
        }

        // IMPORTANT: Changed 'supplier' to 'kode_supplier' to match the database column
        const transactionData = {
            nota: $("#no_nota").val(),
            no_po: $("#no_po").val(), // FIX: Tambahkan field no_po
            no_surat_jalan: $("#no_surat_jalan").val(),
            tanggal: $("#tanggal").val(),
            kode_supplier: $("#kode_supplier").val(), // This field name must match your database column
            pembayaran: $("#pembayaran").val(),
            cara_bayar: $("#cara_bayar").val(),
            hari_tempo: parseInt($("#hari_tempo").val() || '0', 10),
            tanggal_jatuh_tempo: $("#tanggal_jatuh_tempo").val() || null,
            items: items,
            subtotal: parseFloat(
                $("#total").val().replace(/\./g, "").replace(/,/g, ".")
            ),
            diskon: parseFloat(
                $("#discount_amount")
                    .val()
                    .replace(/\./g, "")
                    .replace(/,/g, ".")
            ),
            ppn: parseFloat(
                $("#ppn_amount").val().replace(/\./g, "").replace(/,/g, ".")
            ),
            grand_total: grandTotal,
        };

        // Send data to backend
        $.ajax({
            url: window.storeTransactionUrl,
            method: "POST",
            data: transactionData,
            headers: {
                "X-CSRF-TOKEN": window.csrfToken,
            },
            success: function (response) {
                // Tampilkan modal invoice
                $("#invoiceNota").text(response.nota);
                $("#invoiceTanggal").text(response.tanggal);
                $("#invoiceSupplier").text(
                    response.supplier_name || response.kode_supplier
                );
                $("#invoiceGrandTotal").text(
                    "Rp " +
                        new Intl.NumberFormat("id-ID").format(
                            response.grand_total || 0
                        )
                );

                // Simpan ID transaksi untuk tombol Print
                const transactionId = response.id;

                // --- PERUBAHAN DI SINI ---
                // Tombol Print
                $("#printInvoiceBtn")
                    .off("click")
                    .on("click", function () {
                        // Buat URL dengan parameter auto_print=1 dan buka di tab baru
                        const printUrl = `${window.printInvoiceUrl}${transactionId}?auto_print=1`;
                        window.open(printUrl, '_blank');
                    });
                
                // Tombol Kembali
                $("#backToFormBtn")
                    .off("click")
                    .on("click", function () {
                        window.location.href = window.backToPembelian;
                    });

                $("#invoiceModal").modal("show");
            },
            error: function (xhr) {
                alert(
                    "Terjadi kesalahan: " +
                        (xhr.responseJSON
                            ? xhr.responseJSON.message
                            : xhr.statusText)
                );
            },
        });
    }
});

    // Cancel transaction
    $("#cancelTransaction").click(function () {
        if (confirm("Batalkan transaksi? Semua data akan hilang.")) {
            $("#transactionForm")[0].reset();
            items = [];
            renderItems();
            calculateTotals();
        }
    });

    // Enhanced Kode Barang search modal
    $("#searchKodeBarangBtn").click(function () {
        const keyword = $("#searchKodeBarangInput").val();
        if (keyword.length > 0) {
            $.ajax({
                url: window.kodeBarangSearchUrl,
                method: "GET",
                data: { keyword },
                success: function (data) {
                    let html = "";
                    if (data.length > 0) {
                        data.forEach((item) => {
                            html += `<tr>
                                <td>${item.kode_barang}</td>
                                <td>${item.name}</td>
                                <td>${item.length} m</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-kode-barang"
                                        data-kode="${item.kode_barang}" 
                                        data-name="${item.attribute}"
                                        data-length="${item.length}"
                                        data-cost="${item.cost}">
                                        <i class="fas fa-check"></i> Pilih
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html =
                            '<tr><td colspan="4" class="text-center">Tidak ada data ditemukan</td></tr>';
                    }
                    $("#kodeBarangSearchResults").html(html);
                },
                error: function () {
                    alert("Terjadi kesalahan saat mencari kode barang.");
                },
            });
        } else {
            alert("Masukkan kata kunci pencarian!");
        }
    });

    // Select Kode Barang from search modal
    $(document).on("click", ".select-kode-barang", function () {
        const kodeBarang = $(this).data("kode");
        const namaBarang = $(this).data("name");
        const length = $(this).data("length");
        const cost = $(this).data("cost");

        // Find the option in the dropdown and select it
        const option = $(`.item-barang option[data-kode="${kodeBarang}"]`);
        if (option.length > 0) {
            option.prop('selected', true);
            $('.item-barang').trigger('change');
        }

        // Set other fields
        $('.item-harga').val(cost);
        $('#panjang').val(length);
        $('#keterangan').val(namaBarang);

        $("#kodeBarangSearchModal").modal("hide");
    });
});
