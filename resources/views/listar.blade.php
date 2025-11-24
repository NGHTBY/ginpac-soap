<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pacientes - GINPAC-SOAP</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos mejorados para la tabla */
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px 0;
        }

        .patient-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95em;
        }

        .patient-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .patient-table th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .patient-table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }

        .patient-table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .patient-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Estilos específicos para celdas */
        .cedula-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-color);
        }

        .name-cell {
            font-weight: 500;
            color: var(--text-color);
        }

        .phone-cell {
            font-family: 'Courier New', monospace;
            color: var(--text-muted);
        }

        .date-cell {
            color: var(--text-muted);
            font-size: 0.9em;
        }

        .age-cell {
            text-align: center;
            font-weight: 600;
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 20px;
            padding: 6px 12px;
            display: inline-block;
            min-width: 70px;
        }

        /* Acciones */
        .actions-cell {
            text-align: center;
            white-space: nowrap;
        }

        .actions-cell .btn {
            margin: 0 2px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #3498db;
            color: white;
            border: none;
        }

        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        /* Búsqueda mejorada */
        .search-container {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .search-box {
            position: relative;
            max-width: 500px;
            margin-bottom: 10px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: var(--bg-color);
            color: var(--text-color);
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-stats {
            color: var(--text-muted);
            font-size: 0.9em;
        }

        .search-stats span {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Header actions mejorado */
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn-export {
            background: #27ae60;
            color: white;
        }

        .btn-export:hover {
            background: #219a52;
        }

        .btn-back {
            background: #7f8c8d;
            color: white;
        }

        .btn-back:hover {
            background: #6c7b7d;
        }

        /* Estados vacíos y sin resultados */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .no-results i {
            font-size: 3em;
            color: #bdc3c7;
            margin-bottom: 15px;
            display: block;
        }

        .no-results .search-term {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Alertas mejoradas */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            border-left-color: #27ae60;
            color: #27ae60;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border-left-color: #e74c3c;
            color: #e74c3c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }

            .patient-table {
                min-width: 800px;
            }

            .header-actions {
                flex-direction: column;
            }

            .header-actions .btn {
                width: 100%;
                text-align: center;
            }

            .search-box {
                max-width: 100%;
            }
        }

        /* Efectos de carga */
        .patient-row {
            transition: all 0.3s ease;
        }

        /* Formularios en línea */
        .delete-form {
            display: inline-block;
        }
    </style>
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
            <h1><i class="fas fa-list"></i> Lista de Pacientes</h1>
            <p>Gestión y administración de pacientes registrados</p>
            
            <div class="header-actions">
                <a href="<?php echo route('pacientes.create'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Paciente
                </a>
                <a href="<?php echo route('pacientes.export'); ?>" class="btn btn-export">
                    <i class="fas fa-file-export"></i> Exportar CSV
                </a>
                <a href="<?php echo route('pacientes.index'); ?>" class="btn btn-back">
                    <i class="fas fa-home"></i> Volver al Inicio
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
                    <span id="resultCount"><?php echo count($pacientes); ?></span> pacientes encontrados de <?php echo count($pacientes); ?> totales
                </div>
            </div>

            <!-- Alertas -->
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo session('success'); ?>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo session('error'); ?>
                </div>
            <?php endif; ?>

            <!-- Tabla de Pacientes -->
            <div class="table-container">
                <?php if(count($pacientes) > 0): ?>
                    <table class="patient-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> Cédula</th>
                                <th><i class="fas fa-user"></i> Nombres</th>
                                <th><i class="fas fa-users"></i> Apellidos</th>
                                <th><i class="fas fa-phone"></i> Teléfono</th>
                                <th><i class="fas fa-calendar"></i> Fecha Nacimiento</th>
                                <th><i class="fas fa-birthday-cake"></i> Edad</th>
                                <th><i class="fas fa-cog"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTableBody">
                            <?php foreach($pacientes as $paciente): ?>
                                <tr class="patient-row" data-search="<?php echo strtolower($paciente['cedula'] . ' ' . $paciente['nombres'] . ' ' . $paciente['apellidos'] . ' ' . $paciente['telefono']); ?>">
                                    <td class="cedula-cell">
                                        <i class="fas fa-fingerprint"></i> <?php echo $paciente['cedula']; ?>
                                    </td>
                                    <td class="name-cell"><?php echo $paciente['nombres']; ?></td>
                                    <td class="name-cell"><?php echo $paciente['apellidos']; ?></td>
                                    <td class="phone-cell">
                                        <i class="fas fa-phone"></i> <?php echo $paciente['telefono']; ?>
                                    </td>
                                    <td class="date-cell">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php if(!empty($paciente['fecha_nacimiento'])): ?>
                                            <?php echo \Carbon\Carbon::parse($paciente['fecha_nacimiento'])->format('d/m/Y'); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="age-cell">
                                        <?php if(!empty($paciente['fecha_nacimiento'])): ?>
                                            <?php
                                            try {
                                                $fechaNac = new DateTime($paciente['fecha_nacimiento']);
                                                $hoy = new DateTime();
                                                $edad = $hoy->diff($fechaNac)->y;
                                                echo $edad . ' años';
                                            } catch (Exception $e) {
                                                echo 'N/A';
                                            }
                                            ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="<?php echo route('pacientes.edit', $paciente['cedula']); ?>" 
                                           class="btn btn-edit" 
                                           title="Editar paciente">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form action="<?php echo route('pacientes.destroy', $paciente['cedula']); ?>" 
                                              method="POST" 
                                              class="delete-form"
                                              onsubmit="return confirm('¿Está seguro de eliminar al paciente <?php echo $paciente['nombres']; ?> <?php echo $paciente['apellidos']; ?>?')">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-delete" title="Eliminar paciente">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>No hay pacientes registrados</h3>
                        <p>Comience agregando el primer paciente al sistema</p>
                        <a href="<?php echo route('pacientes.create'); ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Registrar Primer Paciente
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Clínica SaludTotal - Sistema GINPAC-SOAP</p>
            <p class="theme-info">Tema actual: <span id="current-theme">Claro</span></p>
        </footer>
    </div>

    <!-- Script para el sistema de búsqueda CORREGIDO -->
    <script>
        // @ts-nocheck
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const patientRows = document.querySelectorAll('.patient-row');
            const resultCount = document.getElementById('resultCount');
            const totalPatients = <?php echo count($pacientes); ?>;
            
            // Sistema de búsqueda en tiempo real
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let visibleCount = 0;
                    
                    patientRows.forEach(function(row) {
                        const searchData = row.getAttribute('data-search');
                        if (searchData && searchData.includes(searchTerm)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    if (resultCount) {
                        resultCount.textContent = visibleCount;
                    }
                    
                    // Mostrar mensaje si no hay resultados
                    const tableBody = document.getElementById('patientsTableBody');
                    if (visibleCount === 0 && searchTerm !== '') {
                        if (!document.getElementById('noResultsRow')) {
                            const noResultsRow = document.createElement('tr');
                            noResultsRow.id = 'noResultsRow';
                            noResultsRow.innerHTML = '<td colspan="7" class="no-results">' +
                                '<i class="fas fa-search"></i>' +
                                '<div>' +
                                '<strong>No se encontraron pacientes</strong>' +
                                '<p>No hay resultados para "<span class="search-term">' + searchTerm + '</span>"</p>' +
                                '</div>' +
                                '</td>';
                            if (tableBody) {
                                tableBody.appendChild(noResultsRow);
                            }
                        }
                    } else {
                        const noResultsRow = document.getElementById('noResultsRow');
                        if (noResultsRow) {
                            noResultsRow.remove();
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

            // Efectos de hover mejorados
            patientRows.forEach(function(row) {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });

            // Manejar eliminación con feedback visual
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    const originalText = button.innerHTML;
                    
                    // Mostrar estado de carga
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
                    
                    // Permitir que el formulario se envíe
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 3000);
                });
            });
        });
    </script>
</body>
</html>
