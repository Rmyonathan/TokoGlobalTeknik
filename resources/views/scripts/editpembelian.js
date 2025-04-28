// EditPembelian.js
$(document).ready(function() {
    // Initialize variables
    let items = initialItems || [];
    let grandTotal = parseFloat("{{ $purchase->grand_total }}");

    // Render initial items
    renderItems();

    // Search suppliers
    $('#supplier').on('input', function () {
        const keyword = $(this).val();
        if (keyword.length > 0) {
            $.ajax({
                url: window.supplierSearchUrl,
                method: "GET",
                data: { keyword },
                success: function (data) {
                    let dropdown = '';
                    if (data.length > 0) {
                        data.forEach(supplier => {
                            dropdown += `<a class="dropdown-item supplier-item" data-kode="${supplier.kode_supplier}" data-name="${supplier.nama}">${supplier.kode_supplier} - ${supplier.nama}</a>`;
                        });
                    } else {
                        dropdown = '<a class="dropdown-item disabled">Tidak ada supplier ditemukan</a>';
                    }
                    $('#supplierDropdown').html(dropdown).show();
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat mencari supplier.');
                }
            });
        } else {
            $('#supplierDropdown').hide();
        }
    });

    // Select Supplier
    $(document).on('click', '.supplier-item', function () {
        const kodeSupplier = $(this).data('kode');
        const namaSupplier = $(this).data('name');
        $('#kode_supplier').val(kodeSupplier);
        $('#supplier').val(`${kodeSupplier} - ${namaSupplier}`);
        $('#supplierDropdown').hide();
    });

    // Hide dropdown when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#supplier, #supplierDropdown').length) {
            $('#supplierDropdown').hide();
        }
    });
    
    // Toggle discount inputs
    $('#discount_checkbox').change(function() {
        $('#discount_percent').prop('disabled', !this.checked);
        calculateTotals();
    });
    
    $('#ppn_checkbox').change(function() {
        calculateTotals();
    });
    
    // Calculate input changes
    $('#discount_percent').on('input', function() {
        calculateTotals();
    });
    
    // Preview item in modal
    $('#harga, #quantity, #panjang, #diskon').on('input', function() {
        updateItemPreview();
    });
    
    function updateItemPreview() {
        const kodeBarang = $('#kode_barang').val() || '-';
        const keterangan = $('#keterangan').val() || '-';
        const harga = parseInt($('#harga').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const panjang = parseFloat($('#panjang').val()) || 0;
        const satuan = $('#satuan').val();
        const diskon = parseInt($('#diskon').val()) || 0;
        
        // Calculate values
        const total = harga * quantity;
        const diskonAmount = (total * diskon) / 100;
        const subTotal = total - diskonAmount;    
        
        // Update preview
        const tbody = $('#itemPreview');
        tbody.empty();
        
        tbody.append(`
            <tr>
                <td>${kodeBarang}</td>
                <td>${keterangan}</td>
                <td class="text-right">${formatCurrency(harga)}</td>
                <td>${quantity}</td>
                <td>${panjang > 0 ? panjang + ' m' : '-'}</td>
                <td class="text-right">${formatCurrency(total)}</td>
                <td>${satuan}</td>
                <td>${diskon}%</td>
                <td class="text-right">${formatCurrency(subTotal)}</td>
            </tr>
        `);
    }
    
    // Add item to the table
    $('#saveItemBtn').click(function() {
        const kodeBarang = $('#kode_barang').val();
        const namaBarang = $('#nama_barang').val();
        const keterangan = $('#keterangan').val();
        const harga = parseInt($('#harga').val()) || 0;
        const qty = parseInt($('#quantity').val()) || 0;
        const panjang = parseFloat($('#panjang').val()) || 0;
        const diskon = parseInt($('#diskon').val()) || 0;
        
        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data barang!');
            return;
        }
        
        const total = harga * qty;
        
        const newItem = {
            kodeBarang, namaBarang, keterangan, harga, qty, panjang, diskon, total
        };
        
        items.push(newItem);
        renderItems();
        calculateTotals();
        
        // Reset form and close modal
        $('#addItemForm')[0].reset();
        $('#itemPreview').empty();
        $('#addItemModal').modal('hide');
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
                    <td>${item.keterangan || '-'}</td>
                    <td class="text-right">${formatCurrency(item.harga)}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-center">${item.panjang > 0 ? item.panjang + ' m' : '-'}</td>
                    <td class="text-right">${formatCurrency(item.total)}</td>
                    <td class="text-right">${item.diskon}%</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        
        // Remove item handling
        $('.remove-item').click(function() {
            const index = $(this).data('index');
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
        
        $('#total').val(formatCurrency(subtotal));
        
        // Calculate discount
        let discountAmount = 0;
        if ($('#discount_checkbox').is(':checked')) {
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            discountAmount = (subtotal * discountPercent) / 100;
        }
        $('#discount_amount').val(formatCurrency(discountAmount));
        
        // Calculate PPN
        let ppnAmount = 0;
        if ($('#ppn_checkbox').is(':checked')) {
            ppnAmount = ((subtotal - discountAmount) * 11) / 100; // Using 11% for PPN
        }
        $('#ppn_amount').val(formatCurrency(ppnAmount));
        
        // Calculate grand total
        grandTotal = subtotal - discountAmount + ppnAmount;
        $('#grand_total').val(formatCurrency(grandTotal));
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
    
    // Update transaction
    $('#updateTransaction').click(function() {
        if (confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
            if (!$('#kode_supplier').val()) {
                alert('Pilih supplier dari daftar yang tersedia!');
                return;
            }

            if (items.length === 0) {
                alert('Tidak ada barang yang ditambahkan!');
                return;
            }
            
            const transactionData = {
                _token: window.csrfToken,
                tanggal: $('#tanggal').val(),
                kode_supplier: $('#kode_supplier').val(),
                cabang: $('#cabang').val(),
                pembayaran: $('#pembayaran').val(),
                cara_bayar: $('#cara_bayar').val(),
                items: items,
                subtotal: parseFloat($('#total').val().replace(/\./g, '').replace(/,/g, '.')),
                diskon: parseFloat($('#discount_amount').val().replace(/\./g, '').replace(/,/g, '.')),
                ppn: parseFloat($('#ppn_amount').val().replace(/\./g, '').replace(/,/g, '.')),
                grand_total: grandTotal
            };
            
            // Send data to backend
            $.ajax({
                url: window.updateTransactionUrl,
                method: "POST",
                data: transactionData,
                success: function(response) {
                    alert('Transaksi berhasil diperbarui!');
                    window.location.href = window.notaShowUrl;
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }
    });
    
    // Initialize item preview
    updateItemPreview();
});