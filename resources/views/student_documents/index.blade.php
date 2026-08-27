@extends('layouts.app')
@section('titulo', 'Documentación Estudiantes')

@section('sidebar')
    @include('dashboard.partials._sidebar-admin')
@endsection

@section('contenido')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-foreground">Documentación Estudiantes</h1>
            <p class="text-sm text-muted-foreground">Gestión de documentos administrativos</p>
        </div>
        <a href="{{ route('student_documents.pending') }}" class="inline-flex items-center gap-2 bg-warning text-warning-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Documentación Pendiente
        </a>
    </div>

    @if(!$estudiante)
        <div class="bg-card border border-border rounded-xl p-8">
            <h2 class="text-lg font-semibold text-foreground mb-4">Seleccionar Estudiante</h2>
            <div class="relative">
                <input type="text" id="student-search" placeholder="Buscar por nombre o código..." class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <div id="student-results" class="absolute top-full left-0 right-0 mt-1 bg-card border border-border rounded-lg shadow-lg z-10 hidden max-h-60 overflow-y-auto">
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-3">Escribe el nombre o código del estudiante para buscar.</p>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">{{ $estudiante->nombres }} {{ $estudiante->apellidos }}</h2>
                    <p class="text-sm text-muted-foreground">Código: {{ $estudiante->codigo }}</p>
                </div>
                <a href="{{ route('student_documents.index') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                    ← Volver al buscador
                </a>
            </div>

            <button onclick="openModal('modal-upload')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Subir Documento
            </button>
        </div>

        @if($documents->isEmpty())
            <div class="bg-card border border-border rounded-xl p-12 text-center">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-muted-foreground text-sm">No hay documentos registrados</p>
            </div>
        @else
            <div class="bg-card border border-border rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Tipo de Documento</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Archivo</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Estado</th>
                                <th class="text-left px-4 py-3 font-semibold text-foreground">Fecha</th>
                                <th class="text-right px-4 py-3 font-semibold text-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr class="border-b border-border last:border-b-0 hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $document->tipo_documento }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ asset('storage/' . $document->archivo) }}" target="_blank" class="text-primary hover:underline text-xs">
                                            Ver archivo
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colores = [
                                                'PENDIENTE' => 'bg-warning/10 text-warning',
                                                'ENTREGADO' => 'bg-success/10 text-success',
                                                'VERIFICADO' => 'bg-info/10 text-info',
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$document->estado] ?? 'bg-muted text-muted-foreground' }}">
                                            {{ $document->estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">{{ $document->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <form method="POST" action="{{ route('student_documents.updateStatus', $document) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="estado" value="{{ $document->estado === 'PENDIENTE' ? 'ENTREGADO' : 'PENDIENTE' }}">
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-foreground cursor-pointer" title="{{ $document->estado === 'PENDIENTE' ? 'Marcar como entregado' : 'Marcar como pendiente' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    @if(session('mensaje'))
        <div class="fixed bottom-4 right-4 bg-success text-success-foreground px-4 py-3 rounded-lg shadow-lg z-50">
            {{ session('mensaje') }}
        </div>
    @endif
@endsection

@push('modals')
    @if($estudiante)
        <div id="modal-upload" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(0,0,0,0.3); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);">
            <div style="min-height:100%; padding:2rem 1rem; display:flex; align-items:flex-start; justify-content:center;">
                <div style="width:100%; max-width:48rem; background:var(--card); border:1px solid var(--border); border-radius:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); border-radius:1rem 1rem 0 0; background:var(--card);">
                        <h2 style="font-size:1rem; font-weight:700; color:var(--foreground);">Subir Documento</h2>
                        <button onclick="closeModal('modal-upload')" style="padding:0.375rem; border-radius:0.5rem; cursor:pointer; color:var(--muted-foreground); background:none; border:none;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='none'">
                            <svg style="width:1.25rem; height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('student_documents.store') }}" enctype="multipart/form-data" style="padding:1.5rem;">
                        @csrf
                        <input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Tipo de Documento <span class="text-error">*</span></label>
                                <select name="tipo_documento" required class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                    <option value="">Seleccionar...</option>
                                    <option value="Certificado de Nacimiento">Certificado de Nacimiento</option>
                                    <option value="Cédula de Identidad">Cédula de Identidad</option>
                                    <option value="Fotografía">Fotografía</option>
                                    <option value="Certificado de Estudios">Certificado de Estudios</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Archivo <span class="text-error">*</span></label>
                                <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                                <p class="text-xs text-muted-foreground mt-1">Formatos: PDF, JPG, PNG. Máximo 5MB.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-600 mb-1.5">Observaciones</label>
                                <textarea name="observaciones" rows="3" class="w-full px-3 py-2.5 border border-input rounded-lg bg-background text-foreground text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" placeholder="Observaciones opcionales..."></textarea>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--border); margin-top:1rem;">
                            <button type="button" onclick="closeModal('modal-upload')" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:1px solid var(--border); background:var(--background); color:var(--foreground); cursor:pointer;">Cancelar</button>
                            <button type="submit" style="padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; border:none; background:var(--primary); color:var(--primary-foreground); cursor:pointer;">Subir Documento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endpush

@push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Autocompletado de estudiantes
        let debounceTimer;
        const searchInput = document.getElementById('student-search');
        const resultsDiv = document.getElementById('student-results');

        if (searchInput && resultsDiv) {
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) {
                    resultsDiv.classList.add('hidden');
                    resultsDiv.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch('{{ route('student_documents.search_student') }}?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-muted-foreground">No se encontraron estudiantes</div>';
                            } else {
                                resultsDiv.innerHTML = data.map(student => `
                                    <div class="px-4 py-2.5 hover:bg-muted cursor-pointer border-b border-border last:border-b-0 transition-colors"
                                         onclick="selectStudent(${student.id})">
                                        <div class="font-medium text-foreground text-sm">${student.apellidos} ${student.nombres}</div>
                                        <div class="text-xs text-muted-foreground">${student.codigo}</div>
                                    </div>
                                `).join('');
                            }
                            resultsDiv.classList.remove('hidden');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            resultsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-error">Error al buscar estudiantes</div>';
                            resultsDiv.classList.remove('hidden');
                        });
                }, 300);
            });

            // Cerrar resultados al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                    resultsDiv.classList.add('hidden');
                }
            });
        }

        function selectStudent(studentId) {
            window.location.href = '{{ route('student_documents.index') }}?estudiante_id=' + studentId;
        }
    </script>
@endpush
