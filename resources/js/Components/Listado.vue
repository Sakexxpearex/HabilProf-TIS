<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Necesario para el menú

// --- ESTADOS REACTIVOS ---
const habilitaciones = ref([]); // Almacena el array de registros
const isLoading = ref(true);    // Controla el estado de carga
const errorMessage = ref(null); // Almacena errores de API

// --- LÓGICA DE CARGA DE DATOS ---

// Función para obtener los datos desde Laravel
const fetchHabilitaciones = async () => {
    isLoading.value = true;
    errorMessage.value = null;
    try {
        // [AXIOS.GET] Llama al endpoint de la API.
        // El backend (HabilitacionController@index) debe ordenar y traer las relaciones.
        const response = await axios.get('/api/habilitaciones'); 
        
        habilitaciones.value = Array.isArray(response.data) ? response.data : [];
        
    } catch (error) {
        console.error("Error al cargar las habilitaciones:", error);
        errorMessage.value = 'No se pudo cargar el listado. Error: ' + (error.response?.statusText || 'Desconocido');
    } finally {
        isLoading.value = false;
    }
};

// Se ejecuta automáticamente al cargar la página
onMounted(() => {
    fetchHabilitaciones();
});

// Función de utilidad para mostrar el campo correcto (título o empresa)
const getTituloPrincipal = (item) => {
    if (item.modalidad === 'PrTut') {
        return item.empresa_nombre;
    }
    return item.titulo;
};

// Función para obtener el profesor secundario (co-guía o supervisor)
const getProfesorSecundario = (item) => {
    if (item.modalidad === 'PrTut') {
        return `Supervisor: ${item.supervisor_empresa}`;
    }
    if (item.co_guia_nombre) {
        return `Co-Guía: ${item.co_guia_nombre}`;
    }
    return 'N/A';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Listado de Habilitaciones" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Listado Histórico</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <h1 class="text-2xl font-bold mb-6">Listado General por Semestre</h1>

                    <div v-if="isLoading" class="p-4 text-center text-blue-600">
                        Cargando registros...
                    </div>
                    <div v-else-if="errorMessage" class="p-4 bg-red-100 text-red-700 rounded-md">
                        {{ errorMessage }}
                    </div>
                    <div v-else-if="habilitaciones.length === 0" class="p-4 text-center text-gray-500 border rounded-md">
                        No se encontraron habilitaciones registradas.
                    </div>
                    
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semestre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modalidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno(a)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor Guía</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título / Empresa</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Info Secundaria</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in habilitaciones" :key="item.id">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.semestre_inicio }}</td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-indigo-100 text-indigo-800': item.modalidad === 'PrIng',
                                            'bg-purple-100 text-purple-800': item.modalidad === 'PrInv',
                                            'bg-yellow-100 text-yellow-800': item.modalidad === 'PrTut',
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ item.modalidad }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.alumno ? item.alumno.name : 'ID: ' + item.alumno_id }}
                                    </td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.profesor_dinf ? item.profesor_dinf.name : 'N/A' }}
                                    </td>
                                    
                                    <td class="px-4 py-4 max-w-xs truncate text-sm font-semibold text-gray-800">
                                        {{ getTituloPrincipal(item) }}
                                    </td>
                                    
                                    <td class="px-4 py-4 max-w-xs text-sm text-gray-500">
                                        {{ getProfesorSecundario(item) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>