<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Paciente - GINPAC-SOAP</title>
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
            <h1>Registrar Nuevo Paciente</h1>
            <p>Complete el formulario para agregar un nuevo paciente al sistema</p>
            <a href="{{ route('pacientes.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </header>
        
        <main>
            <!-- RF-07: Flujo de Creación - Formulario para registrar paciente -->
            <!-- RA-04: Interfaz de usuario que consume el servicio SOAP -->
            
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
            
            <!-- RF-01: Formulario de creación que enviará datos al servicio SOAP -->
            <form method="POST" action="{{ route('pacientes.store') }}" class="patient-form">
                @csrf <!-- Protección CSRF de Laravel -->
                
                <div class="form-group">
                    <label for="cedula">
                        <i class="fas fa-id-card"></i> Cédula *
                    </label>
                    <input type="text" id="cedula" name="cedula" required maxlength="20"
                           placeholder="Ingrese el número de cédula"
                           value="{{ old('cedula') }}">
                    <small>La cédula será el identificador único del paciente</small>
                    @error('cedula')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="nombres">
                        <i class="fas fa-user"></i> Nombres *
                    </label>
                    <input type="text" id="nombres" name="nombres" required maxlength="100"
                           placeholder="Ingrese los nombres completos"
                           value="{{ old('nombres') }}">
                    @error('nombres')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="apellidos">
                        <i class="fas fa-users"></i> Apellidos *
                    </label>
                    <input type="text" id="apellidos" name="apellidos" required maxlength="100"
                           placeholder="Ingrese los apellidos completos"
                           value="{{ old('apellidos') }}">
                    @error('apellidos')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i> Teléfono *
                    </label>
                    <input type="tel" id="telefono" name="telefono" required maxlength="15"
                           placeholder="Ingrese el número de teléfono"
                           value="{{ old('telefono') }}">
                    @error('telefono')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="fecha_nacimiento">
                        <i class="fas fa-calendar-alt"></i> Fecha de Nacimiento *
                    </label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                           value="{{ old('fecha_nacimiento') }}"
                           max="{{ date('Y-m-d') }}">
                    <small>Seleccione la fecha de nacimiento del paciente</small>
                    @error('fecha_nacimiento')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
                
                <!-- RF-07: Acciones del formulario -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Paciente
                    </button>
                    <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
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
        });
    </script>
</body>
</html>
