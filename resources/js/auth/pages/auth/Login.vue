<template>
    <v-sheet max-width="400" class="mx-auto pa-7">
      <div>Logowanie</div>
      <v-form @submit.prevent="submit">
        <v-text-field
          v-model="form.email"
          type="email"
          label="Email"
          :rules="rules"
          :autofocus="true"
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
        >Zaloguj</v-btn>
      </v-form>
    </v-sheet>
    <router-link :to="{name: 'register'}"><v-btn color="green" style="position: fixed; bottom: 30px; right: 30px;">Zarejestruj się</v-btn></router-link>
  </template>
  
<script setup>
  
  import { ref } from 'vue';
  import { useStore } from 'vuex';
  import { Auth } from '../../helpers/apiAuth.js';
  
  const store = useStore();
  const apiAuth = new Auth(store);
  
  const showPassword = ref(false);
  const form = ref({email: '', password: ''});
  
  const rules = [
    value => {
      if (value) return true;
  
      return 'Wprowadź wartość';
    },
  ];
  
  async function submit () {
    await apiAuth.login(form);
  }
  
</script>