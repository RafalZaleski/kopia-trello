<template>
    <div v-if="isOpen">
        <div class="cover" @click="emit('modal-close')"></div>
        <v-sheet class="sheet">
            <div v-if="props.commentId">Edytuj komentarz</div>
            <div v-else>Dodaj komentarz</div>
            <v-form @submit.prevent="submit">
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
                <v-file-input v-if="props.commentId" v-model="newAttachment" @change="addAttachment()" label="Dodaj załącznik"></v-file-input>
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
    import { Comments } from '../../helpers/api/apiComments';
    
    const store = useStore();
    const apiComments = new Comments(store);

    const props = defineProps({
        isOpen: Boolean,
        taskId: Number,
        commentId: Number
    });
    const emit = defineEmits(["modal-close"]);
    
    const form = ref({ task_id: props.taskId, description: '', attachments: [] });
    const newAttachment = ref(null);

    const rules = [
        value => {
            if (value) return true

            return 'Wprowadź wartość'
        },
    ];

    function addAttachment() {
        apiComments.addAttachment(props.commentId, newAttachment.value).then(
            () => {
                apiComments.get(props.commentId, form);
                newAttachment.value = null;
            }
        );
    }

    function deleteAttachment(id) {
        apiComments.deleteAttachment(id, props.commentId).then(
            () => {
                apiComments.get(props.commentId, form);
            }
        );
    }
    
    async function submit () {
        if (props.commentId) {
            await apiComments.edit(props.commentId, form);
        } else {
            await apiComments.add(form);
        }
        emit('modal-close');
    }

    if (props.commentId) {
        apiComments.get(props.commentId, form);
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