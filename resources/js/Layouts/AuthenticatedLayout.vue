<script setup>
import { Link, router } from '@inertiajs/vue3'

const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">

    <!-- BARRA SUPERIOR -->
    <nav class="bg-white border-b shadow p-4 flex justify-between items-center">
      

      <button 
        @click="logout"
        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
      >
        Logout
      </button>
    </nav>

    <!-- CONTENIDO -->
    <main class="p-6">
      <slot />
    </main>

  </div>
</template>
