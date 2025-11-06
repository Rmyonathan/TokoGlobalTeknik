@extends('layout.Nav')

@section('title', 'Penawaran Harga / Catalog')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-tag mr-2"></i>Penawaran Harga / Catalog
                    </h4>
                    <p class="mb-0 small">Pilih barang untuk di-export sebagai penawaran harga</p>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <!-- Export Options Panel -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-cog mr-2"></i>Pengaturan Export</h5>
                        </div>
                        <div class="card-body">
                            <form id="exportForm" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Judul Catalog</label>
                                            <input type="text" class="form-control" name="catalog_title" value="DAFTAR HARGA BARANG">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Berlaku Sampai</label>
                                            <input type="date" class="form-control" name="valid_until" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Tampilkan Stok</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="showStock" name="show_stock" value="1">
                                                <label class="custom-control-label" for="showStock">Ya</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Catatan / Keterangan (Opsional)</label>
                                            <textarea class="form-control" name="notes" rows="2" placeholder="Contoh: Harga sewaktu-waktu dapat berubah tanpa pemberitahuan"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden field for selected items -->
                                <input type="hidden" name="selected_items" id="selectedItemsInput">

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="btn-group btn-group-lg" role="group">
                                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                                <i class="fas fa-file-pdf mr-2"></i>Export PDF
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                                <i class="fas fa-file-excel mr-2"></i>Export Excel
                                            </button>
                                        </div>
                                        <span class="ml-3 text-muted" id="selectedCount">0 item dipilih</span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('penawaran.index') }}" id="searchForm">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="search" id="searchInput" 
                                           placeholder="Cari kode/nama/merek/ukuran..." 
                                           value="{{ request('search') }}">
                                    @if(request('search'))
                                        <div class="input-group-append">
                                            <a href="{{ route('penawaran.index') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Menampilkan {{ $barangs->firstItem() ?? 0 }} - {{ $barangs->lastItem() ?? 0 }} dari {{ $barangs->total() }} barang
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-primary btn-sm" onclick="selectAllPage()">
                                <i class="fas fa-check-square mr-1"></i>Pilih Halaman Ini
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAll()">
                                <i class="fas fa-square mr-1"></i>Hapus Semua
                            </button>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="barangTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="50px" class="text-center">
                                        <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll()">
                                    </th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Merek</th>
                                    <th>Ukuran/Type</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Harga Jual</th>
                                    <th class="text-center">Stok Tersedia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangs as $barang)
                                <tr class="barang-row">
                                    <td class="text-center">
                                        <input type="checkbox" class="item-checkbox" value="{{ $barang->id }}" 
                                               data-kode="{{ $barang->kode_barang }}"
                                               data-nama="{{ $barang->name }}">
                                    </td>
                                    <td>{{ $barang->kode_barang }}</td>
                                    <td>{{ $barang->name }}</td>
                                    <td>{{ $barang->merek ?? '-' }}</td>
                                    <td>{{ $barang->ukuran ?? '-' }}</td>
                                    <td>{{ $barang->unit_dasar ?? 'PCS' }}</td>
                                    <td class="text-right">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $barang->total_stock > 0 ? 'badge-success' : 'badge-secondary' }}">
                                            {{ number_format($barang->total_stock, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data barang</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Halaman {{ $barangs->currentPage() }} dari {{ $barangs->lastPage() }}
                        </div>
                        <div>
                            {{ $barangs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .barang-row:hover {
        background-color: #f8f9fa;
    }
    
    .item-checkbox {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    
    #selectAllCheckbox {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    
    .btn-group-lg .btn {
        padding: 12px 24px;
        font-size: 16px;
    }
    
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .page-link {
        color: #007bff;
    }
    
    .page-link:hover {
        color: #0056b3;
    }
</style>

<script>
    // Store selected items in localStorage to persist across pages
    let selectedItems = JSON.parse(localStorage.getItem('selectedPenawaranItems') || '[]');

    // Restore selections on page load
    $(document).ready(function() {
        selectedItems.forEach(function(itemId) {
            $('.item-checkbox[value="' + itemId + '"]').prop('checked', true);
        });
        updateSelectedCount();
    });

    // Search with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const form = $('#searchForm');
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 500);
    });

    // Select all on current page only
    function selectAllPage() {
        $('.item-checkbox').prop('checked', true);
        $('.item-checkbox').each(function() {
            const itemId = $(this).val();
            if (!selectedItems.includes(itemId)) {
                selectedItems.push(itemId);
            }
        });
        localStorage.setItem('selectedPenawaranItems', JSON.stringify(selectedItems));
        updateSelectedCount();
    }

    // Deselect all (across all pages)
    function deselectAll() {
        $('.item-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        selectedItems = [];
        localStorage.removeItem('selectedPenawaranItems');
        updateSelectedCount();
    }

    // Toggle select all checkbox
    function toggleSelectAll() {
        const isChecked = $('#selectAllCheckbox').is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        if (isChecked) {
            $('.item-checkbox').each(function() {
                const itemId = $(this).val();
                if (!selectedItems.includes(itemId)) {
                    selectedItems.push(itemId);
                }
            });
        } else {
            $('.item-checkbox').each(function() {
                const itemId = $(this).val();
                const index = selectedItems.indexOf(itemId);
                if (index > -1) {
                    selectedItems.splice(index, 1);
                }
            });
        }
        localStorage.setItem('selectedPenawaranItems', JSON.stringify(selectedItems));
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const count = selectedItems.length;
        $('#selectedCount').text(count + ' item dipilih');
        
        // Update master checkbox state
        const totalOnPage = $('.item-checkbox').length;
        const checkedOnPage = $('.item-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', totalOnPage > 0 && checkedOnPage === totalOnPage);
    }

    // Handle individual checkbox change
    $(document).on('change', '.item-checkbox', function() {
        const itemId = $(this).val();
        if ($(this).is(':checked')) {
            if (!selectedItems.includes(itemId)) {
                selectedItems.push(itemId);
            }
        } else {
            const index = selectedItems.indexOf(itemId);
            if (index > -1) {
                selectedItems.splice(index, 1);
            }
        }
        localStorage.setItem('selectedPenawaranItems', JSON.stringify(selectedItems));
        updateSelectedCount();
    });

    // Export to PDF
    function exportToPDF() {
        if (selectedItems.length === 0) {
            alert('Pilih minimal 1 barang untuk di-export!');
            return;
        }

        $('#selectedItemsInput').val(JSON.stringify(selectedItems));
        $('#exportForm').attr('action', '{{ route('penawaran.export.pdf') }}');
        $('#exportForm').submit();
        
        // Optional: Clear selections after export
        // setTimeout(function() { deselectAll(); }, 1000);
    }

    // Export to Excel
    function exportToExcel() {
        if (selectedItems.length === 0) {
            alert('Pilih minimal 1 barang untuk di-export!');
            return;
        }

        $('#selectedItemsInput').val(JSON.stringify(selectedItems));
        $('#exportForm').attr('action', '{{ route('penawaran.export.excel') }}');
        $('#exportForm').submit();
        
        // Optional: Clear selections after export
        // setTimeout(function() { deselectAll(); }, 1000);
    }
</script>
@endsection

