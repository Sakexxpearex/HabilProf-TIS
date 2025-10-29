<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// --- ESTADOS REACTIVOS ---
const form = reactive({
    modalidad: 'PrIng', // Valor inicial por defecto
    alumno_id: '',
    profesor_dinf_id: '',
    comision_profesor_id: '',
    semestre_inicio: '',
    
    // Campos específicos para PrIng/PrInv
    titulo: '',
    descripcion_proyecto: '',
    co_guia_nombre: '',

    // Campos específicos para PrTut
    empresa_nombre: '',
    supervisor_empresa: '',
    descripcion_practica: '',
});

// Mensajes de estado (Feedback al usuario)
const showSuccess = ref(false);
const errorMessage = ref('');
const users = ref([]); // Lista para almacenar los usuarios cargados desde la API

// --- LÓGICA DE CARGA DE DATOS ---

// 1. Cargar Usuarios
const fetchUsers = async () => {
    try {
        // Asumiendo que tienes una ruta en Laravel definida como /api/users/list
        const response = await axios.get('/api/users/list'); 
        users.value = response.data;
    } catch (error) {
        errorMessage.value = 'Error al cargar usuarios: ' + (error.response?.data?.message || 'Error de conexión.');
        setTimeout(() => errorMessage.value = '', 5000);
    }
};

// Se ejecuta cuando el componente está visible
onMounted(() => {
    fetchUsers();
    // Pre-seleccionar el semestre actual (ejemplo)
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    // Asume 1er semestre (S1) de marzo a julio, 2do (S2) de agosto a diciembre
    const semester = currentMonth >= 7 ? 'S2' : 'S1'; 
    form.semestre_inicio = `${currentYear}-${semester}`;
});

// --- LÓGICA DEL FORMULARIO ---

// Función para resetear el formulario a sus valores iniciales
const resetForm = () => {
    form.modalidad = 'PrIng';
    form.alumno_id = '';
    form.profesor_dinf_id = '';
    form.comision_profesor_id = '';
    form.titulo = '';
    form.descripcion_proyecto = '';
    form.co_guia_nombre = '';
    form.empresa_nombre = '';
    form.supervisor_empresa = '';
    form.descripcion_practica = '';
    // Mantiene el semestre autocompletado si no quieres resetearlo
};

// 2. Función de Envío
const submit = async () => {
    // Limpiar mensajes anteriores
    showSuccess.value = false;
    errorMessage.value = '';

    try {
        // Envía el objeto 'form' (que es reactivo) al controlador de Laravel
        const response = await axios.post('/api/habilitaciones', form);
        
        // Manejo de éxito
        showSuccess.value = true;
        resetForm(); // Limpia los campos
        
        // Ocultar mensaje de éxito después de 3 segundos
        setTimeout(() => showSuccess.value = false, 3000);

    } catch (error) {
        // Manejo de errores de validación del backend (Laravel) o de red
        const validationErrors = error.response?.data?.errors;
        
        if (validationErrors) {
            // Si Laravel devolvió errores de validación (código 422)
            let errorString = 'Revise los campos: \n';
            for (const key in validationErrors) {
                errorString += `- ${validationErrors[key][0]}\n`;
            }
            errorMessage.value = errorString;
        } else {
            // Otros errores (servidor, red, etc.)
            errorMessage.value = 'Ocurrió un error al guardar: ' + (error.response?.data?.message || error.message);
        }
    }
};

// Propiedad computada para determinar qué campos mostrar (oculta/muestra el bloque)
const isProyecto = computed(() => ['PrIng', 'PrInv'].includes(form.modalidad));
const isPractica = computed(() => form.modalidad === 'PrTut');
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
                    <label for="modalidad" class="block text-sm font-medium text-gray-700">Modalidad</label>
                    <select id="modalidad" v-model="form.modalidad" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
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
                    <label for="alumno" class="block text-sm font-medium text-gray-700">Alumno(a)</label>
                    <select id="alumno" v-model="form.alumno_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option disabled value="">Seleccione un alumno</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                
                <div>
                    <label for="profesor_dinf" class="block text-sm font-medium text-gray-700">Profesor Guía/Tutor DINF</label>
                    <select id="profesor_dinf" v-model="form.profesor_dinf_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option disabled value="">Seleccione un profesor</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
            </div>

            <div v-if="isProyecto" class="mt-8 border p-4 rounded-lg bg-indigo-50">
                <h2 class="text-xl font-semibold mb-4 text-indigo-800">Datos del Proyecto (Ingeniería / Investigación)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-full">
                        <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Proyecto</label>
                        <input type="text" id="titulo" v-model="form.titulo" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label for="co_guia" class="block text-sm font-medium text-gray-700">Nombre Co-Guía (Opcional)</label>
                        <input type="text" id="co_guia" v-model="form.co_guia_nombre" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label for="comision_profesor" class="block text-sm font-medium text-gray-700">Profesor Comisión</label>
                        <select id="comision_profesor" v-model="form.comision_profesor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
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
                        <label for="empresa_nombre" class="block text-sm font-medium text-gray-700">Nombre de la Empresa</label>
                        <input type="text" id="empresa_nombre" v-model="form.empresa_nombre" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label for="supervisor_empresa" class="block text-sm font-medium text-gray-700">Nombre Supervisor Empresa</label>
                        <input type="text" id="supervisor_empresa" v-model="form.supervisor_empresa" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_practica" class="block text-sm font-medium text-gray-700">Descripción de las Tareas / Práctica</label>
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