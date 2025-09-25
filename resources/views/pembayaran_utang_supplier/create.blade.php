@extends('layout.Nav')

@section('content')
<style>
.invoice-amount:invalid,
.nota-debit-amount:invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invoice-amount:valid,
.nota-debit-amount:valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.input-error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>Tambah Pembayaran Utang ke Supplier
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('pembayaran-utang-supplier.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <form id="pembayaranForm" method="POST" action="{{ route('pembayaran-utang-supplier.store') }}">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Header Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_pembayaran">No Pembayaran</label>
                                    <input type="text" class="form-control" id="no_pembayaran" 
                                           value="{{ $noPembayaran }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_bayar">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_bayar" name="tanggal_bayar" 
                                           value="{{ old('tanggal_bayar', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id">Pilih Supplier <span class="text-danger">*</span></label>
                                    <select class="form-control" id="supplier_id" name="supplier_id" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->nama }} ({{ $supplier->kode_supplier }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metode_pembayaran">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-control" id="metode_pembayaran" name="metode_pembayaran" required>
                                        <option value="">Pilih Metode</option>
                                        @foreach(($caraBayars ?? collect()) as $metode => $items)
                                            <option value="{{ $metode }}" {{ old('metode_pembayaran') == $metode ? 'selected' : '' }}>{{ $metode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cara_bayar">Cara Bayar <span class="text-danger">*</span></label>
                                    <select class="form-control" id="cara_bayar" name="cara_bayar" required>
                                        <option value="">Pilih Cara Bayar</option>
                                        @foreach(($caraBayars ?? collect()) as $metode => $items)
                                            <optgroup label="{{ $metode }}">
                                                @foreach($items as $cb)
                                                    <option value="{{ $cb->nama }}" {{ old('cara_bayar') == $cb->nama ? 'selected' : '' }}>{{ $cb->nama }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_referensi">No Referensi</label>
                                    <input type="text" class="form-control" id="no_referensi" name="no_referensi" 
                                           value="{{ old('no_referensi') }}" placeholder="No cek, no transfer, dll">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="keterangan">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" 
                                              rows="2" placeholder="Keterangan tambahan...">{{ old('keterangan') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Invoices Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-file-invoice mr-2"></i>Faktur Pembelian yang Akan Dibayar</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="invoicesTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">Pilih</th>
                                                <th width="15%">No Pembelian</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Total Faktur</th>
                                                <th width="15%">Sudah Dibayar</th>
                                                <th width="15%">Sisa Utang</th>
                                                <th width="15%">Jumlah Dilunasi</th>
                                                <th width="8%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoicesTableBody">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    Pilih supplier terlebih dahulu untuk melihat faktur
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Nota Debit Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-receipt mr-2"></i>Nota Debit sebagai Pemotong</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="notaDebitTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">Pilih</th>
                                                <th width="15%">No Nota Debit</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="20%">Total Nota Debit</th>
                                                <th width="15%">Sudah Digunakan</th>
                                                <th width="15%">Sisa Nota Debit</th>
                                                <th width="15%">Jumlah Digunakan</th>
                                                <th width="3%">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="notaDebitTableBody">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    Pilih supplier terlebih dahulu untuk melihat nota debit
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Section -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Ringkasan Pembayaran</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Checkbox Lunasi Semua -->
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="lunasiSemua" onchange="toggleLunasiSemua(this)">
                                            <label class="form-check-label" for="lunasiSemua">
                                                <strong>Lunasi Semua Utang</strong>
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Total Faktur:</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="totalFaktur">Rp 0</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Total Nota Debit:</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="totalNotaDebit">Rp 0</span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Total Pembayaran:</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="totalPembayaran">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_bayar">Total yang Dibayar <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="total_bayar" name="total_bayar" 
                                           value="{{ old('total_bayar', 0) }}" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Simpan Pembayaran
                        </button>
                        <a href="{{ route('pembayaran-utang-supplier.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let selectedInvoices = [];
let selectedNotaDebits = [];

document.getElementById('supplier_id').addEventListener('change', function() {
    const supplierId = this.value;
    
    if (supplierId) {
        loadSupplierInvoices(supplierId);
        loadSupplierNotaDebits(supplierId);
    } else {
        clearTables();
    }
});

function loadSupplierInvoices(supplierId) {
    fetch(`/api/pembayaran-utang-supplier/supplier-invoices?supplier_id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('invoicesTableBody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Tidak ada faktur yang belum dibayar</td></tr>';
                return;
            }
            
            data.forEach(invoice => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="checkbox" class="invoice-checkbox" 
                               data-invoice-id="${invoice.id}" 
                               data-total="${invoice.sisa_utang}"
                               onchange="toggleInvoice(this)">
                    </td>
                    <td>${invoice.no_pembelian}</td>
                    <td>${invoice.tanggal}</td>
                    <td class="text-right">Rp ${formatNumber(invoice.total_faktur)}</td>
                    <td class="text-right">Rp ${formatNumber(invoice.sudah_dibayar)}</td>
                    <td class="text-right">Rp ${formatNumber(invoice.sisa_utang)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm invoice-amount" 
                               data-invoice-id="${invoice.id}" 
                               value="${invoice.sisa_utang}" 
                               max="${invoice.sisa_utang}" 
                               min="0" 
                               step="0.01"
                               onchange="updateInvoiceAmount(this)"
                               oninput="validateInvoiceAmount(this)"
                               onkeypress="return isNumberKey(event)"
                               placeholder="Masukkan jumlah"
                               data-raw-max="${invoice.sisa_utang}">
                    </td>
                    <td>
                        <span class="badge badge-${invoice.status_utang === 'lunas' ? 'success' : 'warning'}">
                            ${invoice.status_utang}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading invoices:', error);
            document.getElementById('invoicesTableBody').innerHTML = 
                '<tr><td colspan="8" class="text-center text-danger">Error loading invoices</td></tr>';
        });
}

function loadSupplierNotaDebits(supplierId) {
    fetch(`/api/pembayaran-utang-supplier/supplier-nota-debits?supplier_id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('notaDebitTableBody');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Tidak ada nota debit yang tersedia</td></tr>';
                return;
            }
            
            data.forEach(notaDebit => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="checkbox" class="nota-debit-checkbox" 
                               data-nota-debit-id="${notaDebit.id}" 
                               data-total="${notaDebit.sisa_nota_debit}"
                               onchange="toggleNotaDebit(this)">
                    </td>
                    <td>${notaDebit.no_nota_debit}</td>
                    <td>${notaDebit.tanggal}</td>
                    <td class="text-right">Rp ${formatNumber(notaDebit.total_nota_debit)}</td>
                    <td class="text-right">Rp ${formatNumber(notaDebit.sudah_digunakan)}</td>
                    <td class="text-right">Rp ${formatNumber(notaDebit.sisa_nota_debit)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm nota-debit-amount" 
                               data-nota-debit-id="${notaDebit.id}" 
                               value="${notaDebit.sisa_nota_debit}" 
                               max="${notaDebit.sisa_nota_debit}" 
                               min="0" 
                               step="0.01"
                               onchange="updateNotaDebitAmount(this)"
                               oninput="validateNotaDebitAmount(this)"
                               onkeypress="return isNumberKey(event)"
                               placeholder="Masukkan jumlah"
                               data-raw-max="${notaDebit.sisa_nota_debit}">
                    </td>
                    <td>${notaDebit.keterangan || '-'}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading nota debits:', error);
            document.getElementById('notaDebitTableBody').innerHTML = 
                '<tr><td colspan="8" class="text-center text-danger">Error loading nota debits</td></tr>';
        });
}

function toggleInvoice(checkbox) {
    const invoiceId = checkbox.dataset.invoiceId;
    const amountInput = document.querySelector(`input[data-invoice-id="${invoiceId}"].invoice-amount`);
    
    if (checkbox.checked) {
        selectedInvoices.push({
            id: invoiceId,
            amount: parseFloat(amountInput.value)
        });
        amountInput.disabled = false;
    } else {
        selectedInvoices = selectedInvoices.filter(inv => inv.id !== invoiceId);
        amountInput.disabled = true;
        amountInput.value = amountInput.max;
    }
    
    updateSummary();
    updateLunasiSemuaStatus();
}

function toggleNotaDebit(checkbox) {
    const notaDebitId = checkbox.dataset.notaDebitId;
    const amountInput = document.querySelector(`input[data-nota-debit-id="${notaDebitId}"].nota-debit-amount`);
    
    if (checkbox.checked) {
        selectedNotaDebits.push({
            id: notaDebitId,
            amount: parseFloat(amountInput.value)
        });
        amountInput.disabled = false;
    } else {
        selectedNotaDebits = selectedNotaDebits.filter(nd => nd.id !== notaDebitId);
        amountInput.disabled = true;
        amountInput.value = amountInput.max;
    }
    
    updateSummary();
    updateLunasiSemuaStatus();
}

function validateInvoiceAmount(input) {
    // Validasi real-time saat user mengetik - hanya beri feedback visual, jangan ubah nilai
    const inputValue = input.value.trim();
    const value = parseFloat(inputValue);
    
    // Gunakan data-raw-max sebagai fallback jika input.max tidak valid
    let maxValue = parseFloat(input.max);
    if (isNaN(maxValue) || maxValue === 0) {
        maxValue = parseFloat(input.dataset.rawMax) || 0;
    }
    
    // Hapus class validasi sebelumnya
    input.classList.remove('is-valid', 'is-invalid');
    
    // Jika input kosong, jangan validasi
    if (inputValue === '' || inputValue === '0') {
        return;
    }

    // Beri feedback visual berdasarkan validitas
    if (isNaN(value) || value < 0) {
        input.classList.add('is-invalid');
        console.log('Invalid: NaN or negative');
    } else if (maxValue > 0 && value > maxValue) {
        input.classList.add('is-invalid');
        console.log('Invalid: exceeds maximum');
    } else {
        input.classList.add('is-valid');
        console.log('Valid input');
    }
}

function validateNotaDebitAmount(input) {
    // Validasi real-time saat user mengetik untuk nota debit - hanya beri feedback visual
    const inputValue = input.value.trim();
    const value = parseFloat(inputValue);
    
    // Gunakan data-raw-max sebagai fallback jika input.max tidak valid
    let maxValue = parseFloat(input.max);
    if (isNaN(maxValue) || maxValue === 0) {
        maxValue = parseFloat(input.dataset.rawMax) || 0;
    }
    
    // Hapus class validasi sebelumnya
    input.classList.remove('is-valid', 'is-invalid');
    
    // Jika input kosong, jangan validasi
    if (inputValue === '' || inputValue === '0') {
        return;
    }
    
    // Beri feedback visual berdasarkan validitas
    if (isNaN(value) || value < 0) {
        input.classList.add('is-invalid');
    } else if (maxValue > 0 && value > maxValue) {
        input.classList.add('is-invalid');
    } else {
        input.classList.add('is-valid');
    }
}

function isNumberKey(evt) {
    // Hanya izinkan angka, titik, dan tombol kontrol
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function updateInvoiceAmount(input) {
    const invoiceId = input.dataset.invoiceId;
    const invoice = selectedInvoices.find(inv => inv.id === invoiceId);
    
    // Validasi dan koreksi nilai hanya saat onchange (bukan saat oninput)
    const value = parseFloat(input.value) || 0;
    
    // Gunakan data-raw-max sebagai fallback jika input.max tidak valid
    let maxValue = parseFloat(input.max);
    if (isNaN(maxValue) || maxValue === 0) {
        maxValue = parseFloat(input.dataset.rawMax) || 0;
    }
    
    const correctedValue = Math.max(0, Math.min(value, maxValue));
    
    // Update nilai input jika ada koreksi
    if (correctedValue !== value) {
        input.value = correctedValue;
    }
    
    if (invoice) {
        invoice.amount = correctedValue;
        updateSummary();
    } else {
        // Jika invoice belum ada di selectedInvoices, tambahkan
        selectedInvoices.push({
            id: invoiceId,
            amount: correctedValue
        });
        updateSummary();
    }
}

function updateNotaDebitAmount(input) {
    const notaDebitId = input.dataset.notaDebitId;
    const notaDebit = selectedNotaDebits.find(nd => nd.id === notaDebitId);
    
    // Validasi dan koreksi nilai hanya saat onchange (bukan saat oninput)
    const value = parseFloat(input.value) || 0;
    
    // Gunakan data-raw-max sebagai fallback jika input.max tidak valid
    let maxValue = parseFloat(input.max);
    if (isNaN(maxValue) || maxValue === 0) {
        maxValue = parseFloat(input.dataset.rawMax) || 0;
    }
    
    const correctedValue = Math.max(0, Math.min(value, maxValue));
    
    // Update nilai input jika ada koreksi
    if (correctedValue !== value) {
        input.value = correctedValue;
    }
    
    if (notaDebit) {
        notaDebit.amount = correctedValue;
        updateSummary();
    } else {
        // Jika nota debit belum ada di selectedNotaDebits, tambahkan
        selectedNotaDebits.push({
            id: notaDebitId,
            amount: correctedValue
        });
        updateSummary();
    }
}

function updateSummary() {
    const totalFaktur = selectedInvoices.reduce((sum, inv) => sum + inv.amount, 0);
    const totalNotaDebit = selectedNotaDebits.reduce((sum, nd) => sum + nd.amount, 0);
    
    // Total Pembayaran = Total Faktur (jumlah dilunasi) - Total Nota Debit
    // Ini menunjukkan berapa banyak cash yang perlu dibayar
    const totalPembayaran = totalFaktur - totalNotaDebit;
    
    document.getElementById('totalFaktur').textContent = `Rp ${formatNumber(totalFaktur)}`;
    document.getElementById('totalNotaDebit').textContent = `Rp ${formatNumber(totalNotaDebit)}`;
    document.getElementById('totalPembayaran').textContent = `Rp ${formatNumber(totalPembayaran)}`;
    document.getElementById('total_bayar').value = totalPembayaran;
    
    // Update max values untuk input field Jumlah Dilunasi
    updateInvoiceMaxValues();
    
    // Update hidden inputs for form submission
    updatePaymentDetails();
    updateNotaDebitDetails();
}

function updatePaymentDetails() {
    // Remove existing payment details inputs
    document.querySelectorAll('input[name^="payment_details"]').forEach(input => input.remove());
    
    // Add new payment details inputs
    selectedInvoices.forEach((invoice, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `payment_details[${index}][pembelian_id]`;
        input.value = invoice.id;
        document.getElementById('pembayaranForm').appendChild(input);
        
        const amountInput = document.createElement('input');
        amountInput.type = 'hidden';
        amountInput.name = `payment_details[${index}][jumlah_dilunasi]`;
        amountInput.value = invoice.amount;
        document.getElementById('pembayaranForm').appendChild(amountInput);
    });
}

function updateNotaDebitDetails() {
    // Remove existing nota debit details inputs
    document.querySelectorAll('input[name^="nota_debit_details"]').forEach(input => input.remove());
    
    // Add new nota debit details inputs
    selectedNotaDebits.forEach((notaDebit, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `nota_debit_details[${index}][nota_debit_id]`;
        input.value = notaDebit.id;
        document.getElementById('pembayaranForm').appendChild(input);
        
        const amountInput = document.createElement('input');
        amountInput.type = 'hidden';
        amountInput.name = `nota_debit_details[${index}][jumlah_digunakan]`;
        amountInput.value = notaDebit.amount;
        document.getElementById('pembayaranForm').appendChild(amountInput);
    });
}

function updateInvoiceMaxValues() {
    // Hitung total nota debit yang dipilih
    const totalNotaDebit = selectedNotaDebits.reduce((sum, nd) => sum + nd.amount, 0);
    
    // Update max value untuk setiap input field Jumlah Dilunasi
    document.querySelectorAll('.invoice-amount').forEach(input => {
        const invoiceId = input.dataset.invoiceId;
        const originalMax = parseFloat(input.dataset.rawMax) || 0;
        
        // Max value = sisa_utang asli (tidak dikurangi nota debit)
        // Nota debit mengurangi tagihan, bukan mengurangi max input
        const newMax = originalMax;
        
        // Update max attribute
        input.max = newMax;
        
        // Jika nilai saat ini lebih besar dari max baru, kurangi nilainya
        const currentValue = parseFloat(input.value) || 0;
        if (currentValue > newMax) {
            input.value = newMax;
            // Update juga di selectedInvoices
            const invoice = selectedInvoices.find(inv => inv.id === invoiceId);
            if (invoice) {
                invoice.amount = newMax;
            }
        }
        
        // Update placeholder untuk memberikan panduan
        input.placeholder = `Maksimal: ${formatNumber(newMax)}`;
    });
}

function toggleLunasiSemua(checkbox) {
    if (checkbox.checked) {
        // Centang semua checkbox faktur yang tersedia
        const invoiceCheckboxes = document.querySelectorAll('.invoice-checkbox');
        invoiceCheckboxes.forEach(cb => {
            if (!cb.checked) {
                cb.checked = true;
                toggleInvoice(cb);
            }
        });
        
        // Centang semua checkbox nota debit yang tersedia
        const notaDebitCheckboxes = document.querySelectorAll('.nota-debit-checkbox');
        notaDebitCheckboxes.forEach(cb => {
            if (!cb.checked) {
                cb.checked = true;
                toggleNotaDebit(cb);
            }
        });
    } else {
        // Uncheck semua checkbox faktur
        const invoiceCheckboxes = document.querySelectorAll('.invoice-checkbox');
        invoiceCheckboxes.forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                toggleInvoice(cb);
            }
        });
        
        // Uncheck semua checkbox nota debit
        const notaDebitCheckboxes = document.querySelectorAll('.nota-debit-checkbox');
        notaDebitCheckboxes.forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                toggleNotaDebit(cb);
            }
        });
    }
}

function updateLunasiSemuaStatus() {
    const lunasiSemuaCheckbox = document.getElementById('lunasiSemua');
    const invoiceCheckboxes = document.querySelectorAll('.invoice-checkbox');
    const notaDebitCheckboxes = document.querySelectorAll('.nota-debit-checkbox');
    
    // Hitung total checkbox yang tersedia
    const totalCheckboxes = invoiceCheckboxes.length + notaDebitCheckboxes.length;
    
    // Hitung total checkbox yang tercentang
    const checkedInvoices = document.querySelectorAll('.invoice-checkbox:checked').length;
    const checkedNotaDebits = document.querySelectorAll('.nota-debit-checkbox:checked').length;
    const totalChecked = checkedInvoices + checkedNotaDebits;
    
    // Update status checkbox "Lunasi Semua"
    if (totalCheckboxes > 0) {
        lunasiSemuaCheckbox.checked = (totalChecked === totalCheckboxes);
        lunasiSemuaCheckbox.indeterminate = (totalChecked > 0 && totalChecked < totalCheckboxes);
    } else {
        lunasiSemuaCheckbox.checked = false;
        lunasiSemuaCheckbox.indeterminate = false;
    }
}

function clearTables() {
    document.getElementById('invoicesTableBody').innerHTML = 
        '<tr><td colspan="8" class="text-center text-muted">Pilih supplier terlebih dahulu untuk melihat faktur</td></tr>';
    document.getElementById('notaDebitTableBody').innerHTML = 
        '<tr><td colspan="8" class="text-center text-muted">Pilih supplier terlebih dahulu untuk melihat nota debit</td></tr>';
    
    selectedInvoices = [];
    selectedNotaDebits = [];
    updateSummary();
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>
@endsection
