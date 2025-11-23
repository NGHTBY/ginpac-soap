<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GINPAC-SOAP</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h1>Dashboard del Sistema</h1>
            <p>Estadísticas y métricas de pacientes - Clínica SaludTotal</p>
            
            <div class="header-actions">
                <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="{{ route('pacientes.list') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> Ver Pacientes
                </a>
                <a href="{{ route('pacientes.create') }}" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Nuevo Paciente
                </a>
            </div>
        </header>
        
        <main>
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

            <!-- Tarjetas de Métricas Principales -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon total-patients">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Total Pacientes</h3>
                        <span class="metric-value">{{ $totalPacientes }}</span>
                        <span class="metric-trend">
                            <i class="fas fa-arrow-up"></i>
                            {{ $pacientesEsteMes }} este mes
                        </span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon age-average">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Edad Promedio</h3>
                        <span class="metric-value">{{ $estadisticasEdad['promedio'] }} años</span>
                        <span class="metric-trend">
                            Rango: {{ $estadisticasEdad['minima'] }} - {{ $estadisticasEdad['maxima'] }} años
                        </span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon age-distribution">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Distribución</h3>
                        <span class="metric-value">{{ count($distribucionEdad) }} rangos</span>
                        <span class="metric-trend">
                            {{ array_sum($distribucionEdad) }} pacientes analizados
                        </span>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon system-health">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Estado Sistema</h3>
                        <span class="metric-value">Optimo</span>
                        <span class="metric-trend">
                            <i class="fas fa-check-circle"></i>
                            Todos los servicios activos
                        </span>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Estadísticas Detalladas -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> Distribución por Edad</h3>
                        <p>Pacientes agrupados por rangos de edad</p>
                    </div>
                    <canvas id="ageDistributionChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Resumen de Edades</h3>
                        <p>Estadísticas generales de edad</p>
                    </div>
                    <div class="stats-container">
                        <div class="stat-item">
                            <span class="stat-label">Edad Mínima</span>
                            <span class="stat-value">{{ $estadisticasEdad['minima'] }} años</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Edad Máxima</span>
                            <span class="stat-value">{{ $estadisticasEdad['maxima'] }} años</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Edad Promedio</span>
                            <span class="stat-value">{{ $estadisticasEdad['promedio'] }} años</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Pacientes Analizados</span>
                            <span class="stat-value">{{ $estadisticasEdad['total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimos Pacientes Registrados -->
            <div class="recent-patients">
                <div class="section-header">
                    <h3><i class="fas fa-clock"></i> Resumen del Sistema</h3>
                    <p>Información general de pacientes registrados</p>
                </div>
                
                @if(count($pacientes) > 0)
                    <div class="patients-summary">
                        @foreach(array_slice($pacientes, 0, 5) as $paciente)
                            <div class="patient-summary-card">
                                <div class="patient-avatar">
                                    {{ substr($paciente['nombres'], 0, 1) }}{{ substr($paciente['apellidos'], 0, 1) }}
                                </div>
                                <div class="patient-info">
                                    <h4>{{ $paciente['nombres'] }} {{ $paciente['apellidos'] }}</h4>
                                    <p>Cédula: {{ $paciente['cedula'] }}</p>
                                    <span class="patient-age">
                                        @php
                                            if(!empty($paciente['fecha_nacimiento'])) {
                                                try {
                                                    $fechaNac = new DateTime($paciente['fecha_nacimiento']);
                                                    $hoy = new DateTime();
                                                    $edad = $hoy->diff($fechaNac)->y;
                                                    echo $edad . ' años';
                                                } catch (Exception $e) {
                                                    echo 'N/A';
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                        @endphp
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if(count($pacientes) > 5)
                        <div class="view-all-container">
                            <a href="{{ route('pacientes.list') }}" class="btn btn-outline">
                                <i class="fas fa-eye"></i> Ver todos los pacientes ({{ count($pacientes) }})
                            </a>
                        </div>
                    @endif
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
            <p class="theme-info">
                <i class="fas fa-chart-bar"></i>
                Dashboard actualizado el {{ date('d/m/Y H:i') }}
            </p>
        </footer>
    </div>

    <!-- Script para los gráficos -->
    <script>
        // @ts-nocheck
        document.addEventListener('DOMContentLoaded', function() {
            // Datos para el gráfico de distribución de edad
            const distributionData = {
                labels: <?php echo json_encode(array_keys($distribucionEdad)); ?>,
                datasets: [{
                    label: 'Pacientes por Rango de Edad',
                    data: <?php echo json_encode(array_values($distribucionEdad)); ?>,
                    backgroundColor: [
                        '#4f46e5', '#7c3aed', '#a855f7', 
                        '#c026d3', '#db2777', '#e11d48'
                    ],
                    borderColor: [
                        '#4f46e5', '#7c3aed', '#a855f7',
                        '#c026d3', '#db2777', '#e11d48'
                    ],
                    borderWidth: 2
                }]
            };

            // Configuración del gráfico de barras
            const ctx = document.getElementById('ageDistributionChart').getContext('2d');
            if (ctx) {
                const ageChart = new Chart(ctx, {
                    type: 'bar',
                    data: distributionData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Pacientes: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Sistema de tema oscuro/claro
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
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
                    if (themeIcon) themeIcon.className = 'fas fa-sun';
                    if (themeText) themeText.textContent = 'Modo Claro';
                    if (currentThemeSpan) currentThemeSpan.textContent = 'Oscuro';
                    localStorage.setItem('theme', 'dark');
                }
                
                function enableLightMode() {
                    document.body.classList.add('light-theme');
                    document.body.classList.remove('dark-theme');
                    if (themeIcon) themeIcon.className = 'fas fa-moon';
                    if (themeText) themeText.textContent = 'Modo Oscuro';
                    if (currentThemeSpan) currentThemeSpan.textContent = 'Claro';
                    localStorage.setItem('theme', 'light');
                }
            }
        });
    </script>
</body>
</html>
