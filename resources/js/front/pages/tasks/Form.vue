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
        <div v-for="comment in form.comments">
          {{ comment.description }}
        </div>
        <v-btn
          @click="addComment()"
          :loading="store.getters.isLoading"
          type="button"
          color="green"
        >Dodaj komentarz</v-btn>
        <div v-for="attachment in form.attachments">
          <a target="_blank" :href="attachment.original_url">Link</a>
        </div>
        <v-file-input v-model="newAttachment" @change="addAttachment()" label="Dodaj załącznik"></v-file-input>
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
  
    const form = ref({ catalog_id: props.catalogId, name: '', date: null, place: null, position: 0, comments: [], attachments: [] });
    const newAttachment = ref(null);

    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];

    function addComment() {
      console.log('otwieramy form z commentarzem');
    }

    function addAttachment() {
      apiTasks.addAttachment(props.taskId, newAttachment.value).then(
        () => {
          apiTasks.get(props.taskId, form);
          newAttachment.value = null;
        }
      );
    }
  
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
    overflow-y: scroll;
    max-height: 70%;
  }
</style>