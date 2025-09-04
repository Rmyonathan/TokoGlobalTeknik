@extends('layout.Nav')

@section('content')
<section id="kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>List Kode Barang</h2>
        
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(isset($codes) && count($codes) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th style="border: 1px solid #000;">Kode Barang</th>
                                <th style="border: 1px solid #000;">Nama Barang</th>
                                <th style="border: 1px solid #000;">Satuan Kecil</th>
                                <th style="border: 1px solid #000;">Satuan Besar</th>
                                <th style="border: 1px solid #000;">Status</th>
                                <th style="border: 1px solid #000;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($codes as $code)
                                <tr>
                                    <td style="border: 1px solid #000;">{{ $code->kode_barang }}</td>
                                    <td style="border: 1px solid #000;">{{ $code->name }}</td>
                                    <td style="border: 1px solid #000;">{{ $code->unit_dasar ?? '-' }}</td>
                                    <td style="border: 1px solid #000; white-space: nowrap;">
                                        @php
                                            $convs = \App\Models\UnitConversion::where('kode_barang_id', $code->id)->orderBy('unit_turunan')->get();
                                        @endphp
                                        @if($convs->count() === 0)
                                            <span class="badge badge-light">-</span>
                                        @else
                                            @foreach($convs as $uc)
                                                <span class="badge {{ $uc->is_active ? 'badge-success' : 'badge-secondary' }} mr-2 mb-1">
                                                    {{ $uc->unit_turunan }} = {{ $uc->nilai_konversi }} {{ $code->unit_dasar }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td style="border: 1px solid #000;">{{ $code->status }}</td>
                                    <td style="border: 1px solid #000;">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('code.edit', $code->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('code.delete', $code->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kode ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $codes->links() }}
                </div>

            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada Kode Barang.
                </div>
            @endif
        </div>
    </div>
</section>

<style>
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
    }

    .table-bordered {
        border: 2px solid #000;
    }
    
    /* Fix button group alignment */
    .btn-group form {
        display: inline-block;
        margin-left: 5px;
    }
</style>
@endsection