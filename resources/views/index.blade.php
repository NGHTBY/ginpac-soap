<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GINPAC-SOAP - Gestor Interno de Pacientes</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Iconos para el botón de tema -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header con botón de tema -->
        <header>
            <div class="header-top">
                <button id="theme-toggle" class="theme-btn" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                    <span class="theme-text">Modo Oscuro</span>
                </button>
            </div>
            <h1>GINPAC-SOAP</h1>
            <p>Gestor Interno de Pacientes - Clínica SaludTotal</p>
        </header>
        
        <main class="main-menu">
            <div class="menu-grid">
                <div class="menu-card">
                    <h2><i class="fas fa-user-plus"></i> Registrar Paciente</h2>
                    <p>Agregar nuevo paciente al sistema</p>
                    <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Registrar Paciente
                    </a>
                </div>
                
                <div class="menu-card">
                    <h2><i class="fas fa-list"></i> Ver Pacientes</h2>
                    <p>Listar y gestionar pacientes registrados</p>
                    <a href="{{ route('pacientes.list') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Ver Pacientes
                    </a>
                </div>

                <!-- Card para el Dashboard -->
                <div class="menu-card">
                    <h2><i class="fas fa-chart-bar"></i> Dashboard</h2>
                    <p>Estadísticas y métricas del sistema</p>
                    <a href="{{ route('pacientes.dashboard') }}" class="btn btn-success">
                        <i class="fas fa-chart-bar"></i> Ver Dashboard
                    </a>
                </div>

                <!-- Card para Backups -->
                <div class="menu-card">
                    <h2><i class="fas fa-database"></i> Backups</h2>
                    <p>Gestión de respaldos del sistema</p>
                    <a href="{{ route('backup.listar') }}" class="btn btn-warning">
                        <i class="fas fa-database"></i> Gestionar Backups
                    </a>
                </div>

                <!-- Card para Exportar Datos -->
                <div class="menu-card">
                    <h2><i class="fas fa-file-export"></i> Exportar</h2>
                    <p>Exportar pacientes a formato CSV</p>
                    <a href="{{ route('pacientes.export') }}" class="btn btn-info">
                        <i class="fas fa-file-export"></i> Exportar CSV
                    </a>
                </div>

                <!-- Card para Servicio SOAP -->
                <div class="menu-card">
                    <h2><i class="fas fa-plug"></i> Servicio SOAP</h2>
                    <p>Información del servicio web</p>
                    <a href="/wsdl" target="_blank" class="btn btn-dark">
                        <i class="fas fa-plug"></i> Ver WSDL
                    </a>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Clínica SaludTotal - Sistema GINPAC-SOAP</p>
            <p class="theme-info">Tema actual: <span id="current-theme">Claro</span></p>
            <p class="system-info">
                <i class="fas fa-server"></i> 
                Servidor: {{ request()->getHttpHost() }} | 
                <i class="fas fa-clock"></i> 
                Hora: <span id="current-time">{{ date('H:i:s') }}</span>
            </p>
        </footer>
    </div>

    <script>
        // Sistema de Tema Oscuro/Claro
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('i');
            const themeText = themeToggle.querySelector('.theme-text');
            const currentThemeSpan = document.getElementById('current-theme');
            
            // Verificar tema guardado o preferencia del sistema
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
            
            // Escuchar cambios en la preferencia del sistema
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (localStorage.getItem('theme') === 'system') {
                    if (e.matches) {
                        enableDarkMode();
                    } else {
                        enableLightMode();
                    }
                }
            });

            // Actualizar hora en tiempo real
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit' 
                });
                document.getElementById('current-time').textContent = timeString;
            }

            // Actualizar cada segundo
            setInterval(updateTime, 1000);
            updateTime(); // Ejecutar inmediatamente

            // Efectos hover para las cards
            const menuCards = document.querySelectorAll('.menu-card');
            menuCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Mostrar notificación de carga suave
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            
            setTimeout(function() {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>
