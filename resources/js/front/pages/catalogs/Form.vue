<template>
  <div class="cover" @click="close()"></div>
  <v-sheet class="sheet">
    <div v-if="store.state.route.params.catalogId">Edytuj katalog</div>
    <div v-else>Dodaj katalog</div>
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
    </v-form>
  </v-sheet>
</template>
  
<script setup>
  
  import { ref } from 'vue';
  import { useStore } from 'vuex';
  import { Catalogs } from '../../helpers/api/apiCatalogs';
    
  const store = useStore();
  const apiCatalogs = new Catalogs(store);

  const form = ref({ board_id: null, name: '', description: null, position: 0 });
  const formToSend = ref(null);

  const rules = [
    value => {
      if (value?.length > 0) return true
      
      return 'Wprowadź wartość (min 1 znak)'
    },
  ];

  async function submit () {
    form.value.board_id = store.state.route.params.boardId;
    const { valid } = await formToSend.value.validate();
    let ans = false;
    
    if (valid) {
      if (store.state.route.params.catalogId > 0) {
        ans = await apiCatalogs.edit(store.state.route.params.catalogId, form);
      } else {
        ans = await apiCatalogs.add(form);
      }
    }
    
    if (ans) {
      close();
    }
  }

  function close() {
    store.state.router.push({ name: 'catalogs', params: { boardId: store.state.route.params.boardId } });
  }

  if (store.state.route.params.catalogId > 0) {
    apiCatalogs.get(store.state.route.params.catalogId, form);
  }

</script>

<style>
  .cover {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(200,200,200,0.7);
  }

  .sheet {
    width: 300px;
    padding: 30px;
    position: fixed;
    left: calc(50% - 180px);
    top: 100px;
    overflow-y: scroll;
    max-height: 70%;
  }
</style>