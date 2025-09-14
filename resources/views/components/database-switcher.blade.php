@props(['currentDatabase', 'availableDatabases'])

<div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="databaseSwitcher" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-database"></i> {{ $currentDatabase['name'] ?? 'Database' }}
    </button>
    <ul class="dropdown-menu" aria-labelledby="databaseSwitcher">
        @foreach($availableDatabases as $key => $db)
        <li>
            <a class="dropdown-item {{ $db['is_current'] ? 'active' : '' }}" 
               href="#" 
               onclick="switchDatabase('{{ $key }}')"
               {{ !$db['is_connected'] ? 'style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $db['name'] }}</strong>
                        @if($db['is_current'])
                            <span class="badge bg-primary ms-1">Aktif</span>
                        @endif
                    </div>
                    <div>
                        @if($db['is_connected'])
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-times-circle text-danger"></i>
                        @endif
                    </div>
                </div>
                <small class="text-muted">{{ $db['description'] }}</small>
            </a>
        </li>
        @endforeach
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('database-switch.index') }}">
                <i class="fas fa-cog"></i> Kelola Database
            </a>
        </li>
    </ul>
</div>

<script>
function switchDatabase(databaseKey) {
    if (confirm('Apakah Anda yakin ingin mengubah database?')) {
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
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            Database berhasil diubah ke ${data.current_database.name}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                document.body.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Gagal mengubah database');
        });
    }
}
</script>
