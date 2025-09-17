@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Buat Faktur dari Multiple Surat Jalan</h2>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Pilih Customer dan Surat Jalan</h5>
        </div>
        <div class="card-body">
            <form id="multipleSJForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_customer">Customer</label>
                            <select class="form-control" id="kode_customer" name="kode_customer" required>
                                <option value="">Pilih Customer</option>
                                @foreach($suratJalans as $customerCode => $sjs)
                                    <option value="{{ $customerCode }}">
                                        {{ $sjs->first()->customer->nama ?? $customerCode }} 
                                        ({{ $sjs->count() }} SJ)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Faktur</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pembayaran">Metode Pembayaran</label>
                            <select class="form-control" id="pembayaran" name="pembayaran" required>
                                <option value="">Pilih Metode</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Non Tunai">Non Tunai</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar" required>
                                <option value="">Pilih Cara Bayar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hari_tempo">Hari Tempo</label>
                            <input type="number" class="form-control" id="hari_tempo" name="hari_tempo" 
                                   min="0" value="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sales">Sales</label>
                            <select class="form-control" id="sales" name="sales" required>
                                <option value="">Pilih Sales</option>
                                @isset($salesList)
                                    @foreach($salesList as $sales)
                                        <option value="{{ $sales->kode_stok_owner }}">{{ $sales->keterangan }} ({{ $sales->kode_stok_owner }})</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="merge_similar_items" 
                               name="merge_similar_items" value="1">
                        <label class="form-check-label" for="merge_similar_items">
                            Gabungkan item sejenis (kode barang sama)
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="no_po">No PO</label>
                            <input type="text" class="form-control" id="no_po" name="no_po" placeholder="Masukkan No PO" required>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Surat Jalan Selection -->
    <div class="card mb-4" id="suratJalanCard" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pilih Surat Jalan</h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                    <i class="fas fa-check-square"></i> Pilih Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                    <i class="fas fa-square"></i> Batal Pilih
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="suratJalanTable">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
                            <th>No. Surat Jalan</th>
                            <th>Tanggal</th>
                            <th>Jumlah Item</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="suratJalanTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Items -->
    <div class="card mb-4" id="previewCard" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0">Preview Item Faktur</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="previewTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Satuan Besar</th>
                            <th>Dari SJ</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody">
                        <!-- Preview data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body text-center">
            <button type="button" class="btn btn-primary btn-lg" id="previewBtn" disabled>
                <i class="fas fa-eye mr-2"></i>Preview Faktur
            </button>
            <button type="button" class="btn btn-success btn-lg" id="createBtn" disabled>
                <i class="fas fa-save mr-2"></i>Buat Faktur
            </button>
            <button type="button" class="btn btn-secondary btn-lg" id="cancelBtn">
                <i class="fas fa-times mr-2"></i>Batal
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Memproses data...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedSJIds = [];
    let previewData = null;

    // Load cara bayar options when pembayaran changes
    $('#pembayaran').on('change', function() {
        const metode = $(this).val();
        $('#cara_bayar').html('<option value="">Loading...</option>');
        
        if (metode) {
            $.ajax({
                url: '{{ url("api/cara-bayar/by-metode") }}',
                method: 'GET',
                data: { metode: metode },
                success: function(data) {
                    let options = '<option value="">Pilih Cara Bayar</option>';
                    data.forEach(cb => {
                        options += `<option value="${cb.nama}">${cb.nama}</option>`;
                    });
                    $('#cara_bayar').html(options);
                },
                error: function() {
                    $('#cara_bayar').html('<option value="">Gagal load data</option>');
                }
            });
        }
    });

    // Load surat jalan when customer changes
    $('#kode_customer').on('change', function() {
        const kodeCustomer = $(this).val();
        if (kodeCustomer) {
            loadSuratJalans(kodeCustomer);
        } else {
            $('#suratJalanCard').hide();
            $('#previewCard').hide();
            $('#previewBtn').prop('disabled', true);
            $('#createBtn').prop('disabled', true);
        }
    });

    function loadSuratJalans(kodeCustomer) {
        $.ajax({
            url: '{{ route("multiple-sj.get-by-customer") }}',
            method: 'GET',
            data: { kode_customer: kodeCustomer },
            success: function(data) {
                let html = '';
                data.forEach(sj => {
                    const totalQty = sj.items.reduce((sum, item) => sum + parseFloat(item.qty), 0);
                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" class="sj-checkbox" value="${sj.id}" 
                                       data-sj-no="${sj.no_suratjalan}">
                            </td>
                            <td>${sj.no_suratjalan}</td>
                            <td>${sj.tanggal}</td>
                            <td>${sj.items.length}</td>
                            <td>${totalQty}</td>
                            <td><span class="badge badge-warning">Belum Terfaktur</span></td>
                        </tr>
                    `;
                });
                $('#suratJalanTableBody').html(html);
                $('#suratJalanCard').show();
                updateButtonStates();
            },
            error: function() {
                alert('Gagal memuat data surat jalan');
            }
        });
    }

    // Handle select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.sj-checkbox').prop('checked', this.checked);
        updateSelectedSJ();
    });

    // Handle individual checkboxes
    $(document).on('change', '.sj-checkbox', function() {
        updateSelectedSJ();
        updateSelectAllState();
    });

    // Select all button
    $('#selectAllBtn').on('click', function() {
        $('.sj-checkbox').prop('checked', true);
        updateSelectedSJ();
        updateSelectAllState();
    });

    // Deselect all button
    $('#deselectAllBtn').on('click', function() {
        $('.sj-checkbox').prop('checked', false);
        updateSelectedSJ();
        updateSelectAllState();
    });

    function updateSelectedSJ() {
        selectedSJIds = $('.sj-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        updateButtonStates();
    }

    function updateSelectAllState() {
        const totalCheckboxes = $('.sj-checkbox').length;
        const checkedCheckboxes = $('.sj-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes);
    }

    function updateButtonStates() {
        const hasSelection = selectedSJIds.length > 0;
        const hasFormData = $('#kode_customer').val() && $('#pembayaran').val() && $('#cara_bayar').val() && $('#sales').val();
        
        $('#previewBtn').prop('disabled', !hasSelection || !hasFormData);
        $('#createBtn').prop('disabled', !hasSelection || !hasFormData || !previewData);
    }

    // Preview button
    $('#previewBtn').on('click', function() {
        if (selectedSJIds.length === 0) {
            alert('Pilih minimal satu surat jalan');
            return;
        }

        const formData = {
            kode_customer: $('#kode_customer').val(),
            surat_jalan_ids: selectedSJIds,
            merge_similar_items: $('#merge_similar_items').is(':checked') ? 1 : 0
        };

        $('#loadingModal').modal('show');

        $.ajax({
            url: '{{ route("multiple-sj.preview") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    previewData = response;
                    displayPreview(response);
                    $('#previewCard').show();
                    $('#createBtn').prop('disabled', false);
                } else {
                    alert('Gagal preview: ' + response.message);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                alert('Gagal preview: ' + error);
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    });

    function displayPreview(data) {
        let html = '';
        data.items.forEach(item => {
            const sjInfo = Array.isArray(item.surat_jalan_no) 
                ? item.surat_jalan_no.join(', ') 
                : item.surat_jalan_no;
            
            html += `
                <tr>
                    <td>${item.kode_barang}</td>
                    <td>${item.nama_barang}</td>
                    <td>${item.qty}</td>
                    <td>${item.satuan}</td>
                    <td>${item.satuan_besar || '-'}</td>
                    <td>${sjInfo}</td>
                </tr>
            `;
        });
        $('#previewTableBody').html(html);
    }

    // Create button
    $('#createBtn').on('click', function() {
        if (!previewData) {
            alert('Silakan preview terlebih dahulu');
            return;
        }

        if (!confirm(`Buat faktur dari ${selectedSJIds.length} surat jalan?`)) {
            return;
        }

        const formData = {
            kode_customer: $('#kode_customer').val(),
            surat_jalan_ids: selectedSJIds,
            tanggal: $('#tanggal').val(),
            pembayaran: $('#pembayaran').val(),
            cara_bayar: $('#cara_bayar').val(),
            sales: $('#sales').val(),
            no_po: $('#no_po').val(),
            hari_tempo: $('#hari_tempo').val() || 0,
            tanggal_jatuh_tempo: $('#hari_tempo').val() > 0 ? 
                new Date(new Date($('#tanggal').val()).getTime() + ($('#hari_tempo').val() * 24 * 60 * 60 * 1000)).toISOString().split('T')[0] : null,
            merge_similar_items: $('#merge_similar_items').is(':checked') ? 1 : 0
        };

        $('#loadingModal').modal('show');

        $.ajax({
            url: '{{ route("multiple-sj.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = response.redirect_url;
                } else {
                    alert('Gagal membuat faktur: ' + response.message);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                alert('Gagal membuat faktur: ' + error);
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    });

    // Cancel button
    $('#cancelBtn').on('click', function() {
        if (confirm('Batalkan pembuatan faktur?')) {
            window.location.href = '{{ route("transaksi.index") }}';
        }
    });

    // Update button states when form changes
    $('#kode_customer, #pembayaran, #cara_bayar, #sales').on('change', updateButtonStates);
});
</script>
@endsection
