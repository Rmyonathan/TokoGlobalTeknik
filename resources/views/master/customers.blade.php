@extends('layout.Nav')

@section('content')
<section id="customers">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pelanggan</h2>
        <div>
            <button class="btn btn-info mr-2" data-toggle="modal" data-target="#creditSummaryModal">
                <i class="fas fa-chart-pie"></i> Ringkasan Kredit
            </button>
            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#addCustomerModal">Tambah Pelanggan</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form method="GET" action="{{ route('customers.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-2">
                <select name="search_by" id="search_by" class="form-control">
                    <option selected disabled value="">Cari Berdasarkan</option>
                    <option value="nama" {{ request('search_by') == 'nama' ? 'selected' : '' }}>Nama</option>
                    <option value="kode_customer" {{ request('search_by') == 'kode_customer' ? 'selected' : '' }}>Kode Customer</option>
                    <option value="alamat" {{ request('search_by') == 'alamat' ? 'selected' : '' }}>Alamat</option>
                    <option value="hp" {{ request('search_by') == 'hp' ? 'selected' : '' }}>Nomor HP</option>
                    <option value="telepon" {{ request('search_by') == 'telepon' ? 'selected' : '' }}>Telepon</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" id="search_input" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}" disabled>
            </div>
            <div class="col-md-2">
                <select name="credit_status" class="form-control">
                    <option value="">Semua Status Kredit</option>
                    <option value="tunai" {{ request('credit_status') == 'tunai' ? 'selected' : '' }}>Customer Tunai</option>
                    <option value="kredit" {{ request('credit_status') == 'kredit' ? 'selected' : '' }}>Customer Kredit</option>
                    <option value="aman" {{ request('credit_status') == 'aman' ? 'selected' : '' }}>Limit Aman</option>
                    <option value="sedang" {{ request('credit_status') == 'sedang' ? 'selected' : '' }}>Limit Sedang</option>
                    <option value="kritis" {{ request('credit_status') == 'kritis' ? 'selected' : '' }}>Limit Kritis</option>
                    <option value="habis" {{ request('credit_status') == 'habis' ? 'selected' : '' }}>Limit Habis</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered" style="border: 5px solid black; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Kode Customer</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>HP</th>
                <th>Telepon</th>
                <th>Info Kredit</th>
                <th>Wilayah</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="customerTableBody">
        <tbody id="customerTableBody">
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->kode_customer }}</td>
                    <td>{{ $customer->nama }}</td>
                    <td>{{ $customer->alamat }}</td>
                    <td>{{ $customer->hp }}</td>
                    <td>{{ $customer->telepon }}</td>
                    <td>
                        @php
                            $limitKredit = $customer->limit_kredit ?? 0;
                            $sisaPiutang = $customer->sisa_piutang ?? 0;
                            $sisaLimit = $limitKredit - $sisaPiutang;
                            
                            // Hitung persentase penggunaan kredit
                            $persentasePenggunaan = $limitKredit > 0 ? ($sisaPiutang / $limitKredit) * 100 : 0;
                            
                            // Tentukan status kredit
                            if ($limitKredit == 0) {
                                $statusKredit = 'Tunai';
                                $badgeClass = 'badge-success';
                            } elseif ($persentasePenggunaan >= 100) {
                                $statusKredit = 'Limit Habis';
                                $badgeClass = 'badge-danger';
                            } elseif ($persentasePenggunaan >= 80) {
                                $statusKredit = 'Limit Kritis';
                                $badgeClass = 'badge-warning';
                            } elseif ($persentasePenggunaan >= 50) {
                                $statusKredit = 'Limit Sedang';
                                $badgeClass = 'badge-info';
                            } else {
                                $statusKredit = 'Limit Aman';
                                $badgeClass = 'badge-success';
                            }
                        @endphp
                        
                        <div class="text-center">
                            <span class="badge {{ $badgeClass }} mb-1">
                                {{ $statusKredit }}
                            </span>
                            <br>
                            @if($limitKredit > 0)
                                <small class="text-muted">
                                    Limit: Rp {{ number_format($limitKredit, 0, ',', '.') }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Sisa: Rp {{ number_format($sisaLimit, 0, ',', '.') }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($persentasePenggunaan, 1) }}% terpakai
                                </small>
                            @else
                                <small class="text-muted">Customer Tunai</small>
                            @endif
                        </div>
                    </td>
                    <td>{{ $customer->wilayah->nama_wilayah }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCustomerModal-{{ $customer->id }}">Edit</button>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $customers->links() }}
    </div>
</section>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border: 3px solid black;">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Tambah Pelanggan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_customer">Kode Customer</label>
                        <input type="text" name="kode_customer" class="form-control" value="{{ $newKodeCustomer }}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input type="text" name="alamat" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="hp">HP</label>
                        <input type="text" name="hp" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon</label>
                        <input type="text" name="telepon" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="limit_kredit">Limit Kredit (Rp)</label>
                        <input type="number" name="limit_kredit" class="form-control" value="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="limit_hari_tempo">Limit Hari Tempo</label>
                        <input type="number" name="limit_hari_tempo" class="form-control" value="0" min="0">
                        <small class="form-text text-muted">0 = Customer Tunai, > 0 = Customer Kredit</small>
                    </div>
                    <div class="form-group">
                        <label for="wilayah_id">Wilayah</label>
                        <select name="wilayah_id" class="form-control">
                            <option value="">Pilih Wilayah</option>
                            @foreach($wilayahs ?? [] as $wilayah)
                                <option value="{{ $wilayah->id }}">{{ $wilayah->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modals -->
@foreach($customers as $customer)
<div class="modal fade" id="editCustomerModal-{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerModalLabel-{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border: 3px solid black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel-{{ $customer->id }}">Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_customer" class="form-label">Kode Customer</label>
                        <input type="text" name="kode_customer" class="form-control" value="{{ $customer->kode_customer }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ $customer->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="{{ $customer->alamat }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="hp" class="form-label">HP</label>
                        <input type="text" name="hp" class="form-control" value="{{ $customer->hp }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="{{ $customer->telepon }}"/>
                    </div>
                    <div class="mb-3">
                        <label for="limit_kredit" class="form-label">Limit Kredit (Rp)</label>
                        <input type="number" name="limit_kredit" class="form-control" value="{{ $customer->limit_kredit ?? 0 }}" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="limit_hari_tempo" class="form-label">Limit Hari Tempo</label>
                        <input type="number" name="limit_hari_tempo" class="form-control" value="{{ $customer->limit_hari_tempo ?? 0 }}" min="0">
                        <small class="form-text text-muted">0 = Customer Tunai, > 0 = Customer Kredit</small>
                    </div>
                    <div class="mb-3">
                        <label for="wilayah_id" class="form-label">Wilayah</label>
                        <select name="wilayah_id" class="form-control">
                            <option value="">Pilih Wilayah</option>
                            @foreach($wilayahs ?? [] as $wilayah)
                                <option value="{{ $wilayah->id }}" {{ $customer->wilayah_id == $wilayah->id ? 'selected' : '' }}>
                                    {{ $wilayah->id == $customer->wilayah_id ? $wilayah->nama_wilayah : $wilayah->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
    </div>
@endforeach

<!-- Credit Summary Modal -->
<div class="modal fade" id="creditSummaryModal" tabindex="-1" role="dialog" aria-labelledby="creditSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="creditSummaryModalLabel">
                    <i class="fas fa-chart-pie"></i> Ringkasan Kredit Customer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box bg-info text-white p-3 rounded text-center">
                            <h4>{{ $customers->count() }}</h4>
                            <p class="mb-0">Total Customer</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success text-white p-3 rounded text-center">
                            <h4>{{ $customers->where('limit_hari_tempo', 0)->count() }}</h4>
                            <p class="mb-0">Customer Tunai</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning text-white p-3 rounded text-center">
                            <h4>{{ $customers->where('limit_hari_tempo', '>', 0)->count() }}</h4>
                            <p class="mb-0">Customer Kredit</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-primary text-white p-3 rounded text-center">
                            <h4>Rp {{ number_format($customers->sum('limit_kredit'), 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Limit</p>
                        </div>
                    </div>
                </div>

                <!-- Credit Status Distribution -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="small-box bg-success text-white p-3 rounded text-center">
                            <h4>{{ $customers->filter(function($c) { 
                                $limit = $c->limit_kredit ?? 0; 
                                $piutang = $c->sisa_piutang ?? 0; 
                                return $limit > 0 && ($piutang / $limit) < 0.5; 
                            })->count() }}</h4>
                            <p class="mb-0">Limit Aman</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-info text-white p-3 rounded text-center">
                            <h4>{{ $customers->filter(function($c) { 
                                $limit = $c->limit_kredit ?? 0; 
                                $piutang = $c->sisa_piutang ?? 0; 
                                return $limit > 0 && ($piutang / $limit) >= 0.5 && ($piutang / $limit) < 0.8; 
                            })->count() }}</h4>
                            <p class="mb-0">Limit Sedang</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-warning text-white p-3 rounded text-center">
                            <h4>{{ $customers->filter(function($c) { 
                                $limit = $c->limit_kredit ?? 0; 
                                $piutang = $c->sisa_piutang ?? 0; 
                                return $limit > 0 && ($piutang / $limit) >= 0.8 && ($piutang / $limit) < 1; 
                            })->count() }}</h4>
                            <p class="mb-0">Limit Kritis</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-danger text-white p-3 rounded text-center">
                            <h4>{{ $customers->filter(function($c) { 
                                $limit = $c->limit_kredit ?? 0; 
                                $piutang = $c->sisa_piutang ?? 0; 
                                return $limit > 0 && ($piutang / $limit) >= 1; 
                            })->count() }}</h4>
                            <p class="mb-0">Limit Habis</p>
                        </div>
                    </div>
                </div>

                <!-- Customer List by Credit Status -->
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Status Kredit</th>
                                <th>Limit</th>
                                <th>Piutang</th>
                                <th>Sisa</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers->where('limit_hari_tempo', '>', 0) as $customer)
                            @php
                                $limit = $customer->limit_kredit ?? 0;
                                $piutang = $customer->sisa_piutang ?? 0;
                                $sisa = $limit - $piutang;
                                $persentase = $limit > 0 ? ($piutang / $limit) * 100 : 0;
                                
                                if ($persentase >= 100) {
                                    $statusClass = 'badge-danger';
                                    $statusText = 'Limit Habis';
                                } elseif ($persentase >= 80) {
                                    $statusClass = 'badge-warning';
                                    $statusText = 'Limit Kritis';
                                } elseif ($persentase >= 50) {
                                    $statusClass = 'badge-info';
                                    $statusText = 'Limit Sedang';
                                } else {
                                    $statusClass = 'badge-success';
                                    $statusText = 'Limit Aman';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $customer->nama }}</strong><br>
                                    <small class="text-muted">{{ $customer->kode_customer }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>Rp {{ number_format($limit, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($piutang, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $persentase >= 100 ? 'bg-danger' : ($persentase >= 80 ? 'bg-warning' : ($persentase >= 50 ? 'bg-info' : 'bg-success')) }}" 
                                             style="width: {{ min($persentase, 100) }}%">
                                            {{ number_format($persentase, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen select dan input
    const searchBySelect = document.getElementById('search_by');
    const searchInput = document.getElementById('search_input');

    // Fungsi untuk mengecek status dropdown dan mengatur disabled state pada input
    function updateSearchInputState() {
        if (searchBySelect.value !== "" && searchBySelect.selectedIndex !== 0) {
            searchInput.disabled = false;
        } else {
            searchInput.disabled = true;
            searchInput.value = '';
        }
    }

    updateSearchInputState();
    searchBySelect.addEventListener('change', updateSearchInputState);

    // Handle credit status filter
    const creditStatusSelect = document.querySelector('select[name="credit_status"]');
    if (creditStatusSelect) {
        creditStatusSelect.addEventListener('change', function() {
            // Auto-submit form when credit status changes
            this.closest('form').submit();
        });
    }
});
</script>
@endsection