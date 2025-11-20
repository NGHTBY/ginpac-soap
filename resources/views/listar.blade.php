<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Pacientes - GINPAC-SOAP</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header con botón de tema y navegación -->
        <header>
            <div class="header-top">
                <button id="theme-toggle" class="theme-btn" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                    <span class="theme-text">Modo Oscuro</span>
                </button>
            </div>
            <h1>Lista de Pacientes</h1>
            <p>Gestión completa de pacientes registrados en el sistema</p>
            <a href="{{ route('pacientes.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </header>
        
        <main>
            <!-- RF-08: Flujo de Listado - Muestra todos los pacientes en tabla -->
            <!-- RA-04: Interfaz que consume RF-03 (listar) del servicio SOAP -->
            
            <!-- Alertas de éxito/error -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            
            <!-- RF-08: Tabla de pacientes con botones de acción -->
            @if(empty($pacientes) || count($pacientes) === 0)
                <!-- Estado vacío cuando no hay pacientes -->
                <div class="empty-state">
                    <i class="fas fa-users-slash" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <p>No hay pacientes registrados en el sistema.</p>
                    <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Registrar Primer Paciente
                    </a>
                </div>
            @else
                <!-- Tabla con lista de pacientes -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>
                            <i class="fas fa-list"></i> 
                            Total de Pacientes: {{ count($pacientes) }}
                        </h3>
                    </div>
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> Cédula</th>
                                <th><i class="fas fa-user"></i> Nombres</th>
                                <th><i class="fas fa-users"></i> Apellidos</th>
                                <th><i class="fas fa-phone"></i> Teléfono</th>
                                <th><i class="fas fa-calendar-alt"></i> Fecha Nacimiento</th>
                                <th><i class="fas fa-cogs"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacientes as $paciente)
                            <tr>
                                <td><strong>{{ $paciente['cedula'] ?? 'N/A' }}</strong></td>
                                <td>{{ $paciente['nombres'] ?? 'N/A' }}</td>
                                <td>{{ $paciente['apellidos'] ?? 'N/A' }}</td>
                                <td>{{ $paciente['telefono'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($paciente['fecha_nacimiento']) && !empty($paciente['fecha_nacimiento']))
                                        {{ date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="actions">
                                    <!-- RF-09: Botón Editar - Navega a la vista de edición -->
                                    <a href="{{ route('pacientes.edit', $paciente['cedula']) }}" 
                                       class="btn btn-edit" 
                                       title="Editar paciente">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    
                                    <!-- RF-10: Botón Eliminar - Con confirmación JavaScript -->
                                    <form method="POST" 
                                          action="{{ route('pacientes.destroy', $paciente['cedula']) }}" 
                                          class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        @php
                                            $nombreCompleto = ($paciente['nombres'] ?? '') . ' ' . ($paciente['apellidos'] ?? '');
                                        @endphp
                                        <button type="submit" class="btn btn-delete" title="Eliminar paciente" onclick="return confirmDelete('{{ trim($nombreCompleto) }}')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Información adicional -->
                <div class="table-info">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Mostrando <strong>{{ count($pacientes) }}</strong> paciente(s) registrado(s) en el sistema.
                    </p>
                </div>
            @endif
        </main>
        
        <footer>
            <p>&copy; 2025 Clínica SaludTotal - Sistema GINPAC-SOAP</p>
            <p class="theme-info">Tema actual: <span id="current-theme">Claro</span></p>
        </footer>
    </div>

    <!-- Script para el sistema de tema oscuro/claro -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('.theme-text');
            const currentThemeSpan = document.getElementById('current-theme');
            
            // Recuperar tema guardado o usar preferencia del sistema
            const savedTheme = localStorage.getItem('theme') || 'light';
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Aplicar tema inicial
            if (savedTheme === 'dark' || (savedTheme === 'system' && systemPrefersDark)) {
                enableDarkMode();
            } else {
                enableLightMode();
            }
            
            // Event listener para el botón de tema
            themeToggle.addEventListener('click', function() {
                if (document.body.classList.contains('dark-theme')) {
                    enableLightMode();
                } else {
                    enableDarkMode();
                }
            });
            
            function enableDarkMode() {
                document.body.classList.add('dark-theme');
                document.body.classList.remove('light-theme');
                themeIcon.className = 'fas fa-sun';
                themeText.textContent = 'Modo Claro';
                currentThemeSpan.textContent = 'Oscuro';
                localStorage.setItem('theme', 'dark');
            }
            
            function enableLightMode() {
                document.body.classList.add('light-theme');
                document.body.classList.remove('dark-theme');
                themeIcon.className = 'fas fa-moon';
                themeText.textContent = 'Modo Oscuro';
                currentThemeSpan.textContent = 'Claro';
                localStorage.setItem('theme', 'light');
            }
            
            // RF-10: Función de confirmación para eliminación
            window.confirmDelete = function(nombrePaciente) {
                if (!nombrePaciente || nombrePaciente.trim() === '') {
                    nombrePaciente = 'este paciente';
                }
                return confirm(`¿Está seguro de que desea eliminar al paciente:\n"${nombrePaciente}"?\n\nEsta acción no se puede deshacer.`);
            };
        });
    </script>
</body>
</html>