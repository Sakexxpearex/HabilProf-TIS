<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// --- ESTADOS REACTIVOS ---
const form = reactive({
    id_habilitacion: '',
    modalidad: 'PrIng', // Valor inicial por defecto
    alumno_id: '',
    alumno_nombre: '',
    profesor_dinf_id: '',
    profesor_tutor_id: '',
    comision_profesor_id: '',
    co_guia_id: '',            // ahora es id del profesor co-guía (opcional)
    semestre_inicio: '',
    
    // Campos proyecto
    titulo: '',
    descripcion_proyecto: '',

    // Campos práctica
    empresa_nombre: '',
    supervisor_empresa: '',
    descripcion_practica: '',
});

const showSuccess = ref(false);
const errorMessage = ref('');
const alumnos = ref([]);      // lista de alumnos desde API
const profesores = ref([]);   // lista de profesores desde API

// Computeds para mostrar secciones
const isProyecto = computed(() => ['PrIng', 'PrInv'].includes(form.modalidad));
const isPractica = computed(() => form.modalidad === 'PrTut');

// --- FUNCIONES ---
// Cargar alumnos y profesores (suponiendo que el endpoint devuelva { alumnos: [...], profesores: [...] })
const fetchUsers = async () => {
    try {
        const res = await axios.get('/api/users/list');
        // Si tu backend devuelve un array mezclado en lugar de {alumnos,profesores},
        // ver nota abajo para ajustar.
        if (res.data.alumnos && res.data.profesores) {
            alumnos.value = res.data.alumnos;
            profesores.value = res.data.profesores;
        } else {
            // fallback: si backend devuelve lista mezclada, detectar por esquema y separar por tablas
            // asumimos que cada objeto tiene { id, name, type } OR id único de tabla
            const data = Array.isArray(res.data) ? res.data : [];
            // Si tu backend devolvía { id, name } sin type, preferible cambiar backend.
            // Intento separar por heurística: si ya existe la tabla 'alumno' con esos ids, backend debe devolver types.
            alumnos.value = data.filter(x => x.type === 'alumno' || x.role === 'alumno');
            profesores.value = data.filter(x => x.type === 'profesor' || x.role === 'profesor');

            // Si aún vacío, como última opción tratamos todo como alumnos (no ideal)
            if (alumnos.value.length === 0 && profesores.value.length === 0) {
                // asumimos que la respuesta era { alumnos: [...], profesores: [...] } but parsing failed
                // dejar arrays vacíos para que no rompa la UI
                alumnos.value = [];
                profesores.value = [];
            }
        }
    } catch (err) {
        console.error('Error fetchUsers', err);
        errorMessage.value = 'Error al cargar alumnos y profesores: ' + (err.response?.data?.message || err.message);
        setTimeout(() => errorMessage.value = '', 5000);
    }
};

// ❌ fetchNextId: ELIMINADA. El ID ahora lo genera la BD.


// cuando seleccionas RUT alumno completa nombre (seguro y simple)
const onAlumnoSelect = () => {
    // Si tus IDs en Vue son números, asegúrate de comparar números:
    const sel = alumnos.value.find(a => a.id == form.alumno_id);
    form.alumno_nombre = sel ? sel.name : '';
};

// reset minimal del form (sin tocar semestre si quieres mantenerlo)
const resetForm = () => {
    form.modalidad = 'PrIng';
    form.alumno_id = '';
    form.alumno_nombre = '';
    form.profesor_dinf_id = '';
    form.profesor_tutor_id = '';
    form.comision_profesor_id = '';
    form.co_guia_id = '';
    form.semestre_inicio = '';
    form.titulo = '';
    form.descripcion_proyecto = '';
    form.empresa_nombre = '';
    form.supervisor_empresa = '';
    form.descripcion_practica = '';
    form.id_habilitacion = ''; // Limpiar el ID
};

// Build payload y enviar (mapeo explícito)
const submit = async () => {
    showSuccess.value = false;
    errorMessage.value = '';

    // Validaciones simples en frontend (puedes ampliar)
    if (!form.alumno_id) {
        errorMessage.value = 'Seleccione un alumno.';
        return;
    }
    if (!form.semestre_inicio) {
        errorMessage.value = 'Ingrese semestre de inicio.';
        return;
    }
    // si es proyecto, validar guía y comisión
    if (isProyecto.value && (!form.profesor_dinf_id || !form.comision_profesor_id || !form.titulo)) {
        errorMessage.value = 'Complete profesor guía, profesor comisión y título del proyecto.';
        return;
    }
    // si es práctica, validar tutor y empresa y supervisor
    if (isPractica.value && (!form.profesor_tutor_id || !form.empresa_nombre || !form.supervisor_empresa)) {
        errorMessage.value = 'Complete profesor tutor, nombre de empresa y supervisor.';
        return;
    }

    try {
        // parse semestre: esperamos formato "AAAA-S1" o "2025-S1"
        let yearPart = null;
        let semPart = null;
        if (form.semestre_inicio && form.semestre_inicio.includes('-')) {
            [yearPart, semPart] = form.semestre_inicio.split('-');
        } else {
            // si usuario ingresó "2025S1" u otro, mandamos raw y backend debería validar
            yearPart = null;
            semPart = form.semestre_inicio;
        }

        const tipo = form.modalidad === 'PrIng' ? 'ingenieria'
                        : form.modalidad === 'PrInv' ? 'investigacion'
                        : 'practica';

        const payload = {
            tipo,
            // ✅ CORRECCIÓN: Usar parseInt() para enviar como Number (integer) al backend
            rut_alumno: form.alumno_id ? parseInt(form.alumno_id) : null,
            alumno_nombre: form.alumno_nombre,
            semestre_inicio_año: yearPart ? parseInt(yearPart) : null,
            semestre_inicio: semPart || null,
            descripcion: isProyecto.value ? form.descripcion_proyecto : form.descripcion_practica,

            // ✅ CORRECCIÓN: Usar parseInt() para IDs de profesores
            titulo: form.titulo || null,
            profesor_guia: isProyecto.value && form.profesor_dinf_id ? parseInt(form.profesor_dinf_id) : null,
            profesor_comision: isProyecto.value && form.comision_profesor_id ? parseInt(form.comision_profesor_id) : null,
            profesor_coguia: isProyecto.value && form.co_guia_id ? parseInt(form.co_guia_id) : null,

            // ✅ CORRECCIÓN: Usar parseInt() para profesor tutor
            profesor_tutor: isPractica.value && form.profesor_tutor_id ? parseInt(form.profesor_tutor_id) : null,
            nombre_empresa: isPractica.value ? form.empresa_nombre : null,
            nombre_supervisor: isPractica.value ? form.supervisor_empresa : null,
        };

        const res = await axios.post('/api/habilitaciones', payload);

        // Mensaje de éxito mejorado usando el ID devuelto
        const newId = res.data.id_habilitacion; 
        errorMessage.value = `✅ ¡Habilitación ${newId} creada exitosamente!`; 
        showSuccess.value = true;
        
        // Actualizar el ID en el formulario (solo para visualización)
        form.id_habilitacion = newId;

        // Limpiar el mensaje después de 4 segundos
        setTimeout(() => { 
            showSuccess.value = false; 
            errorMessage.value = ''; 
            resetForm(); // Resetear el formulario después de mostrar el éxito
        }, 4000); 

    } catch (err) {
        console.error('Error submit', err);
        
        // --- INICIO: Bloque catch ACTUALIZADO ---
        const responseData = err.response?.data;
        const validationErrors = responseData?.errors;
        const backendMessage = responseData?.message || 'Ocurrió un error desconocido al guardar.';
        const backendDetail = responseData?.error_detail; // <-- Captura el detalle del error de DB

        if (validationErrors) {
            // Error de Validación (ej: 422)
            let s = 'Revise los campos:\n';
            for (const k in validationErrors) {
                s += `- ${validationErrors[k][0]}\n`;
            }
            errorMessage.value = s;
        } else if (backendDetail) {
            // Error de Base de Datos/Servidor con detalle (ej: 500)
            errorMessage.value = `${backendMessage}\n\nDetalle de la BD: ${backendDetail}`;
        } else {
            // Otros errores del servidor
            errorMessage.value = backendMessage;
        }
        // --- FIN: Bloque catch ACTUALIZADO ---

        window.scrollTo(0,0);
    }
};

// onMounted: cargar datos y autosemestre por defecto 
onMounted(() => {
    fetchUsers();
    // ❌ fetchNextId() ELIMINADO de onMounted
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth(); // 0..11
    const semester = currentMonth >= 7 ? 'S2' : 'S1';
    form.semestre_inicio = `${currentYear}-${semester}`;
});
</script>

<template>
    <Head title="Ingreso Habilitación" />

    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-xl">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Ingreso de Nueva Habilitación Profesional</h1>

        <div v-if="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            ¡Habilitación creada exitosamente!
        </div>
        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 whitespace-pre-line" role="alert">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-full border-b pb-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Datos Generales</h2>
                </div>
                <div>
                    <label for="id_habilitacion" class="block text-sm font-medium text-gray-700">
                    ID Habilitación
                    </label>
                    <input
                        type="text"
                        id="id_habilitacion"
                        v-model="form.id_habilitacion"
                        readonly
                        class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2"
                        placeholder="Se generará al guardar"
                    />
                </div>

                <div>
                    <label for="modalidad" class="block text-sm font-medium text-gray-700">Tipo de Habilitación</label>
                    <select id="modalidad" v-model="form.modalidad" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option disabled value="">Seleccione una opción</option>
                        <option value="PrIng">Proyecto de Ingeniería</option>
                        <option value="PrInv">Proyecto de Investigación</option>
                        <option value="PrTut">Práctica Tutelada</option>
                    </select>
                </div>

                <div>
                    <label for="semestre" class="block text-sm font-medium text-gray-700">Semestre de Inicio</label>
                    <input type="text" id="semestre" v-model="form.semestre_inicio" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Ej: 2025-S1">
                </div>

                             
                <div>
                    <label for="alumno_rut" class="block text-sm font-medium text-gray-700">RUT Alumno(a)</label>
                    <select id="alumno_rut" v-model="form.alumno_id" @change="onAlumnoSelect" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option disabled value="">Seleccione un RUT</option>
                        <option v-for="a in alumnos" :key="a.id" :value="a.id.toString()">{{ a.id }}</option>
                    </select>
                </div>

                <div>
                    <label for="alumno_nombre" class="block text-sm font-medium text-gray-700">Nombre Alumno(a)</label>
                    <input type="text" id="alumno_nombre" v-model="form.alumno_nombre" readonly class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2">
                </div>
            </div>

            <div v-if="isProyecto" class="mt-8 border p-4 rounded-lg bg-indigo-50">
                <h2 class="text-xl font-semibold mb-4 text-indigo-800">Datos del Proyecto (Ingeniería / Investigación)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profesor_guia" class="block text-sm font-medium text-gray-700">Profesor Guía DINF</label>
                        <select id="profesor_guia" v-model="form.profesor_dinf_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesores" :key="p.id" :value="p.id.toString()">{{ p.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="comision_profesor" class="block text-sm font-medium text-gray-700">Profesor Comisión</label>
                        <select id="comision_profesor" v-model="form.comision_profesor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesores" :key="p.id" :value="p.id.toString()">{{ p.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="co_guia" class="block text-sm font-medium text-gray-700">Co-Guía (Opcional)</label>
                        <select id="co_guia" v-model="form.co_guia_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Ninguno</option>
                            <option v-for="p in profesores" :key="p.id" :value="p.id.toString()">{{ p.name }}</option>
                        </select>
                    </div>

                    <div class="col-span-full">
                        <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Proyecto</label>
                        <input type="text" id="titulo" v-model="form.titulo" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_proyecto" class="block text-sm font-medium text-gray-700">Descripción/Objetivo del Proyecto</label>
                        <textarea id="descripcion_proyecto" v-model="form.descripcion_proyecto" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>
            </div>

            <div v-if="isPractica" class="mt-8 border p-4 rounded-lg bg-yellow-50">
                <h2 class="text-xl font-semibold mb-4 text-yellow-800">Datos de la Práctica Tutelada</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profesor_tutor" class="block text-sm font-medium text-gray-700">Profesor Tutor DINF</label>
                        <select id="profesor_tutor" v-model="form.profesor_tutor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesores" :key="p.id" :value="p.id.toString()">{{ p.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="empresa_nombre" class="block text-sm font-medium text-gray-700">Nombre de la Empresa</label>
                        <input type="text" id="empresa_nombre" v-model="form.empresa_nombre" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label for="supervisor_empresa" class="block text-sm font-medium text-gray-700">Nombre Supervisor Empresa</label>
                        <input type="text" id="supervisor_empresa" v-model="form.supervisor_empresa" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_practica" class="block text-sm font-medium text-gray-700">Descripción de la Práctica</label>
                        <textarea id="descripcion_practica" v-model="form.descripcion_practica" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition duration-150">
                    Guardar Habilitación
                </button>
            </div>
        </form>
    </div>
</template>