<template>
  <div>
    <h1>Habilitaciones por Semestre</h1>

    <table>
      <thead>
        <tr>
          <th>RUT Alumno</th>
          <th>Nombre Alumno</th>
          <th>Tipo Habilitación</th>
          <th>RUT Profesor</th>
          <th>Nombre Profesor</th>
          <th>Semestre Inicio</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(h, i) in habilitaciones" :key="i">
          <td>{{ h.rut_alumno }}</td>
          <td>{{ h.nombre_alumno }}</td>
          <td>{{ h.tipo_habilitacion }}</td>
          <td>{{ h.rut_profesor }}</td>
          <td>{{ h.nombre_profesor }}</td>
          <td>{{ h.semestre_inicio }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { habilitaciones } = usePage().props
</script>
