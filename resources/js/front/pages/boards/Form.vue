<template>
  <div v-if="isOpen">
    <div class="cover" @click="emit('modal-close')"></div>
    <v-sheet class="sheet">
      <div v-if="props.boardId">Edytuj tablice</div>
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
  </div>
</template>
  
<script setup>
  
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Boards } from '../../helpers/api/apiBoards';
      
    const store = useStore();
    const apiBoards = new Boards(store);

    const props = defineProps({
        isOpen: Boolean,
        boardId: Number
    });
    const emit = defineEmits(["modal-close"]);
  
    const form = ref({ name: '', description: null });
   
  
    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];
  
    async function submit () {
      if (props.boardId) {
          await apiBoards.edit(props.boardId, form);
      } else {
          await apiBoards.add(form);
      }
      emit('modal-close');
    }

    if (props.boardId) {
      apiBoards.get(props.boardId, form);
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