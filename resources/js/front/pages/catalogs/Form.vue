<template>
  <div v-if="isOpen">
    <div class="cover" @click="emit('modal-close')"></div>
    <v-sheet class="sheet">
      <div v-if="props.catalogId">Edytuj katalog</div>
      <div v-else>Dodaj katalog</div>
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
    import { Catalogs } from '../../helpers/api/apiCatalogs';
      
    const store = useStore();
    const apiCatalogs = new Catalogs(store);

    const props = defineProps({
        isOpen: Boolean,
        catalogId: Number
    });
    const emit = defineEmits(["modal-close"]);
  
    const form = ref({ board_id: null, name: '', description: null, position: 0 });
   
  
    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];
  
    async function submit () {
      form.value.board_id = store.state.route.params.id;

      if (props.catalogId) {
          await apiCatalogs.edit(props.catalogId, form);
      } else {
          await apiCatalogs.add(form);
      }
      emit('modal-close');
    }

    if (props.catalogId) {
      apiCatalogs.get(props.catalogId, form);
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