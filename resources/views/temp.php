
<!-- @extends('layout.Nav')

@section('content')
<div class="container bg-light p-4 rounded">
    <h4>:: Surat Jalan ::</h4>
    <form id="suratJalanForm">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>No Surat Jalan</label>
                <input type="text" class="form-control bg-warning text-white font-weight-bold" name="no_suratjalan" value="{{ $noSuratJalan }}" readonly>
            </div>
            <div class="col-md-3">
                <label>Tanggal</label>
                <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Customer</label>
                <input type="text" class="form-control" id="customerInput" name="kode_customer" autocomplete="off">
                <div id="customerList" class="list-group position-absolute w-100"></div>
            </div>
            <div class="col-md-6">
                <label>Tanggal Transaksi</label>
                <input type="date" class="form-control" id="tanggalTransaksi" name="tanggal_transaksi" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Alamat di Surat Jalan</label>
                <input type="text" class="form-control" name="alamat_suratjalan">
            </div>
            <div class="col-md-6">
                <label>No Faktur</label>
                <input type="text" class="form-control" id="noFakturInput" name="no_transaksi" autocomplete="off">
                <div id="noFakturList" class="list-group position-absolute w-100"></div>
            </div>
        </div>
        <div class="row mb-3">
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <h5>Detail Item</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Stock Owner</th>
                            <th>Kode Barang</th>
                            <th>Keterangan</th>
                            <th>Panjang</th>
                            <th>Lebar</th>
                            <th>Qty</th>
                            <th>Pernah Ambil</th>
                            <th>Qty Dibawa</th>
                        </tr>
                    </thead>
                    <tbody id="detailItemTable">
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" id="refreshForm">Refresh</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Fetch customer berdasarkan input
        $('#customerInput').on('input', function() {
            const query = $(this).val();
            if (query.length > 0) {
                $.ajax({
                    url: "{{ route('api.customers') }}",
                    method: "GET",
                    data: { query: query },
                    success: function(response) {
                        let html = '';
                        response.forEach(function(customer) {
                            html += `<a href="#" class="list-group-item list-group-item-action customerItem" 
                                        data-kode="${customer.kode_customer}" 
                                        data-nama="${customer.nama}" 
                                        data-alamat="${customer.alamat}">
                                        ${customer.nama} (${customer.kode_customer})
                                    </a>`;
                        });
                        $('#customerList').html(html).show();
                    }
                });
            } else {
                $('#customerList').hide();
            }
        });

        // Pilih customer dari list
        $(document).on('click', '.customerItem', function(e) {
            e.preventDefault();
            const kodeCustomer = $(this).data('kode');
            const namaCustomer = $(this).data('nama');
            const alamatCustomer = $(this).data('alamat');

            $('#customerInput').val(namaCustomer); // Tampilkan nama customer di input
            $('#alamatCustomer').val(alamatCustomer); // Tampilkan alamat customer
            $('#customerList').hide();

            // Reset No Faktur dan Tabel Detail Item
            $('#noFakturInput').val('');
            $('#tanggalTransaksi').val('');
            $('#detailItemTable').html('');

            // Fetch nomor faktur berdasarkan customer
            $.ajax({
                url: "{{ url('api/transaksi') }}",
                method: "GET",
                data: { kode_customer: kodeCustomer },
                success: function(response) {
                    let html = '';
                    response.forEach(function(item) {
                        html += `<a href="#" class="list-group-item list-group-item-action noFakturItem" 
                                    data-id="${item.id}" 
                                    data-no_transaksi="${item.no_transaksi}" 
                                    data-tanggal="${item.tanggal}">
                                    ${item.no_transaksi}
                                </a>`;
                    });
                    $('#noFakturList').html(html).show();
                }
            });
        });

        // Fetch nomor faktur berdasarkan input
        $('#noFakturInput').on('input', function() {
            const query = $(this).val();
            if (query.length > 0) {
                $.ajax({
                    url: "{{ url('api/transaksi') }}",
                    method: "GET",
                    data: { query: query },
                    success: function(response) {
                        let html = '';
                        response.forEach(function(item) {
                            html += `<a href="#" class="list-group-item list-group-item-action noFakturItem" 
                                        data-id="${item.id}" 
                                        data-no_transaksi="${item.no_transaksi}" 
                                        data-tanggal="${item.tanggal}" 
                                        data-kode_customer="${item.kode_customer}" 
                                        data-nama_customer="${item.nama_customer}" 
                                        data-alamat_customer="${item.alamat_customer}">
                                        ${item.no_transaksi}
                                    </a>`;
                        });
                        $('#noFakturList').html(html).show();
                    }
                });
            } else {
                $('#noFakturList').hide();
            }
        });

        // Pilih nomor faktur dari list
        $(document).on('click', '.noFakturItem', function(e) {
            e.preventDefault();
            const transaksiId = $(this).data('transaksi_id'); // Ambil ID transaksi
            const noTransaksi = $(this).data('no_transaksi'); // Ambil No Transaksi
            const tanggal = $(this).data('tanggal'); // Ambil Tanggal Transaksi

            // Isi field No Faktur dan Tanggal Transaksi
            $('#noFakturInput').val(noTransaksi);
            $('#tanggalTransaksi').val(tanggal);

            $('#noFakturList').hide();

            // Fetch detail item berdasarkan ID transaksi
            $.ajax({
                url: "{{ url('api/transaksi/items') }}/" + transaksiId, // Gunakan ID transaksi
                method: "GET",
                success: function(response) {
                    let html = '';
                    response.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.stock_owner}</td>
                                <td>${item.kode_barang}</td>
                                <td>${item.nama_barang}</td>
                                <td>${item.keterangan || '-'}</td>
                                <td>${item.qty}</td>
                            </tr>
                        `;
                    });
                    $('#detailItemTable').html(html);
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat mengambil detail item: ' + xhr.responseJSON.message);
                }
            });
        });

        // Submit form untuk menyimpan data surat jalan
        $('#suratJalanForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                no_suratjalan: $('input[name="no_suratjalan"]').val(),
                tanggal: $('input[name="tanggal"]').val(),
                kode_customer: $('#customerInput').data('kode'),
                alamat_suratjalan: $('input[name="alamat_suratjalan"]').val(),
                no_transaksi: $('#noFakturInput').val(),
                items: []
            };

            // Ambil data dari tabel detail item
            $('#detailItemTable tr').each(function() {
                const transaksiItemId = $(this).find('.qtyDibawa').data('id');
                const qtyDibawa = $(this).find('.qtyDibawa').val();

                if (qtyDibawa > 0) {
                    formData.items.push({
                        transaksi_item_id: transaksiItemId,
                        qty_dibawa: qtyDibawa
                    });
                }
            });

            // Kirim data ke backend
            $.ajax({
                url: "{{ route('suratjalan.store') }}",
                method: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Tampilkan notifikasi berhasil
                        $('#suratJalanForm').after(`
                            <div class="alert alert-success mt-3">
                                Surat Jalan berhasil disimpan!
                                <div class="mt-2">
                                    <a href="{{ url('suratjalan/detail') }}/${response.id}" class="btn btn-primary">Print</a>
                                    <button class="btn btn-secondary" id="resetForm">Oke</button>
                                </div>
                            </div>
                        `);

                        // Reset form jika user klik "Oke"
                        $('#resetForm').on('click', function() {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
                }
            });
        });

    });
</script>
@endsection -->

<!-- @extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="mb-4">Data Penjualan Per Customer</h2>

    
    <div class="card mb-4 p-3">
        <form method="GET" action="{{ route('transaksi.getPenjualancustomer') }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="kode_customer" class="form-label">Pilih Customer</label>
                    <select id="kode_customer" name="kode_customer" class="form-select">
                        <option value="">-- Pilih Customer --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->kode_customer }}" {{ request('kode_customer') == $customer->kode_customer ? 'selected' : '' }}>
                                {{ $customer->nama }} ({{ $customer->total_transaksi }} transaksi)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    
    @if (!empty($transaksi))
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No Transaksi</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Subtotal</th>
                        <th>Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi as $item)
                        <tr>
                            <td>{{ $item->no_transaksi }}</td>
                            <td>{{ $item->tanggal }}</td>
                            <td>{{ $item->lokasi }}</td>
                            <td>{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td>{{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center">Pilih customer untuk melihat transaksi.</p>
    @endif
</div>
@endsection -->