<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// Estados Reactivos
const form = reactive({
    id_habilitacion: '', 
    modalidad: 'PrIng',
    // Alumnos
    rut_alumno: '',
    alumno_nombre: '',
    // Profesores
    profesor_dinf_id: '',      
    profesor_dinf_rut: '',
    profesor_dinf_nombre: '',  
    
    comision_profesor_id: '', 
    comision_profesor_rut: '',
    comision_profesor_nombre: '', 
    
    co_guia_id: '',            
    co_guia_rut: '',
    co_guia_nombre: '',       
    
    profesor_tutor_id: '',     
    profesor_tutor_rut: '',
    profesor_tutor_nombre: '', 

    semestre_inicio: '',      
    titulo: '',                
    descripcion_proyecto: '',  
    empresa_nombre: '',        
    supervisor_empresa: '',    
    descripcion_practica: '',  
});

const showSuccess = ref(false);
const errorMessage = ref('');
const alumnos = ref([]);
const profesores = ref([]);
const errorAlumno = ref('');

// Filtros
const profesoresDINF = computed(() =>
    profesores.value.filter(p => (p.departamento || '').toUpperCase().trim() === 'DINF')
);
const profesoresCoGuia = computed(() => profesores.value);
const isProyecto = computed(() => ['PrIng', 'PrInv'].includes(form.modalidad));
const isPractica = computed(() => form.modalidad === 'PrTut');

//Validaciones
const regexRut = /^[1-9][0-9]{0,7}$/; 
const regexNombreTexto = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,50}$/;
const regexTitulo = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,25}$/;
const regexSemestreFormato = /^\d{4}-(1|2)$/;
const validarDescripcionLargo = (d) => d && d.length <= 350;

//Carga de datos
const fetchUsers = async () => {
    try {
        const usersRes = await axios.get('/api/users/list');
        if (usersRes.data.alumnos && usersRes.data.profesores) {
            alumnos.value = usersRes.data.alumnos;
            profesores.value = usersRes.data.profesores;
        }
        const profRes = await axios.get('/api/profesores');
        profesores.value = profRes.data.todos || profesores.value;
    } catch (err) {
        console.error('Error fetchUsers', err);
    }
};

//Watchers
const asignarDatosProfesor = (idProfesor, campoRut, campoNombre) => {
    const prof = profesores.value.find(p => p.rut_profesor.toString() === idProfesor);
    form[campoRut] = prof ? prof.rut_profesor : '';
    form[campoNombre] = prof ? (prof.nombre || prof.name || '') : '';
};

watch(() => form.profesor_dinf_id, (n) => asignarDatosProfesor(n, 'profesor_dinf_rut', 'profesor_dinf_nombre'));
watch(() => form.comision_profesor_id, (n) => asignarDatosProfesor(n, 'comision_profesor_rut', 'comision_profesor_nombre'));
watch(() => form.co_guia_id, (n) => asignarDatosProfesor(n, 'co_guia_rut', 'co_guia_nombre'));
watch(() => form.profesor_tutor_id, (n) => asignarDatosProfesor(n, 'profesor_tutor_rut', 'profesor_tutor_nombre'));

//Función Buscar Alumno
const buscarAlumnoPorRut = async () => {
    errorAlumno.value = '';
    form.alumno_nombre = '';
    
    // Usamos form.rut_alumno
    if (!form.rut_alumno) return;

    // R1.1 Validación inmediata
    if (!regexRut.test(form.rut_alumno)) {
        errorAlumno.value = 'El RUT del alumno debe ser un entero positivo de hasta 8 dígitos.';
        return;
    }

    try {
        const res = await axios.get(`/api/alumnos/${form.rut_alumno}`);
        if (res.data && res.data.name) {
            form.alumno_nombre = res.data.name;
        } else {
            errorAlumno.value = 'No se encontró un alumno con ese RUT.';
        }
    } catch (err) {
        if (err.response && err.response.status === 409) { 
             errorAlumno.value = "El alumno ya posee una habilitación registrada y no puede tener más de una.";
        } else {
             errorAlumno.value = 'Error al consultar el alumno o no existe.';
        }
    }
};

const validarFormulario = () => {
    const errores = [];

    // R1.1 RUT_ALUMNO (Validamos form.rut_alumno)
    if (!form.rut_alumno || !regexRut.test(form.rut_alumno)) {
        errores.push("RUT Alumno (Debe ser numérico 1-99999999)");
    }
    // R1.2 NOMBRE_ALUMNO
    if (!form.alumno_nombre || !regexNombreTexto.test(form.alumno_nombre)) {
        errores.push("Nombre Alumno (Solo letras y espacios, máx 50 caracteres)");
    }

    // R2.12 SEMESTRE
    if (!form.semestre_inicio || !regexSemestreFormato.test(form.semestre_inicio)) {
        errores.push("Semestre Inicio (Formato AAAA-1 o AAAA-2)");
    } else {
        const [year, sem] = form.semestre_inicio.split('-');
        const yearNum = parseInt(year);
        if (yearNum < 2025 || yearNum > 2050) {
            errores.push("Semestre Inicio (Año debe estar entre 2025 y 2050)");
        }
    }

    if (isProyecto.value) {
        if (!form.profesor_dinf_id) errores.push("Profesor Guía es obligatorio");
        else if (!regexRut.test(form.profesor_dinf_rut)) errores.push("RUT Profesor Guía inválido");

        if (!form.comision_profesor_id) errores.push("Profesor Comisión es obligatorio");
        else if (!regexRut.test(form.comision_profesor_rut)) errores.push("RUT Profesor Comisión inválido");

        if (form.co_guia_id && !regexRut.test(form.co_guia_rut)) errores.push("RUT Co-Guía inválido");

        if (!form.titulo || !regexTitulo.test(form.titulo)) 
            errores.push("Título (Obligatorio, solo letras, máx 25 caracteres)");

        if (!form.descripcion_proyecto || !validarDescripcionLargo(form.descripcion_proyecto)) 
            errores.push("Descripción Proyecto (Obligatorio, máx 350 caracteres)");
    }

    if (isPractica.value) {
        if (!form.profesor_tutor_id) errores.push("Profesor Tutor es obligatorio");
        else if (!regexRut.test(form.profesor_tutor_rut)) errores.push("RUT Profesor Tutor inválido");

        if (!form.empresa_nombre || !regexNombreTexto.test(form.empresa_nombre)) 
            errores.push("Nombre Empresa (Solo letras, sin números, máx 50 caracteres)");

        if (!form.supervisor_empresa || !regexNombreTexto.test(form.supervisor_empresa)) 
            errores.push("Nombre Supervisor (Solo letras, sin números, máx 50 caracteres)");

        if (!form.descripcion_practica || !validarDescripcionLargo(form.descripcion_practica)) 
            errores.push("Descripción Práctica (Obligatorio, máx 350 caracteres)");
    }

    return errores;
};

// Submit
const submit = async () => {
    showSuccess.value = false;
    errorMessage.value = '';

    const erroresValidacion = validarFormulario();
    if (erroresValidacion.length > 0) {
        errorMessage.value = "Revise los campos:\n" + erroresValidacion.map(e => `- ${e}`).join("\n");
        window.scrollTo(0, 0);
        return;
    }

    try {
        const tipoHabilitacionMap = {
            PrIng: 'Proyecto de Ingeniería',
            PrInv: 'Proyecto de Investigación',
            PrTut: 'Práctica Tutelada'
        };
        
        let [yearPart, semPart] = form.semestre_inicio.split('-');

        const payload = {
            tipo_habilitacion: tipoHabilitacionMap[form.modalidad],
            rut_alumno: parseInt(form.rut_alumno), 
            alumno_nombre: form.alumno_nombre,
            semestre_inicio_año: parseInt(yearPart),
            semestre_inicio: semPart,
            
            descripcion: isProyecto.value ? form.descripcion_proyecto : form.descripcion_practica,
            titulo: isProyecto.value ? form.titulo : null,
            profesor_guia: isProyecto.value ? parseInt(form.profesor_dinf_id) : null,
            profesor_comision: isProyecto.value ? parseInt(form.comision_profesor_id) : null,
            profesor_coguia: (isProyecto.value && form.co_guia_id) ? parseInt(form.co_guia_id) : null,
            profesor_tutor: isPractica.value ? parseInt(form.profesor_tutor_id) : null,
            nombre_empresa: isPractica.value ? form.empresa_nombre : null,
            nombre_supervisor: isPractica.value ? form.supervisor_empresa : null,
        };

        const res = await axios.post('/api/habilitaciones', payload);

        const newId = res.data.id_habilitacion; 
        errorMessage.value = `Habilitación creada exitosamente. (ID: ${newId})`;
        showSuccess.value = true;
        
        setTimeout(() => {
            showSuccess.value = false;
            errorMessage.value = '';
            resetForm();
        }, 4000);

    } catch (err) {
        console.error('Error submit', err);
        const responseData = err.response?.data;
        
        if (responseData?.message === 'DUPLICATE_ALUMNO' || err.response?.status === 409) {
            errorMessage.value = "El alumno ya posee una habilitación registrada y no puede tener más de una.";
        } else if (responseData?.errors) {
            let s = 'Revise los campos (Backend):\n';
            for (const k in responseData.errors) s += `- ${responseData.errors[k][0]}\n`;
            errorMessage.value = s;
        } else {
            errorMessage.value = responseData?.message || 'Ocurrió un error al guardar.';
        }
        window.scrollTo(0, 0);
    }
};

const resetForm = () => {
    Object.assign(form, {
        modalidad: 'PrIng',
        rut_alumno: '',
        alumno_nombre: '',
        profesor_dinf_id: '', profesor_dinf_rut: '', profesor_dinf_nombre: '',
        profesor_tutor_id: '', profesor_tutor_rut: '', profesor_tutor_nombre: '',
        comision_profesor_id: '', comision_profesor_rut: '', comision_profesor_nombre: '',
        co_guia_id: '', co_guia_rut: '', co_guia_nombre: '',
        semestre_inicio: form.semestre_inicio,
        titulo: '',
        descripcion_proyecto: '',
        empresa_nombre: '',
        supervisor_empresa: '',
        descripcion_practica: '',
        id_habilitacion: '',
    });
};

onMounted(() => {
    fetchUsers();
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const semester = currentMonth >= 7 ? 2 : 1;
    const finalYear = Math.max(2025, currentYear);
    form.semestre_inicio = `${finalYear}-${semester}`;
});
</script>

<template>
    <AuthenticatedLayout>
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
                    <input
                        type="text"
                        id="alumno_rut"
                        v-model="form.rut_alumno"
                        @blur="buscarAlumnoPorRut"
                        required
                        placeholder="Ej: 12345678"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    />
                    <p v-if="errorAlumno" class="text-red-600 text-sm mt-1">{{ errorAlumno }}</p>
                </div>

                <div>
                    <label for="alumno_nombre" class="block text-sm font-medium text-gray-700">Nombre Alumno(a)</label>
                    <input type="text" id="alumno_nombre" v-model="form.alumno_nombre" readonly class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2">
                </div>
            </div>

            <!--Proyectos-->>
            <div v-if="isProyecto" class="mt-8 border p-4 rounded-lg bg-indigo-50">
                <h2 class="text-xl font-semibold mb-4 text-indigo-800">Datos del Proyecto (Ingeniería / Investigación)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profesor Guía -->
                    <div>
                        <label for="profesor_guia" class="block text-sm font-medium text-gray-700">Profesor Guía DINF</label>
                        <select id="profesor_guia" v-model="form.profesor_dinf_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.profesor_dinf_rut" readonly placeholder="RUT profesor guía"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <!-- Profesor Comisión -->
                    <div>
                        <label for="comision_profesor" class="block text-sm font-medium text-gray-700">Profesor Comisión</label>
                        <select id="comision_profesor" v-model="form.comision_profesor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.comision_profesor_rut" readonly placeholder="RUT profesor comisión"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <!-- Profesor Co-Guía -->
                    <div>
                        <label for="co_guia" class="block text-sm font-medium text-gray-700">Co-Guía (Opcional)</label>
                        <select id="co_guia" v-model="form.co_guia_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Ninguno</option>
                            <option v-for="p in profesoresCoGuia" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.co_guia_rut" readonly placeholder="RUT co-guía"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
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

            <!-- Sección Práctica -->
            <div v-if="isPractica" class="mt-8 border p-4 rounded-lg bg-yellow-50">
                <h2 class="text-xl font-semibold mb-4 text-yellow-800">Datos de la Práctica Tutelada</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profesor Tutor -->
                    <div>
                        <label for="profesor_tutor" class="block text-sm font-medium text-gray-700">Profesor Tutor DINF</label>
                        <select id="profesor_tutor" v-model="form.profesor_tutor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.profesor_tutor_rut" readonly placeholder="RUT profesor tutor"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
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
    </AuthenticatedLayout>
</template>
