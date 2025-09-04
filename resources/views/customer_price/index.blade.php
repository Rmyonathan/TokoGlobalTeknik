@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-tags mr-2"></i>Harga Khusus Pelanggan</h2>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Harga Khusus Pelanggan</h5>
                    <a href="{{ route('customer-price.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Tambah Harga Khusus
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="Cari pelanggan atau barang...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="customerFilter" class="form-select">
                                <option value="">Semua Pelanggan</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="customerPriceTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Pelanggan</th>
                                    <th>Barang</th>
                                    <th>Kode Barang</th>
                                    <th>Harga Normal</th>
                                    <th>Harga Khusus</th>
                                    <th>Diskon (%)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customerPrices as $index => $price)
                                    <tr>
                                        <td>{{ $customerPrices->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $price->customer->nama }}</strong><br>
                                            <small class="text-muted">{{ $price->customer->alamat }}</small>
                                        </td>
                                        <td>{{ $price->kodeBarang->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $price->kodeBarang->kode_barang }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success">Rp {{ number_format($price->kodeBarang->harga_jual, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-primary fw-bold">Rp {{ number_format($price->harga_jual_khusus, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $diskon = (($price->kodeBarang->harga_jual - $price->harga_jual_khusus) / max(1, $price->kodeBarang->harga_jual)) * 100;
                                            @endphp
                                            <span class="badge bg-warning">{{ number_format($diskon, 1) }}%</span>
                                        </td>
                                        <td>
                                            @if($price->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('customer-price.edit', $price->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteCustomerPrice({{ $price->id }})" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>Tidak ada data harga khusus pelanggan</p>
                                                <a href="{{ route('customer-price.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus mr-1"></i> Tambah Harga Khusus Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($customerPrices->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $customerPrices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus harga khusus pelanggan ini?</p>
                <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const customerFilter = document.getElementById('customerFilter');
    const statusFilter = document.getElementById('statusFilter');
    const clearFilters = document.getElementById('clearFilters');
    const table = document.getElementById('customerPriceTable');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const customerValue = customerFilter.value;
        const statusValue = statusFilter.value;
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            
            if (cells.length === 0) continue; // Skip empty rows

            const customerName = cells[1].textContent.toLowerCase();
            const barangName = cells[2].textContent.toLowerCase();
            const kodeBarang = cells[3].textContent.toLowerCase();
            const status = cells[7].textContent.toLowerCase();

            const matchesSearch = searchTerm === '' || 
                customerName.includes(searchTerm) || 
                barangName.includes(searchTerm) || 
                kodeBarang.includes(searchTerm);

            const matchesCustomer = customerValue === '' || 
                row.getAttribute('data-customer-id') === customerValue;

            const matchesStatus = statusValue === '' || 
                (statusValue === 'active' && status.includes('aktif')) ||
                (statusValue === 'inactive' && status.includes('tidak aktif'));

            if (matchesSearch && matchesCustomer && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    searchInput.addEventListener('input', filterTable);
    customerFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);

    clearFilters.addEventListener('click', function() {
        searchInput.value = '';
        customerFilter.value = '';
        statusFilter.value = '';
        filterTable();
    });
});

function deleteCustomerPrice(id) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/customer-price/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection
