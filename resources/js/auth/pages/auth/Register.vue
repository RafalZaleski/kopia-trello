<template>
    <v-sheet max-width="400" class="mx-auto pa-7">
      <div>Rejestracja</div>
      <v-form @submit.prevent="submit">
        <v-text-field
          v-model="form.name"
          label="Nick"
          :rules="rules"
          :autofocus="true"
        ></v-text-field>
        <v-text-field
          v-model="form.email"
          type="email"
          label="Email"
          :rules="rules"
        ></v-text-field>
        <v-text-field
          v-model="form.password"
          :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :type="showPassword ? 'text' : 'password'"
          label="Hasło"
          :rules="rules"
          @click:append="showPassword = !showPassword"
        ></v-text-field>
        <v-btn
          :loading="store.getters.isLoading"
          type="submit"
          color="green"
          class="w-100 mb-5"
        >Zarejestruj</v-btn>
      </v-form>
    </v-sheet>
      <router-link :to="{name: 'login'}"><v-btn color="green" style="position: fixed; bottom: 30px; right: 30px;">Zaloguj się</v-btn></router-link>
  </template>
  
<script setup>

  import { ref } from 'vue';
  import { useStore } from 'vuex';
  import { Auth } from '../../helpers/apiAuth.js';
  
  const store = useStore();
  const apiAuth = new Auth(store);
  
  const showPassword = ref(false);
  const form = ref({name: '', email: '', password: ''});
  
  const rules = [
    value => {
      if (value) return true;
  
      return 'Wprowadź wartość';
    },
  ];
  
  async function submit () {
    await apiAuth.register(form);
  }
  
</script>