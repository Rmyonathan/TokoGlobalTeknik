@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-database"></i> Database Switch
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Current Database Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Database Saat Ini</h5>
                                    <p class="card-text">
                                        <strong>{{ $currentDatabase['name'] ?? 'Tidak diketahui' }}</strong><br>
                                        <small class="text-muted">{{ $currentDatabase['description'] ?? '' }}</small>
                                    </p>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Ukuran Database:</small><br>
                                            <strong>{{ number_format($databaseSize['size_mb'], 2) }} MB</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Jumlah Tabel:</small><br>
                                            <strong>{{ $databaseSize['table_count'] }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Status Koneksi</h5>
                                    <div id="connection-status">
                                        <span class="badge bg-success">Terhubung</span>
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="testConnection()">
                                            <i class="fas fa-wifi"></i> Test Koneksi
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshStatus()">
                                            <i class="fas fa-sync"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Selection -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Pilih Database</h5>
                            <div class="row">
                                @foreach($availableDatabases as $key => $db)
                                <div class="col-md-6 mb-3">
                                    <div class="card {{ $db['is_current'] ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="card-title">
                                                        {{ $db['name'] }}
                                                        @if($db['is_current'])
                                                            <span class="badge bg-primary">Aktif</span>
                                                        @endif
                                                    </h6>
                                                    <p class="card-text text-muted">{{ $db['description'] }}</p>
                                                </div>
                                                <div class="text-end">
                                                    @if($db['is_connected'])
                                                        <span class="badge bg-success">Terhubung</span>
                                                    @else
                                                        <span class="badge bg-danger">Tidak Terhubung</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                @if(!$db['is_current'])
                                                    <button class="btn btn-primary btn-sm" 
                                                            onclick="switchDatabase('{{ $key }}')"
                                                            {{ !$db['is_connected'] ? 'disabled' : '' }}>
                                                        <i class="fas fa-exchange-alt"></i> Switch ke Database Ini
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-check"></i> Database Aktif
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline-info btn-sm ms-2" 
                                                        onclick="testDatabaseConnection('{{ $key }}')">
                                                    <i class="fas fa-wifi"></i> Test
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button class="btn btn-warning" onclick="resetToDefault()">
                                    <i class="fas fa-undo"></i> Reset ke Default
                                </button>
                                <button class="btn btn-info" onclick="refreshStatus()">
                                    <i class="fas fa-sync"></i> Refresh Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchDatabase(databaseKey) {
    if (confirm('Apakah Anda yakin ingin mengubah database?')) {
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Switching...';
        button.disabled = true;
        
        fetch('{{ route("database.switch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ database: databaseKey })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Database berhasil diubah!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Gagal mengubah database');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

function testDatabaseConnection(databaseKey) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;
    
    fetch('{{ route("database.test-connection") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ database: databaseKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Koneksi berhasil!');
        } else {
            alert('Koneksi gagal: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Gagal test koneksi');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function testConnection() {
    fetch('{{ route("database.status") }}')
    .then(response => response.json())
    .then(data => {
        const statusElement = document.getElementById('connection-status');
        if (data.current_database) {
            statusElement.innerHTML = '<span class="badge bg-success">Terhubung</span>';
        } else {
            statusElement.innerHTML = '<span class="badge bg-danger">Tidak Terhubung</span>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('connection-status').innerHTML = '<span class="badge bg-danger">Error</span>';
    });
}

function refreshStatus() {
    location.reload();
}

function resetToDefault() {
    if (confirm('Apakah Anda yakin ingin mereset ke database default?')) {
        fetch('{{ route("database.reset") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Database direset ke default!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Gagal mereset database');
        });
    }
}
</script>
@endsection
