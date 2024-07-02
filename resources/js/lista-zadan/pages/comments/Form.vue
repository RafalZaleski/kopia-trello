<template>
    <div class="position-fixed top-0 right-0 bg-surface-light h-100 w-100 opacity-80" @click="removeEverythingAndEmitClose()"></div>
    <v-sheet class="sheet overflow-y-auto position-fixed pa-6">
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
            <div class="d-flex flex-row-reverse justify-space-between ">
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
            </div>
        </v-form>
    </v-sheet>
</template>

<script setup>
    
    import { ref } from 'vue';
    import { useStore } from 'vuex';
    import { Tasks } from '../../helpers/api/apiTasks';
    
    const store = useStore();
    const apiTasks = new Tasks(store);
    
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
        apiTasks.addCommentAttachment(store.state.route.params.taskId, commentId.value, newAttachment.value).then(
            () => {
                newAttachment.value = null;
                const tmpForm = { value: {} };
                apiTasks.getComment(store.state.route.params.taskId, commentId.value, tmpForm).then(
                    () => {
                        form.value.attachments = { ...tmpForm.value.attachments };
                    }
                );
            }
        );
    }

    function deleteAttachment(id) {
        apiTasks.deleteCommentAttachment(id, commentId.value, store.state.route.params.taskId).then(
            () => {
                const tmpForm = { value: {} };
                apiTasks.getComment(store.state.route.params.taskId, commentId.value, tmpForm).then(
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
            ans = await apiTasks.editComment(store.state.route.params.taskId, commentId.value, form);
        }

        if (ans) {
            close();
        }
    }

    async function removeEverythingAndEmitClose() {
        if (isNew.value) {
            await apiTasks.deleteComment(commentId.value);
        }

        close();
    }

    function close() {
        document.documentElement.style.overflow = 'auto';
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

    document.documentElement.style.overflow = 'hidden';

    if (store.state.route.params.commentId > 0) {
        commentId.value = store.state.route.params.commentId;
        apiTasks.getComment(store.state.route.params.taskId, commentId.value, form);
    } else {
      isNew.value = true;
      form.value.description = '######';
      apiTasks.addComment(store.state.route.params.taskId, form).then(
        () => {
          form.value.description = '';
          commentId.value = form.value.id;
        }
      );
    }

</script>