<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const mensaje = ref('');
const form = useForm({
  usuario: '', 
  password: '',
  remember: false,
});

// Validación (R8.1 y R8.2)
const validarCampo = (valor) => /^[A-Za-z0-9]{1,20}$/.test(valor);

const submit = () => {
  mensaje.value = '';

  const usuarioTrim = form.usuario.trim();
  const passwordTrim = form.password.trim();

  if (!usuarioTrim || !passwordTrim) {
    mensaje.value = 'Debe completar ambos campos';
    return;
  }

  if (!validarCampo(usuarioTrim) || !validarCampo(passwordTrim)) {
    mensaje.value =
      'Solo se permiten letras y números, sin espacios ni caracteres especiales (máx. 20)';
    return;
  }

  form.post(route('login'), {
    data: {
      usuario: usuarioTrim,
      password: passwordTrim,
      remember: form.remember,
    },
    onSuccess: () => (mensaje.value = 'Ingresado exitosamente'),
    onError: () => (mensaje.value = 'Usuario o contraseña incorrectos'),
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Login" />

    <!-- Fondo rojo burdeos y centrado -->
    <div class="min-h-screen flex items-center justify-center bg-[#800000]">
      <!-- Contenedor del formulario -->
      <div class="w-full max-w-md bg-white/95 p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
          Iniciar Sesión
        </h1>

        <form @submit.prevent="submit">
          <div>
            <InputLabel for="usuario" value="Usuario" />
            <TextInput
              id="usuario"
              type="text"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-700 focus:border-red-700"
              v-model.trim="form.usuario"
              required
            />
            <InputError class="mt-2" :message="form.errors.usuario" />
          </div>

          <div class="mt-4">
            <InputLabel for="password" value="Contraseña" />
            <TextInput
              id="password"
              type="password"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-700 focus:border-red-700"
              v-model="form.password"
              required
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="mt-4 flex items-center">
            <Checkbox name="remember" v-model:checked="form.remember" />
            <span class="ms-2 text-sm text-gray-600">Recordarme</span>
          </div>

          <PrimaryButton
            class="mt-6 w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-2 px-4 rounded shadow-sm transition"
            :disabled="form.processing"
          >
            Ingresar
          </PrimaryButton>

          <p v-if="mensaje" class="mt-4 text-sm text-center text-red-600">
            {{ mensaje }}
          </p>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>
