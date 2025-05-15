@extends('layout.Nav')

@section('content')
<div class="container py-3">
    <div class="title-box mb-4">
        <h2><i class="fas fa-wallet mr-2"></i> Master Cara Bayar</h2>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Form Tambah -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            Tambah Cara Bayar
        </div>
        <div class="card-body">
            <form action="{{ route('master.cara_bayar.store') }}" method="POST">
                @csrf
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label for="metode">Metode Pembayaran</label>
                        <select class="form-control" name="metode" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Non Tunai">Non Tunai</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nama">Cara Bayar</label>
                        <input type="text" name="nama" class="form-control" placeholder="Misal: Cash, Transfer BCA xxx" required>
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="searchCaraBayar" class="form-control" placeholder="Cari berdasarkan metode atau nama...">
        </div>
    </div>

    <!-- Table List -->
    <div class="card">
        <div class="card-header">
            List Cara Bayar
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Metode</th>
                        <th>Nama Cara Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="caraBayarTable">
                    @foreach($cara_bayar as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->metode }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>
                            <form action="{{ route('master.cara_bayar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin mau hapus cara bayar ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($cara_bayar) === 0)
                        <tr><td colspan="4" class="text-center">Data belum ada</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#searchCaraBayar').on('keyup', function() {
            const keyword = $(this).val().toLowerCase();
            $('#caraBayarTable tr').filter(function() {
                $(this).toggle(
                    $(this).text().toLowerCase().indexOf(keyword) > -1
                );
            });
        });
    });
</script>
@endsection
