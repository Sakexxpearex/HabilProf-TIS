<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Listado por Profesor</h1>

        <div v-for="p in profesores" :key="p.id" class="mb-8">

            <h2 class="text-xl font-semibold mb-2">
                {{ p.name }}
            </h2>

            <table class="w-full border mb-3">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">Alumno</th>
                        <th class="border p-2">Título</th>
                        <th class="border p-2">Modalidad</th>
                        <th class="border p-2">Semestre</th>
                        <th class="border p-2">Tipo Supervisión</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Como Guía -->
                    <tr v-for="h in p.habilitaciones_como_guia" :key="h.id">
                        <td class="border p-2">{{ h.alumno?.name }}</td>
                        <td class="border p-2">{{ h.titulo }}</td>
                        <td class="border p-2">{{ h.modalidad }}</td>
                        <td class="border p-2">{{ h.semestre_inicio }}</td>
                        <td class="border p-2">Guía</td>
                    </tr>

                    <!-- Como Comisión -->
                    <tr v-for="h in p.habilitaciones_como_comision" :key="h.id">
                        <td class="border p-2">{{ h.alumno?.name }}</td>
                        <td class="border p-2">{{ h.titulo }}</td>
                        <td class="border p-2">{{ h.modalidad }}</td>
                        <td class="border p-2">{{ h.semestre_inicio }}</td>
                        <td class="border p-2">Comisión</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</template>

<script setup>
defineProps({
    profesores: Array
});
</script>
