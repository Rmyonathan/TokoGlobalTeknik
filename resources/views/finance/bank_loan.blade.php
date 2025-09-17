@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box"><h2><i class="fas fa-university mr-2"></i>Utang Bank</h2></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Pencairan Pinjaman</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('finance.bank-loan.disburse') }}">
                        @csrf
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Bank</label>
                            <select class="form-control" name="bank" required>
                                @foreach($banks as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="jumlah" required>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" placeholder="Opsional">
                        </div>
                        <button type="submit" class="btn btn-primary">Catat Pencairan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Angsuran Pinjaman</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('finance.bank-loan.installment') }}">
                        @csrf
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Bank</label>
                            <select class="form-control" name="bank" required>
                                @foreach($banks as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Pokok</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="pokok" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Bunga</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="bunga" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" placeholder="Opsional">
                        </div>
                        <button type="submit" class="btn btn-success">Catat Angsuran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


