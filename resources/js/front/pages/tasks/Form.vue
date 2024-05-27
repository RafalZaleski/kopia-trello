<template>
  <div class="cover" @click="removeEverythingAndEmitClose()"></div>
  <v-sheet class="sheet">
    <div v-if="!isNew">Edytuj zadanie</div>
    <div v-else>Dodaj zadanie</div>
    <v-form @submit.prevent="submit()">
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
        >
        <template v-slot:append>
            <v-btn @click="openDatePicker()">
              <v-icon icon="mdi-calendar"></v-icon>
            </v-btn>
        </template>
      </v-text-field>
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
  <router-view></router-view>
  <div class="cover" v-if="showDatePicker" @click="dateTimePickerCancel()"></div>
  <v-sheet class="sheetDatePicker" v-if="showDatePicker">
    <v-date-picker v-if="showDatePickerStep1" v-model="dataPicker" :hide-header="true"></v-date-picker>
    <v-time-picker v-if="showDatePickerStep2" v-model="timePicker" format="24hr" title=""></v-time-picker>
    <v-btn
        @click="dateTimePickerNextStep()"
        :loading="store.getters.isLoading"
        type="button"
        color="green"
      >Dalej</v-btn>
      <v-btn
        @click="dateTimePickerCancel()"
        :loading="store.getters.isLoading"
        type="button"
        color="red"
      >Anuluj</v-btn>
  </v-sheet>
</template>
  
<script setup>
  
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Comments } from '../../helpers/api/apiComments';
    import { Tasks } from '../../helpers/api/apiTasks';
    
    const store = useStore();
    const apiComments = new Comments(store);
    const apiTasks = new Tasks(store);

    const isNew = ref(false);
    const taskId = ref(false);
    const showDatePicker = ref(false);
    const showDatePickerStep1 = ref(false);
    const showDatePickerStep2 = ref(false);
    const dataPicker = ref(false);
    const timePicker = ref(false);
    const newAttachment = ref(null);
    
    const form = ref(
      {
        catalog_id: store.state.route.params.catalogId,
        name: '',
        description: null,
        date: null,
        place: null,
        position: 0,
        comments: [],
        attachments: []
      }
    );

    const rules = [
      value => {
        if (value) return true
  
        return 'Wprowadź wartość'
      },
    ];

    function openDatePicker() {
      dataPicker.value = new Date(form.value.date);
      timePicker.value = dataPicker.value.getHours() + ':' + dataPicker.value.getMinutes();
      showDatePicker.value = true;
      showDatePickerStep1.value = true;
    }

    function dateTimePickerCancel() {
      showDatePicker.value = false;
      showDatePickerStep1.value = false;
      showDatePickerStep2.value = false;
    }

    function dateTimePickerNextStep() {
      if (showDatePickerStep1.value) {
        showDatePickerStep1.value = false;
        showDatePickerStep2.value = true;
      } else {
        setDateTime();
        dateTimePickerCancel();
      }
    }

    function setDateTime() {
      form.value.date = formatDate(dataPicker.value);
      form.value.date += ' ' + timePicker.value;
    }

    function formatDate(date) {
      let day = date.getDate();
      if (day < 10) {
        day = '0' + day;
      }

      let month = date.getMonth() + 1;
      if (month < 10) {
        month = '0' + month;
      }

      return date.getFullYear() + '-' + month + '-' + day;
    }

    function setCommentId(id) {
      store.state.router.push(
        { 
          name: 'commentForm',
          params: { 
            id: store.state.route.params.id, 
            catalogId: store.state.route.params.catalogId, 
            taskId: store.state.route.params.taskId,
            commentId: id
          } 
        }
      );
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
  
    async function submit() {
      await apiTasks.edit(taskId.value, form);

      close();
    }

    async function removeEverythingAndEmitClose() {
      if (isNew.value) {
          await apiTasks.delete(taskId.value);
      }

      close();
    }

    function close() {
      store.state.router.push({ name: 'catalogs', params: { id: store.state.route.params.id } });
    }

    if (store.state.route.params.taskId > 0) {
      taskId.value = store.state.route.params.taskId;
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

  .sheetDatePicker {
    width: 350px;
    padding: 20px;
    position: fixed;
    left: calc(50% - 200px);
    top: 100px;
    max-height: 80%;
  }
</style>