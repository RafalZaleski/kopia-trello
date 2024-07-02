<template>
    <div class="position-fixed top-0 right-0 bg-surface-light h-100 w-100 opacity-80" @click="close()"></div>
    <v-sheet class="sheet overflow-y-auto position-fixed pa-6">
      <div>Dodaj przyjaciela</div>
      <v-form @submit.prevent="submit()" ref="formToSend">
        <v-text-field
          v-model="form.email"
          label="Email"
          :rules="rules"
          :autofocus="true"
        ></v-text-field>
        <div class="d-flex flex-row-reverse justify-space-between ">
          <v-btn
            :loading="store.getters.isLoading"
            type="submit"
            color="green"
          >Zapisz</v-btn>
          <v-btn
            @click="close()"
            :loading="store.getters.isLoading"
            type="button"
            color="red"
          >Anuluj</v-btn>
        </div>
      </v-form>
    </v-sheet>
</template>
  
<script setup>
  
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Friends } from '../helpers/apiFriends.js';
      
    const store = useStore();
    const apiFriends = new Friends(store);
  
    const form = ref({ email: '' });
    const formToSend = ref(null);
   
  
    const rules = [
      value => {
        if (value?.length > 0) return true
  
        return 'Wprowadź wartość (min 1 znak)'
      },
    ];
  
    async function submit () {
      const { valid } = await formToSend.value.validate();
      let ans = false;
      
      if (valid) {
        ans = await apiFriends.invite(form);
        
        if (ans) {
          close();
        }
      }
    }

    function close() {
      document.documentElement.style.overflow = 'auto';
      store.state.router.push({ name: 'friends' });
    }

    document.documentElement.style.overflow = 'hidden';

</script>