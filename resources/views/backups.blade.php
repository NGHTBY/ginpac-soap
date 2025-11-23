<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Backups - GINPAC-SOAP</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .backups-container {
            margin: 20px 0;
        }
        
        .backups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .backup-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .backup-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        .backup-card h3 {
            margin: 0 0 10px 0;
            color: var(--text-primary);
            font-size: 1.1em;
            word-break: break-all;
        }
        
        .backup-card p {
            margin: 5px 0;
            color: var(--text-secondary);
            font-size: 0.9em;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--bg-card);
            border-radius: 8px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .loading, .no-data, .error {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-size: 1.1em;
        }
        
        .error {
            color: var(--danger-color);
        }
        
        .btn-restore {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-restore:hover {
            background: #e67e22;
            transform: translateY(-1px);
        }
        
        .backup-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .backup-info {
            background: rgba(52, 152, 219, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
            color: var(--text-primary);
        }

        .backup-info h3 {
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .backup-info p {
            color: var(--text-secondary);
            margin: 0;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        footer {
            background: var(--bg-footer);
            color: var(--text-secondary);
            border-top: 1px solid var(--border-color);
        }

        footer code {
            background: var(--bg-secondary);
            color: var(--text-primary);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .header-top {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .theme-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .theme-btn:hover {
            background: var(--btn-primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .theme-btn i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .header-top {
                position: static;
                margin-bottom: 1rem;
                display: flex;
                justify-content: center;
            }
            
            .action-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .backups-grid {
                grid-template-columns: 1fr;
            }
            
            .backup-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <button class="theme-btn" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                    <span class="theme-text">Modo Oscuro</span>
                </button>
            </div>
            <h1><i class="fas fa-database"></i> Gestión de Backups</h1>
            <p>Sistema de respaldos - Clínica SaludTotal</p>
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </header>

        <main>
            <!-- Información del sistema -->
            <div class="backup-info">
                <h3><i class="fas fa-info-circle"></i> Información</h3>
                <p>Los backups se almacenan automáticamente en el servidor. Puedes crear respaldos manuales, restaurar versiones anteriores o descargar los archivos de backup.</p>
            </div>

            <!-- Barra de acciones -->
            <div class="action-bar">
                <div>
                    <h3>Respaldos del Sistema</h3>
                    <p id="backup-count" class="text-muted">Cargando...</p>
                </div>
                <button id="crearBackup" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Nuevo Backup
                </button>
            </div>

            <!-- Lista de backups -->
            <div id="backupsList" class="backups-container">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Cargando backups...
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Clínica SaludTotal - Sistema GINPAC-SOAP</p>
            <p>Backups almacenados en: <code>storage/backups/</code></p>
        </footer>
    </div>

    <script>
        // Función para cambiar entre temas
        function toggleTheme() {
            const body = document.body;
            const themeBtn = document.querySelector('.theme-btn');
            const themeIcon = themeBtn.querySelector('i');
            const themeText = themeBtn.querySelector('.theme-text');
            
            if (body.classList.contains('dark-theme')) {
                body.classList.remove('dark-theme');
                themeIcon.className = 'fas fa-moon';
                themeText.textContent = 'Modo Oscuro';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.add('dark-theme');
                themeIcon.className = 'fas fa-sun';
                themeText.textContent = 'Modo Claro';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Aplicar tema guardado al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const themeBtn = document.querySelector('.theme-btn');
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                if (themeBtn) {
                    const themeIcon = themeBtn.querySelector('i');
                    const themeText = themeBtn.querySelector('.theme-text');
                    themeIcon.className = 'fas fa-sun';
                    themeText.textContent = 'Modo Claro';
                }
            }

            // Código existente para backups
            const backupsList = document.getElementById('backupsList');
            const crearBackupBtn = document.getElementById('crearBackup');
            const backupCount = document.getElementById('backup-count');

            // Cargar lista de backups
            function cargarBackups() {
                backupsList.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando backups...</div>';
                
                fetch('{{ route("backup.listar.api") }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            mostrarBackups(data.backups);
                            backupCount.textContent = `${data.total} backup(s) encontrado(s)`;
                        } else {
                            backupsList.innerHTML = '<div class="error"><i class="fas fa-exclamation-triangle"></i> Error: ' + (data.message || 'Error desconocido') + '</div>';
                            backupCount.textContent = 'Error al cargar';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        backupsList.innerHTML = '<div class="error"><i class="fas fa-exclamation-triangle"></i> Error al cargar backups: ' + error.message + '</div>';
                        backupCount.textContent = 'Error al cargar';
                    });
            }

            // Mostrar backups en la lista
            function mostrarBackups(backups) {
                if (backups.length === 0) {
                    backupsList.innerHTML = '<div class="no-data"><i class="fas fa-database"></i> No hay backups disponibles</div>';
                    return;
                }

                let html = '<div class="backups-grid">';
                backups.forEach(backup => {
                    html += `
                        <div class="backup-card">
                            <h3><i class="fas fa-file-archive"></i> ${backup.nombre}</h3>
                            <p><strong><i class="fas fa-calendar"></i> Fecha:</strong> ${backup.fecha}</p>
                            <p><strong><i class="fas fa-weight-hanging"></i> Tamaño:</strong> ${backup.tamaño}</p>
                            <div class="backup-actions">
                                <button class="btn btn-restore restaurar-btn" data-file="${backup.nombre}">
                                    <i class="fas fa-undo"></i> Restaurar
                                </button>
                                <button class="btn btn-secondary descargar-btn" data-file="${backup.nombre}">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                backupsList.innerHTML = html;

                // Agregar event listeners a los botones de restaurar
                document.querySelectorAll('.restaurar-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const file = this.getAttribute('data-file');
                        restaurarBackup(file);
                    });
                });

                // Agregar event listeners a los botones de descargar
                document.querySelectorAll('.descargar-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const file = this.getAttribute('data-file');
                        descargarBackup(file);
                    });
                });
            }

            // Crear nuevo backup
            crearBackupBtn.addEventListener('click', function() {
                if (crearBackupBtn.disabled) return;
                
                crearBackupBtn.disabled = true;
                crearBackupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando Backup...';

                fetch('{{ route("backup.crear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Backup creado exitosamente: ' + data.backup_file);
                        cargarBackups();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Error desconocido al crear backup'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error de conexión al crear backup: ' + error.message);
                })
                .finally(() => {
                    crearBackupBtn.disabled = false;
                    crearBackupBtn.innerHTML = '<i class="fas fa-plus"></i> Crear Nuevo Backup';
                });
            });

            // Restaurar backup
            function restaurarBackup(file) {
                if (!confirm(`⚠️ ¿Estás seguro de que quieres restaurar el backup "${file}"?\n\nEsto reemplazará los datos actuales de pacientes y no se puede deshacer.`)) {
                    return;
                }

                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restaurando...';

                fetch('{{ route("backup.restaurar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ backup_file: file })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Backup restaurado exitosamente');
                        cargarBackups();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Error al restaurar backup'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-undo"></i> Restaurar';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error de conexión al restaurar backup: ' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-undo"></i> Restaurar';
                });
            }

            // Descargar backup - VERSIÓN CORREGIDA
            function descargarBackup(file) {
                // Validar que el archivo es seguro
                if (!file || file.includes('..') || file.includes('/') || file.includes('\\')) {
                    alert('❌ Nombre de archivo no válido');
                    return;
                }

                if (!confirm(`¿Descargar el backup: ${file}?`)) {
                    return;
                }

                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Descargando...';

                // Usar la nueva ruta de descarga
                const downloadUrl = '{{ url("/backups/descargar") }}/' + encodeURIComponent(file);
                
                // Crear enlace temporal para descarga
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = file;
                link.style.display = 'none';
                link.target = '_blank';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Restaurar el botón después de un breve delay
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('✅ Descarga iniciada: ' + file);
                }, 1000);
            }

            // Cargar backups al iniciar
            cargarBackups();
        });
    </script>
</body>
</html>
