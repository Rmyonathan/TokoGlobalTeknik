// Purchase Transaction JavaScript
$(document).ready(function () {
    // Initialize variables
    let items = [];
    let grandTotal = 0;

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

    // NEW: Search Kode Barang
    $("#kode_barang").on("input", function () {
        const keyword = $(this).val();
        if (keyword.length > 0) {
            $.ajax({
                url: window.kodeBarangSearchUrl, // Make sure to define this in your blade template
                method: "GET",
                data: { keyword },
                success: function (data) {
                    let dropdown = "";
                    if (data.length > 0) {
                        data.forEach((item) => {
                            dropdown += `<a class="dropdown-item kode-barang-item" 
                                            data-kode="${item.kode_barang}" 
                                            data-name="${item.attribute}"
                                            data-length="${item.length}"
                                            data-cost="${item.cost}">
                                        ${item.kode_barang} - ${item.attribute} (${item.length} m)
                                        </a>`;
                        });
                    } else {
                        dropdown =
                            '<a class="dropdown-item disabled">Kode barang tidak ditemukan! Tambahkan di Master Data.</a>';
                    }
                    $("#kodeBarangDropdown").html(dropdown).show();
                },
                error: function (xhr, status, error) {
                    console.error("Error searching kode barang:", error);
                    alert("Terjadi kesalahan saat mencari kode barang.");
                },
            });
        } else {
            $("#kodeBarangDropdown").hide();
        }
    });

    // Select Kode Barang
   $(document).on("click", ".kode-barang-item", function () {
        const kodeBarang = $(this).data("kode");
        const namaBarang = $(this).data("name");
        const length = $(this).data("length");
        const cost = $(this).data("cost");

        $("#kode_barang").val(kodeBarang);
        $("#nama_barang").val(namaBarang);
        $("#panjang").val(length);
        $("#harga").val(cost);
        
        // Get panel name with AJAX request
        $.ajax({
            url: window.getPanelInfoUrl,
            method: "GET",
            data: { kode_barang: kodeBarang },
            success: function(response) {
                if (response.success && response.panel_name) {
                    // Set both Nama Barang and Keterangan to the same Panel name
                    $("#nama_barang").val(response.panel_name); // Update this line
                    $("#keterangan").val(response.panel_name);
                } else {
                    $("#keterangan").val(namaBarang);
                }
                updateItemPreview();
            },
            error: function() {
                $("#keterangan").val(namaBarang);
                updateItemPreview();
            }
        });
        
        $("#kodeBarangDropdown").hide();
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

    // Preview item in modal
    $("#harga, #quantity, #panjang, #diskon").on("input", function () {
        updateItemPreview();
    });

    function updateItemPreview() {
        const kodeBarang = $("#kode_barang").val() || "-";
        const keterangan = $("#keterangan").val() || "-";
        const harga = parseInt($("#harga").val()) || 0;
        const quantity = parseInt($("#quantity").val()) || 0;
        const panjang = parseFloat($("#panjang").val()) || 0;
        const satuan = $("#satuan").val();
        const diskon = parseInt($("#diskon").val()) || 0;

        // Calculate values
        const total = harga * quantity;
        const diskonAmount = (total * diskon) / 100;
        const subTotal = total - diskonAmount;

        // Update preview
        const tbody = $("#itemPreview");
        tbody.empty();

        tbody.append(`
            <tr>
                <td>${kodeBarang}</td>
                <td>${keterangan}</td>
                <td class="text-right">${formatCurrency(harga)}</td>
                <td>${quantity}</td>
                <td>${
                    panjang > 0 ? panjang + " m" : "-"
                }</td> <!-- Display with meters unit -->
                <td class="text-right">${formatCurrency(total)}</td>
                <td>${satuan}</td>
                <td>${diskon}%</td>
                <td class="text-right">${formatCurrency(subTotal)}</td>
            </tr>
        `);
    }

    // Add item to the table
    $("#saveItemBtn").click(function () {
        const kodeBarang = $("#kode_barang").val();
        const namaBarang = $("#nama_barang").val();
        const keterangan = $("#keterangan").val();
        const harga = parseInt($("#harga").val()) || 0;
        const qty = parseInt($("#quantity").val()) || 0;
        const panjang = parseFloat($("#panjang").val()) || 0;
        const diskon = parseInt($("#diskon").val()) || 0;

        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert("Mohon lengkapi data barang!");
            return;
        }

        // Check if kode_barang is valid by searching for it
        $.ajax({
            url: window.kodeBarangSearchUrl,
            method: "GET",
            data: { keyword: kodeBarang },
            async: false, // Make this synchronous to ensure check completes before continuing
            success: function (data) {
                // If no matching kode barang found
                if (
                    data.length === 0 ||
                    !data.some((item) => item.kode_barang === kodeBarang)
                ) {
                    alert(
                        "Kode barang tidak terdaftar! Silakan tambahkan di Master Data terlebih dahulu."
                    );
                    return false;
                }

                // If valid, add item
                const total = harga * qty;

                const newItem = {
                    kodeBarang,
                    namaBarang,
                    keterangan,
                    harga,
                    qty,
                    panjang,
                    diskon,
                    total,
                };

                items.push(newItem);
                renderItems();
                calculateTotals();

                // Reset form and close modal
                $("#addItemForm")[0].reset();
                $("#itemPreview").empty();
                $("#addItemModal").modal("hide");
            },
            error: function () {
                alert("Terjadi kesalahan saat memvalidasi kode barang.");
            },
        });
    });

    // Item search functionality
    $("#findItem").click(function () {
        $("#kodeBarangSearchModal").modal("show");
    });

    // Function to render items table
    function renderItems() {
        const tbody = $("#itemsList");
        tbody.empty();

        items.forEach((item, index) => {
            tbody.append(`
                <tr>
                    <td>${item.kodeBarang}</td>
                    <td>${item.namaBarang}</td>
                    <td>${item.keterangan || "-"}</td>
                    <td class="text-right">${formatCurrency(item.harga)}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-center">${
                        item.panjang > 0 ? item.panjang + " m" : "-"
                    }</td> <!-- Display with meters unit -->
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

        // Calculate PPN
        let ppnAmount = 0;
        if ($("#ppn_checkbox").is(":checked")) {
            ppnAmount = ((subtotal - discountAmount) * 11) / 100; // Using 11% for PPN
        }
        $("#ppn_amount").val(formatCurrency(ppnAmount));

        // Calculate grand total
        grandTotal = subtotal - discountAmount + ppnAmount;
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
                tanggal: $("#tanggal").val(),
                kode_supplier: $("#kode_supplier").val(), // This field name must match your database column
                cabang: $("#cabang").val(),
                pembayaran: $("#pembayaran").val(),
                cara_bayar: $("#cara_bayar").val(),
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

                    // Tombol Print
                    $("#printInvoiceBtn")
                        .off("click")
                        .on("click", function () {
                            window.location.href =
                                window.printInvoiceUrl + transactionId;
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

    // Initialize item preview
    updateItemPreview();

    // Kode Barang search modal
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
                                <td>${item.attribute}</td>
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
        }
    });

    // Search Stok Owner (Cabang)
$("#cabang_display").on("input", function () {
    console.log("Cabang input changed:", $(this).val());
    const keyword = $(this).val();
    if (keyword.length > 0) {
        console.log("About to send AJAX request for StokOwner:", window.stokOwnerSearchUrl);
        $.ajax({
            url: window.stokOwnerSearchUrl,
            method: "GET",
            data: { keyword },
            success: function (data) {
                console.log("StokOwner search results:", data);
                let dropdown = "";
                if (data && data.length > 0) {
                    data.forEach((stokOwner) => {
                        dropdown += `<a class="dropdown-item cabang-item" data-kode="${stokOwner.kode_stok_owner}" data-name="${stokOwner.keterangan}">${stokOwner.kode_stok_owner} - ${stokOwner.keterangan}</a>`;
                    });
                } else {
                    dropdown = '<a class="dropdown-item disabled">Tidak ada stok owner ditemukan</a>';
                }
                $("#cabangDropdown").html(dropdown).show();
            },
            error: function (xhr, status, error) {
                console.error("Error searching StokOwner:", xhr.responseText);
                console.error("Status:", status);
                console.error("Error:", error);
                alert("Terjadi kesalahan saat mencari stok owner.");
            },
        });
    } else {
        $("#cabangDropdown").hide();
    }
});

// Select Cabang (StokOwner)
$(document).on("click", ".cabang-item", function () {
    console.log("StokOwner item clicked:", $(this).data());
    const kodeCabang = $(this).data("kode");
    const namaCabang = $(this).data("name");
    $("#cabang").val(kodeCabang); // Set the hidden field with the kode_stok_owner
    $("#cabang_display").val(`${kodeCabang} - ${namaCabang}`); // Display the readable version
    $("#cabangDropdown").hide();
});

// Update the document click handler to include cabang dropdown
$(document).click(function (e) {
    if (
        !$(e.target).closest(
            "#supplier, #supplierDropdown, #kode_barang, #kodeBarangDropdown, #cabang_display, #cabangDropdown"
        ).length
    ) {
        $("#supplierDropdown").hide();
        $("#kodeBarangDropdown").hide();
        $("#cabangDropdown").hide();
    }
});
    // Select Kode Barang from search modal
    $(document).on("click", ".select-kode-barang", function () {
        const kodeBarang = $(this).data("kode");
        const namaBarang = $(this).data("name");
        const length = $(this).data("length");
        const cost = $(this).data("cost");

        $("#kode_barang").val(kodeBarang);
        $("#nama_barang").val(namaBarang);
        $("#panjang").val(length);
        $("#harga").val(cost);
        
        // Get panel name with AJAX request
        $.ajax({
            url: window.getPanelInfoUrl,
            method: "GET",
            data: { kode_barang: kodeBarang },
            success: function(response) {
                if (response.success && response.panel_name) {
                    // Set both Nama Barang and Keterangan to the same Panel name
                    $("#nama_barang").val(response.panel_name); // Update this line
                    $("#keterangan").val(response.panel_name);
                } else {
                    $("#keterangan").val(namaBarang);
                }
                updateItemPreview();
            },
            error: function() {
                $("#keterangan").val(namaBarang);
                updateItemPreview();
            }
        });

        $("#kodeBarangSearchModal").modal("hide");
    });
});
