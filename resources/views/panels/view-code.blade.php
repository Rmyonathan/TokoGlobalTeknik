@extends('layout.Nav')

@section('content')
<section id="kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>List Kode Barang</h2>
        <a href="{{ route('code.create-code') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Kode Barang
        </a>
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
                                <th style="border: 1px solid #000;">Attribute</th>
                                <th style="border: 1px solid #000;">Length (m)</th>
                                <th style="border: 1px solid #000;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($codes as $code)
                                <tr>
                                    <td style="border: 1px solid #000;">{{ $code->kode_barang }}</td>
                                    <td style="border: 1px solid #000;">{{ $code->attribute }}</td>
                                    <td style="border: 1px solid #000;">{{ number_format($code->length, 2) }}</td>
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