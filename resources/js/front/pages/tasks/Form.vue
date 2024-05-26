<template>
  <div v-if="isOpen">
    <div class="cover" @click="removeEverythingAndEmitClose()"></div>
    <v-sheet class="sheet">
      <div v-if="!isNew">Edytuj zadanie</div>
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
          <v-btn
            :disabled="store.getters.isLoading"
            @click="setCommentId(comment.id)"
            color="green"
            class="ma-1"
          >
            <span class="mdi mdi-pencil"></span>
            <v-tooltip activator="parent" location="top" text="Edytuj"></v-tooltip>
          </v-btn>
          <v-btn 
            :disabled="store.getters.isLoading"
            @click="deleteCommentFromList(comment.id)"
            color="red"
            class="ma-1"
          >
            <span class="mdi mdi-delete"></span>
            <v-tooltip activator="parent" location="top" text="Usuń"></v-tooltip>
          </v-btn>
        </div>
        <v-btn v-if="taskId"
          @click="setCommentId(0)"
          :loading="store.getters.isLoading"
          type="button"
          color="green"
        >Dodaj komentarz</v-btn>
        <div v-for="attachment in form.attachments">
          <a target="_blank" :href="attachment.original_url">Link</a>
          <v-btn 
            :disabled="store.getters.isLoading"
            @click="deleteAttachment(attachment.id)"
            color="red"
            class="ma-1"
          >
            <span class="mdi mdi-delete"></span>
            <v-tooltip activator="parent" location="top" text="Usuń"></v-tooltip>
          </v-btn>
        </div>
        <v-file-input
          v-if="taskId"
          v-model="newAttachment"
          @change="addAttachment()"
          label="Dodaj załącznik"
          ></v-file-input>
        <v-btn
          :loading="store.getters.isLoading"
          type="submit"
          color="green"
        >Zapisz</v-btn>
        <v-btn
          @click="removeEverythingAndEmitClose()"
          :loading="store.getters.isLoading"
          type="button"
          color="red"
        >Anuluj</v-btn>
      </v-form>
    </v-sheet>
    <CommentForm v-if="commentId !== false" :taskId="taskId" :commentId="commentId" :isOpen="isCommentModalOpened" @modal-close="closeCommentModal()"></CommentForm>
  </div>
</template>
  
<script setup>
  
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Comments } from '../../helpers/api/apiComments';
    import { Tasks } from '../../helpers/api/apiTasks';
    import CommentForm from '../comments/Form.vue';
    
    const store = useStore();
    const apiComments = new Comments(store);
    const apiTasks = new Tasks(store);

    const props = defineProps({
        isOpen: Boolean,
        taskId: Number,
        catalogId: Number
    });
    const emit = defineEmits(["modal-close"]);

    const commentId = ref(false);
    const isCommentModalOpened = ref(false);
    const isNew = ref(false)
    const taskId = ref(false)
  
    const form = ref(
      {
        catalog_id: props.catalogId,
        name: '',
        description: null,
        date: null,
        place: null,
        position: 0,
        comments: [],
        attachments: []
      }
    );
    
    const newAttachment = ref(null);

    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];

    const openCommentModal = () => {
      isCommentModalOpened.value = true;
    };

    const closeCommentModal = async () => {
      isCommentModalOpened.value = false;
      commentId.value = false;

      const tmpForm = { value: {} };
      apiTasks.get(taskId.value, tmpForm).then(
        () => {
          form.value.comments = { ...tmpForm.value.comments };
        }
      );
    };

    function setCommentId(id) {
      commentId.value = id;
      openCommentModal();
    }

    async function deleteCommentFromList(id) {
      await apiComments.delete(id).then(
        () => {
          const tmpForm = { value: {} };
          apiTasks.get(taskId.value, tmpForm).then(
            () => {
              form.value.comments = { ...tmpForm.value.comments };
            }
          );
        }
      )
    }

    function deleteAttachment(id) {
      apiTasks.deleteAttachment(id, taskId.value).then(
        () => {
          const tmpForm = { value: {} };
          apiTasks.get(taskId.value, tmpForm).then(
            () => {
              form.value.attachments = { ...tmpForm.value.attachments };
            }
          );
        }
      );
    }

    function addAttachment() {
      apiTasks.addAttachment(taskId.value, newAttachment.value).then(
        () => {
          newAttachment.value = null;
          const tmpForm = { value: {} };
          apiTasks.get(taskId.value, tmpForm).then(
            () => {
              form.value.attachments = { ...tmpForm.value.attachments };
            }
          );
        }
      );
    }
  
    async function submit () {
      await apiTasks.edit(taskId.value, form);

      emit('modal-close');
    }

    async function removeEverythingAndEmitClose() {
      if (isNew.value) {
          await apiTasks.delete(taskId.value);
      }

      emit('modal-close');
    }

    if (props.taskId) {
      taskId.value = props.taskId;
      apiTasks.get(taskId.value, form);
    } else {
      isNew.value = true;
      form.value.name = '######';
      apiTasks.add(form).then(
        () => {
          form.value.name = '';
          taskId.value = form.value.id;
        }
      );
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