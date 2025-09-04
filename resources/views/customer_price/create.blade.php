@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-plus-circle mr-2"></i>Tambah Harga Khusus Pelanggan</h2>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Form Harga Khusus Pelanggan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer-price.store') }}" method="POST" id="customerPriceForm">
                        @csrf
                        
                        <!-- Customer Selection -->
                        <div class="form-group mb-3">
                            <label for="customer_id" class="form-label">
                                <i class="fas fa-user mr-1"></i> Pilih Pelanggan <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Pilih Pelanggan</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->nama }} - {{ $customer->alamat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Barang Selection -->
                        <div class="form-group mb-3">
                            <label for="kode_barang_id" class="form-label">
                                <i class="fas fa-box mr-1"></i> Pilih Barang <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('kode_barang_id') is-invalid @enderror" 
                                    id="kode_barang_id" name="kode_barang_id" required>
                                <option value="">Pilih Barang</option>
                                @foreach($kodeBarangs as $barang)
                                    <option value="{{ $barang->id }}" 
                                            data-harga="{{ $barang->harga_jual }}"
                                            {{ old('kode_barang_id') == $barang->id ? 'selected' : '' }}>
                                        {{ $barang->kode_barang }} - {{ $barang->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Harga Normal (Read Only) -->
                        <div class="form-group mb-3">
                            <label for="harga_normal" class="form-label">
                                <i class="fas fa-tag mr-1"></i> Harga Normal
                            </label>
                            <input type="text" class="form-control" id="harga_normal" readonly>
                            <small class="form-text text-muted">Harga normal dari master barang</small>
                        </div>

                        <!-- Harga Khusus (customer_prices.harga_jual_khusus) -->
                        <div class="form-group mb-3">
                            <label for="harga_khusus" class="form-label">
                                <i class="fas fa-percentage mr-1"></i> Harga Khusus <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('harga_jual_khusus') is-invalid @enderror" 
                                       id="harga_khusus" name="harga_jual_khusus" 
                                       value="{{ old('harga_jual_khusus') }}" required>
                            </div>
                            @error('harga_jual_khusus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Harga khusus untuk pelanggan ini</small>
                        </div>

                        <!-- Diskon Preview -->
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-calculator mr-1"></i> Diskon
                            </label>
                            <div class="alert alert-info" id="diskonPreview">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pilih barang untuk melihat diskon
                            </div>
                        </div>

                        <!-- Status (customer_prices.is_active) -->
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on mr-1"></i> Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('is_active') is-invalid @enderror" 
                                    id="status" name="is_active" required>
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="form-group mb-3">
                            <label for="keterangan" class="form-label">
                                <i class="fas fa-comment mr-1"></i> Keterangan
                            </label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" name="keterangan" rows="3" 
                                      placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Simpan Harga Khusus
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('customer-price.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kodeBarangSelect = document.getElementById('kode_barang_id');
    const hargaNormalInput = document.getElementById('harga_normal');
    const hargaKhususInput = document.getElementById('harga_khusus');
    const diskonPreview = document.getElementById('diskonPreview');

    function updateHargaNormal() {
        const selectedOption = kodeBarangSelect.options[kodeBarangSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const harga = parseFloat(selectedOption.dataset.harga) || 0;
            hargaNormalInput.value = formatCurrency(harga);
            updateDiskonPreview();
        } else {
            hargaNormalInput.value = '';
            diskonPreview.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Pilih barang untuk melihat diskon';
        }
    }

    function updateDiskonPreview() {
        const selectedOption = kodeBarangSelect.options[kodeBarangSelect.selectedIndex];
        const hargaKhusus = parseFloat(hargaKhususInput.value) || 0;
        
        if (selectedOption && selectedOption.value && hargaKhusus > 0) {
            const hargaNormal = parseFloat(selectedOption.dataset.harga) || 0;
            if (hargaNormal > 0) {
                const diskon = ((hargaNormal - hargaKhusus) / hargaNormal) * 100;
                const selisih = hargaNormal - hargaKhusus;
                
                if (diskon > 0) {
                    diskonPreview.innerHTML = `
                        <i class="fas fa-percentage mr-1"></i>
                        <strong>Diskon: ${diskon.toFixed(1)}%</strong><br>
                        <small>Penghematan: Rp ${formatNumber(selisih)}</small>
                    `;
                    diskonPreview.className = 'alert alert-success';
                } else if (diskon < 0) {
                    diskonPreview.innerHTML = `
                        <i class="fas fa-arrow-up mr-1"></i>
                        <strong>Harga lebih tinggi: ${Math.abs(diskon).toFixed(1)}%</strong><br>
                        <small>Selisih: Rp ${formatNumber(Math.abs(selisih))}</small>
                    `;
                    diskonPreview.className = 'alert alert-warning';
                } else {
                    diskonPreview.innerHTML = `
                        <i class="fas fa-equals mr-1"></i>
                        <strong>Harga sama dengan harga normal</strong>
                    `;
                    diskonPreview.className = 'alert alert-info';
                }
            }
        } else {
            diskonPreview.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Pilih barang dan masukkan harga khusus';
            diskonPreview.className = 'alert alert-info';
        }
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    function formatNumber(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    // Event listeners
    kodeBarangSelect.addEventListener('change', updateHargaNormal);
    hargaKhususInput.addEventListener('input', updateDiskonPreview);

    // Initialize on page load
    updateHargaNormal();
    updateDiskonPreview();

    // Form validation
    document.getElementById('customerPriceForm').addEventListener('submit', function(e) {
        const hargaKhusus = parseFloat(hargaKhususInput.value) || 0;
        const selectedOption = kodeBarangSelect.options[kodeBarangSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const hargaNormal = parseFloat(selectedOption.dataset.harga) || 0;
            
            if (hargaKhusus <= 0) {
                e.preventDefault();
                alert('Harga khusus harus lebih dari 0');
                hargaKhususInput.focus();
                return false;
            }
            
            if (hargaKhusus > hargaNormal * 2) {
                if (!confirm('Harga khusus lebih dari 2x harga normal. Apakah Anda yakin?')) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
});
</script>
@endsection
