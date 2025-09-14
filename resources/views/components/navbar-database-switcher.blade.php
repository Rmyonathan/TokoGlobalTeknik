@props(['currentDatabase', 'availableDatabases'])

<div class="dropdown d-inline-block">
    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="navbarDatabaseSwitcher" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
        <i class="fas fa-database"></i> {{ $currentDatabase['name'] ?? 'Database' }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDatabaseSwitcher" style="min-width: 250px;">
        <li class="dropdown-header">
            <i class="fas fa-database"></i> Pilih Database
        </li>
        @foreach($availableDatabases as $key => $db)
        <li>
            <a class="dropdown-item {{ $db['is_current'] ? 'active' : '' }}" 
               href="#" 
               onclick="switchDatabase('{{ $key }}')"
               {{ !$db['is_connected'] ? 'style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <strong style="font-size: 0.9rem;">{{ $db['name'] }}</strong>
                            @if($db['is_current'])
                                <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">Aktif</span>
                            @endif
                        </div>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $db['description'] }}</small>
                        </div>
                        <div class="mt-1">
                            <small class="text-info" style="font-size: 0.7rem;">
                                <i class="fas fa-server"></i> {{ $db['connection'] ?? $key }}
                            </small>
                        </div>
                    </div>
                    <div class="ms-2">
                        @if($db['is_connected'])
                            <i class="fas fa-check-circle text-success" style="font-size: 0.9rem;" title="Terhubung"></i>
                        @else
                            <i class="fas fa-times-circle text-danger" style="font-size: 0.9rem;" title="Tidak Terhubung"></i>
                        @endif
                    </div>
                </div>
            </a>
        </li>
        @endforeach
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('database-switch.index') }}" style="font-size: 0.85rem;">
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
