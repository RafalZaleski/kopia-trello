<template>
    <div class="cover" @click="removeEverythingAndEmitClose()"></div>
    <v-sheet class="sheet">
        <div v-if="!isNew">Edytuj komentarz</div>
        <div v-else>Dodaj komentarz</div>
        <v-form @submit.prevent="submit()" ref="formToSendComment">
            <v-textarea
                v-model="form.description"
                :rules="rules"
                label="opis"
                ></v-textarea>
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
            <v-file-input v-if="commentId" v-model="newAttachment" @change="addAttachment()" label="Dodaj załącznik"></v-file-input>
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
</template>

<script setup>
    
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Comments } from '../../helpers/api/apiComments';
    
    const store = useStore();
    const apiComments = new Comments(store);
    
    const form = ref({ task_id: store.state.route.params.taskId, description: '', attachments: [] });
    const formToSendComment = ref(null);
    const newAttachment = ref(null);
    const commentId = ref(null);
    const isNew = ref(false);

    const rules = [
        value => {
            if (value?.length > 0) return true
  
            return 'Wprowadź wartość (min 1 znak)'
        },
    ];

    function addAttachment() {
        apiComments.addAttachment(commentId.value, newAttachment.value).then(
            () => {
                newAttachment.value = null;
                const tmpForm = { value: {} };
                apiComments.get(commentId.value, tmpForm).then(
                    () => {
                        form.value.attachments = { ...tmpForm.value.attachments };
                    }
                );
            }
        );
    }

    function deleteAttachment(id) {
        apiComments.deleteAttachment(id, commentId.value).then(
            () => {
                const tmpForm = { value: {} };
                apiComments.get(commentId.value, tmpForm).then(
                    () => {
                        form.value.attachments = { ...tmpForm.value.attachments };
                    }
                );
            }
        );
    }
    
    async function submit () {
        const { valid } = await formToSendComment.value.validate();
        let ans = false;
        
        if (valid) {
            ans = await apiComments.edit(commentId.value, form);
        }

        if (ans) {
            close();
        }
    }

    async function removeEverythingAndEmitClose() {
        if (isNew.value) {
            await apiComments.delete(commentId.value);
        }

        close();
    }

    function close() {
        store.state.router.push(
            { 
                name: 'taskForm',
                params: {
                    boardId: store.state.route.params.boardId,
                    catalogId: store.state.route.params.catalogId,
                    taskId: store.state.route.params.taskId
                }
            }
        );
    }

    if (store.state.route.params.commentId > 0) {
        commentId.value = store.state.route.params.commentId;
        apiComments.get(commentId.value, form);
    } else {
      isNew.value = true;
      form.value.description = '######';
      apiComments.add(form).then(
        () => {
          form.value.description = '';
          commentId.value = form.value.id;
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