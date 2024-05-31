<template>
    <div class="cover" @click="close()"></div>
    <v-sheet class="sheet">
      <div v-if="store.state.route.params.boardId > 0">Edytuj tablice</div>
      <div v-else>Dodaj tablice</div>
      <v-form @submit.prevent="submit">
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
   
  
    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];
  
    async function submit () {
      if (store.state.route.params.boardId > 0) {
          await apiBoards.edit(store.state.route.params.boardId, form);
      } else {
          await apiBoards.add(form);
      }
      
      close();
    }

    function close() {
      store.state.router.push({ name: 'boards' });
    }

    if (store.state.route.params.boardId > 0) {
      apiBoards.get(store.state.route.params.boardId, form);
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