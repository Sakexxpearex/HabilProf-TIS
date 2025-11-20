<template>
  <div>
    <h1>Habilitaciones por Semestre</h1>
    <table>
      <thead>
        <tr>
          <th>Alumno</th>
          <th>Semestre</th>
          <th>Descripción</th>
          <th>Modalidad</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="h in habilitaciones" :key="h.id_habilitacion">
            <td>{{ h.alumno?.nombre_alumno ?? 'N/A' }}</td>
            <td>{{ h.semestre_inicio_anho }} - {{ h.semestre_inicio }}</td>
            <td>{{ h.descripcion }}</td>
            <td>{{ h.tipo_habilitacion }}</td>
        </tr>

      </tbody>
    </table>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { habilitaciones } = usePage().props
</script>
