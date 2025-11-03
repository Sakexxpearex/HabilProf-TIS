<script setup>
import { reactive, ref, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// --- ESTADOS REACTIVOS ---
const form = reactive({
    id_habilitacion: '',
    accion: 'Modificar', // valor inicial
    tipo_habilitacion: '',
    rut_profesor: '',
    nombre_profesor: '',
    nombre_empresa: '',
    nombre_supervisor: '',
    titulo: '',
    descripcion: '',
    nota_final: '',
    fecha_nota: ''
});

const habilitacionEncontrada = ref(false);
const showSuccess = ref(false);
const errorMessage = ref('');
const confirmDelete = ref(false); // para mostrar confirmación de eliminación

// --- FUNCIONES ---

// Buscar habilitación por ID
const buscarHabilitacion = async () => {
    errorMessage.value = '';
    showSuccess.value = false;
    habilitacionEncontrada.value = false;

    if (!form.id_habilitacion) {
        errorMessage.value = 'Debe ingresar un ID de habilitación válido.';
        return;
    }

    try {
        const response = await axios.get(`/api/habilitaciones/${form.id_habilitacion}`);
        const data = response.data;

        // Cargar los datos obtenidos al formulario
        Object.assign(form, data);
        habilitacionEncontrada.value = true;
    } catch (error) {
        errorMessage.value = 'Habilitación no registrada.';
    }
};

// Guardar cambios (MODIFICAR)
const modificarHabilitacion = async () => {
    errorMessage.value = '';
    showSuccess.value = false;

    try {
        const response = await axios.put(`/api/habilitaciones/${form.id_habilitacion}`, form);
        showSuccess.value = true;
        habilitacionEncontrada.value = false;

        // Limpiar formulario
        for (const key in form) {
            form[key] = '';
        }

        setTimeout(() => (showSuccess.value = false), 4000);
    } catch (error) {
        errorMessage.value = 'Error de modificación. Datos no válidos.';
    }
};

// Confirmación de eliminación
const confirmarEliminacion = () => {
    confirmDelete.value = true;
};

// Eliminar habilitación
const eliminarHabilitacion = async () => {
    errorMessage.value = '';
    showSuccess.value = false;
    confirmDelete.value = false;

    try {
        await axios.delete(`/api/habilitaciones/${form.id_habilitacion}`);
        showSuccess.value = true;
        habilitacionEncontrada.value = false;
        for (const key in form) form[key] = '';
    } catch (error) {
        errorMessage.value = 'Error al eliminar la habilitación.';
    }
};
</script>

<template>
    <Head title="Modificar o Eliminar Habilitación" />

    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-xl">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Modificar o Eliminar Habilitación</h1>

        <div v-if="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ form.accion === 'Modificar' ? 'Habilitación modificada correctamente.' : 'La habilitación ha sido eliminada correctamente.' }}
        </div>

        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 whitespace-pre-line" role="alert">
            {{ errorMessage }}
        </div>

        <div class="mb-6 flex gap-4 items-end">
            <div class="flex-1">
                <label for="id" class="block text-sm font-medium text-gray-700">ID Habilitación</label>
                <input
                    id="id"
                    v-model="form.id_habilitacion"
                    type="number"
                    min="1"
                    max="999999"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    placeholder="Ej: 123456"
                />
            </div>
            <button
                @click="buscarHabilitacion"
                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition duration-150"
            >
                Buscar
            </button>
        </div>

        <div v-if="habilitacionEncontrada" class="border-t pt-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Acción</label>
                <select v-model="form.accion" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="Modificar">Modificar</option>
                    <option value="Eliminar">Eliminar</option>
                </select>
            </div>

            <div v-if="form.accion === 'Modificar'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo Habilitación</label>
                        <select v-model="form.tipo_habilitacion" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="PrIng">Proyecto Ingeniería</option>
                            <option value="PrInv">Proyecto Investigación</option>
                            <option value="PrTut">Práctica Tutelada</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">RUT Profesor</label>
                        <input v-model="form.rut_profesor" type="number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre Profesor</label>
                        <input v-model="form.nombre_profesor" type="text" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre Empresa</label>
                        <input v-model="form.nombre_empresa" type="text" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supervisor Empresa</label>
                        <input v-model="form.nombre_supervisor" type="text" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input v-model="form.titulo" type="text" maxlength="25" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" />
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" maxlength="350" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nota Final</label>
                        <input v-model="form.nota_final" type="text" readonly class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Nota</label>
                        <input v-model="form.fecha_nota" type="date" readonly class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="modificarHabilitacion" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 transition duration-150">
                        Guardar Cambios
                    </button>
                </div>
            </div>

            <div v-else class="mt-6">
                <div class="bg-yellow-50 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
                    ¿Desea eliminar la habilitación del alumno ID {{ form.id_habilitacion }}?
                </div>

                <div class="flex gap-4 justify-end">
                    <button @click="eliminarHabilitacion" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700 transition duration-150">
                        Confirmar Eliminación
                    </button>
                    <button @click="confirmDelete = false" class="px-6 py-2 bg-gray-400 text-white font-semibold rounded-md shadow-md hover:bg-gray-500 transition duration-150">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
