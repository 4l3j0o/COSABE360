<a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    Dashboard
</a>

<details class="group" data-sidebar-group="gestion">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Gestión Escolar
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('periodos.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Períodos Académicos
        </a>
        <a href="{{ route('levels.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V10l7-6 7 6v11M9 21v-6h6v6M8 11h.01M12 11h.01M16 11h.01"/></svg>
            Niveles Educativos
        </a>
        <a href="{{ route('subjects.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            Materias
        </a>
        <a href="{{ route('study_plans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Planes de Estudio
        </a>
        <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Horarios
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="personas">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Personas
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            Estudiantes
        </a>
        <a href="{{ route('inscripciones.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Nueva Inscripción
        </a>
        <a href="{{ route('tutores.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 10a4 4 0 100-8 4 4 0 000 8zm12 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Tutores
        </a>
        <a href="{{ route('teachers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l8-4-8-4-8 4 8 4zm0 4l8-4M4 10l8 4 8-4M12 14v7"/></svg>
            Docentes
        </a>
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Usuarios
        </a>
        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Roles y permisos
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="comunicacion">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Comunicación
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Comunicados
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="reportes">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Reportes
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('reportes.central.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/></svg>
            Central de Reportes
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="auditoria">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Auditoría
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('auditoria.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7.5A1.5 1.5 0 006 6.5v12A1.5 1.5 0 007.5 20h9a1.5 1.5 0 001.5-1.5v-12A1.5 1.5 0 0016.5 5H15m-6 0a1.5 1.5 0 001.5 1.5H15A1.5 1.5 0 0016.5 5M6 5a1.5 1.5 0 001.5-1.5h9A1.5 1.5 0 0015 3.5h-9A1.5 1.5 0 006 5z"/></svg>
            Historial de Operaciones
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="configuracion">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Configuración
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('tax-configuration.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6M9 10h6M9 18h3M6 3h9l3 3v15H6V3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3v4h4"/></svg>
            Configuración
        </a>
    </div>
</details>

<details class="group" data-sidebar-group="facturacion">
    <summary class="flex items-center gap-3 px-3 py-2 rounded-lg text-white/70 hover:bg-white/15 text-xs font-semibold tracking-widest uppercase cursor-pointer select-none">
        Pensiones
        <svg class="w-3.5 h-3.5 ml-auto transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="mt-1 space-y-1">
        <a href="{{ route('pensiones.estado-cuentas') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h4m-8 4h18a1 1 0 001-1V6a1 1 0 00-1-1H3a1 1 0 00-1 1v12a1 1 0 001 1z"/></svg>
            Estado de Cuentas
        </a>
        <a href="{{ route('pensiones.historial-pagos') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Historial de Pagos
        </a>
        <a href="{{ route('discount-types.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/15 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Descuentos / Becas
        </a>
    </div>
</details>

<script>
    // HU-39: recordar grupos abiertos/cerrados del sidebar por sesión
    (function () {
        var key = 'csb-sidebar-groups';
        function persist() {
            var o = {};
            document.querySelectorAll('[data-sidebar-group]').forEach(function (d) { o[d.dataset.sidebarGroup] = d.open; });
            try { localStorage.setItem(key, JSON.stringify(o)); } catch (e) {}
        }
        document.querySelectorAll('[data-sidebar-group]').forEach(function (d) {
            d.addEventListener('toggle', persist);
            try {
                var saved = JSON.parse(localStorage.getItem(key) || '{}');
                if (saved.hasOwnProperty(d.dataset.sidebarGroup)) d.open = saved[d.dataset.sidebarGroup];
            } catch (e) {}
        });
    })();
</script>