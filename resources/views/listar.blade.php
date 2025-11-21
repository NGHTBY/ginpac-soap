<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pacientes - GINPAC-SOAP</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <button id="theme-toggle" class="theme-btn" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                    <span class="theme-text">Modo Oscuro</span>
                </button>
            </div>
            <h1>Lista de Pacientes</h1>
            <p>Gestión y administración de pacientes registrados</p>
            
            <div class="header-actions">
                <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Paciente
                </a>
                <a href="{{ route('pacientes.export') }}" class="btn btn-export">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
                <a href="{{ route('pacientes.index') }}" class="btn btn-back">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </div>
        </header>
        
        <main>
            <!-- Sistema de Búsqueda en Tiempo Real -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar pacientes por cédula, nombre, apellido o teléfono...">
                </div>
                <div class="search-stats">
                    <span id="resultCount">{{ count($pacientes) }}</span> pacientes encontrados de {{ count($pacientes) }} totales
                </div>
            </div>

            <!-- Alertas -->
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

            <!-- Tabla de Pacientes -->
            <div class="table-container">
                @if(count($pacientes) > 0)
                    <table class="patient-table">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Teléfono</th>
                                <th>Fecha Nacimiento</th>
                                <th>Edad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTableBody">
                            @foreach($pacientes as $paciente)
                                <tr class="patient-row" data-search="{{ strtolower($paciente['cedula'] . ' ' . $paciente['nombres'] . ' ' . $paciente['apellidos'] . ' ' . $paciente['telefono']) }}">
                                    <td class="cedula-cell">{{ $paciente['cedula'] }}</td>
                                    <td class="name-cell">{{ $paciente['nombres'] }}</td>
                                    <td class="name-cell">{{ $paciente['apellidos'] }}</td>
                                    <td class="phone-cell">{{ $paciente['telefono'] }}</td>
                                    <td class="date-cell">{{ $paciente['fecha_nacimiento'] }}</td>
                                    <td class="age-cell">
                                        @php
                                            $fechaNac = new DateTime($paciente['fecha_nacimiento']);
                                            $hoy = new DateTime();
                                            $edad = $hoy->diff($fechaNac)->y;
                                            echo $edad . ' años';
                                        @endphp
                                    </td>
                                    <td class="actions-cell">
                                        <a href="{{ route('pacientes.edit', $paciente['cedula']) }}" 
                                           class="btn btn-edit" 
                                           title="Editar paciente">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pacientes.destroy', $paciente['cedula']) }}" 
                                              method="POST" 
                                              class="delete-form"
                                              onsubmit="return confirm('¿Está seguro de eliminar al paciente {{ $paciente['nombres'] }} {{ $paciente['apellidos'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-delete" title="Eliminar paciente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>No hay pacientes registrados</h3>
                        <p>Comience agregando el primer paciente al sistema</p>
                        <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Registrar Primer Paciente
                        </a>
                    </div>
                @endif
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Clínica SaludTotal - Sistema GINPAC-SOAP</p>
            <p class="theme-info">Tema actual: <span id="current-theme">Claro</span></p>
        </footer>
    </div>

    <!-- Script para el sistema de búsqueda -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const patientRows = document.querySelectorAll('.patient-row');
            const resultCount = document.getElementById('resultCount');
            const totalPatients = {{ count($pacientes) }};
            
            // Sistema de búsqueda en tiempo real
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;
                
                patientRows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    if (searchData.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                resultCount.textContent = visibleCount;
                
                // Mostrar mensaje si no hay resultados
                const tableBody = document.getElementById('patientsTableBody');
                if (visibleCount === 0 && searchTerm !== '') {
                    if (!document.getElementById('noResultsRow')) {
                        const noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noResultsRow';
                        noResultsRow.innerHTML = `
                            <td colspan="7" class="no-results">
                                <i class="fas fa-search"></i>
                                <div>
                                    <strong>No se encontraron pacientes</strong>
                                    <p>No hay resultados para "<span class="search-term">${searchTerm}</span>"</p>
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(noResultsRow);
                    }
                } else {
                    const noResultsRow = document.getElementById('noResultsRow');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            });
            
            // Sistema de tema oscuro/claro
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('.theme-text');
            const currentThemeSpan = document.getElementById('current-theme');
            
            const savedTheme = localStorage.getItem('theme') || 'light';
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (savedTheme === 'system' && systemPrefersDark)) {
                enableDarkMode();
            } else {
                enableLightMode();
            }
            
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
        });
    </script>
</body>
</html>
