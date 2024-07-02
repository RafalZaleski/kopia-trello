<template>
    <div class="position-fixed top-0 right-0 bg-surface-light h-100 w-100 opacity-80" @click="close()"></div>
    <v-sheet class="sheet overflow-y-auto position-fixed pa-6">
      <div v-if="store.state.route.params.boardId > 0">Edytuj tablice</div>
      <div v-else>Dodaj tablice</div>
      <v-form @submit.prevent="submit()" ref="formToSend">
        <v-text-field
          v-model="form.name"
          label="Nazwa"
          :rules="rules"
          :autofocus="true"
        ></v-text-field>
        <v-textarea
          v-model="form.description"
          label="Opis"
        ></v-textarea>
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
    import { Boards } from '../../helpers/api/apiBoards';
      
    const store = useStore();
    const apiBoards = new Boards(store);
  
    const form = ref({ name: '', description: null });
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
        if (store.state.route.params.boardId > 0) {
            ans = await apiBoards.edit(store.state.route.params.boardId, form);
        } else {
            ans = await apiBoards.add(form);
        }
        
        if (ans) {
          close();
        }
      }
    }

    function close() {
      document.documentElement.style.overflow = 'auto';
      store.state.router.push({ name: 'boards' });
    }

    document.documentElement.style.overflow = 'hidden';

    if (store.state.route.params.boardId > 0) {
      apiBoards.get(store.state.route.params.boardId, form);
    }

</script>