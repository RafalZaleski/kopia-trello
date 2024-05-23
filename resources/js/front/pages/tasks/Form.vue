<template>
  <div v-if="isOpen">
    <div class="cover" @click="emit('modal-close')"></div>
    <v-sheet class="sheet">
      <div v-if="props.taskId">Edytuj zadanie</div>
      <div v-else>Dodaj zadanie</div>
      <v-form @submit.prevent="submit">
        <v-text-field
          v-model="form.name"
          label="nazwa"
          :rules="rules"
          :autofocus="true"
        ></v-text-field>
        <v-textarea
          v-model="form.description"
          label="opis"
        ></v-textarea>
        <v-text-field
          v-model="form.date"
          label="termin"
        ></v-text-field>
        <!-- <v-date-picker></v-date-picker> -->
        <v-text-field
          v-model="form.place"
          label="miejsce"
        ></v-text-field>
        <v-text-field
          v-model="form.position"
          label="pozycja"
          :rules="rules"
        ></v-text-field>
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
    import { Tasks } from '../../helpers/api/apiTasks';
    
    const store = useStore();
    const apiTasks = new Tasks(store);

    const props = defineProps({
        isOpen: Boolean,
        taskId: Number,
        catalogId: Number
    });
    const emit = defineEmits(["modal-close"]);
  
    const form = ref({ catalog_id: props.catalogId, name: '', date: null, place: null, position: 0 });
  
    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];
  
    async function submit () {
      if (props.taskId) {
          await apiTasks.edit(props.taskId, form);
      } else {
          await apiTasks.add(form);
      }
      emit('modal-close');
    }

    if (props.taskId) {
      apiTasks.get(props.taskId, form);
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
  }
</style>