import axios from 'axios';
import { standardErrorApiHandler } from '../standardErrorApiHandler.js';

export class Comments {
    constructor(store) {
        this.store = store;
        this.name = 'comments';
        this.boardIndex = this.store.state.boards.findIndex((elem) => elem.id == store.state.route.params.boardId);
        this.catalogIndex = this.store.state.boards[this.boardIndex].catalogs
            .findIndex((elem) => elem.id == store.state.route.params.catalogId);
        this.taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks
            .findIndex((elem) => elem.id == store.state.route.params.taskId);
        this.collectionName = 'boards';
    }

    async get(id, form) {
        this.store.commit('startLoading');

        const commentIndex = this.store.state.boards[this.boardIndex]
            .catalogs[this.catalogIndex].tasks[this.taskIndex].comments.findIndex((elem) => elem.id == id);
        const comment = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
            .tasks[this.taskIndex].comments[commentIndex];

        if (this.store.getters.useLocalStorage && comment) {
            form.value = { ...comment };
        } else {
            await axios.get('/api/comments/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        await axios.post('/api/comments', form.value)
            .then((response) => {
                this.store.commit(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                            .tasks[this.taskIndex].comments,
                    }
                );

                form.value = { ...response.data.data };
                
                this.store.state.notify({
                    type: 'success',
                    title: "Dodano komentarz",
                });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async edit(id, form) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + id, { ...form.value, _method: 'patch'})
        .then((response) => {
            this.store.commit(
                'editItemIn',
                {
                    name: this.name,
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                        .tasks[this.taskIndex].comments,
                    itemId: id
                }
            );

            form.value = { ...response.data.data };
            
            this.store.state.notify({
                type: 'success',
                title: "Zmieniono komentarz",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async delete(id) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + id, { _method: 'delete'})
        .then((response) => {
            this.store.commit(
                'deleteItemIn',
                {
                    name: this.name,
                    payload: id,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                        .tasks[this.taskIndex].comments,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: "Usunięto komentarz",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async addAttachment(commentId, file) {
        this.store.commit('startLoading');

        const formData = new FormData();
        formData.append('file', file);

        await axios.post('/api/comments/' + commentId + '/addAttachment', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        })
        .then((response) => {
            const commentIndex = this.store.state.boards[this.boardIndex]
                .catalogs[this.catalogIndex].tasks[this.taskIndex].comments.findIndex((elem) => elem.id == commentId);

            this.store.commit(
                'addItemIn',
                {
                    name: 'attachments',
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                        .tasks[this.taskIndex].comments[commentIndex].attachments,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: "Dodano załącznik",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async deleteAttachment(attachmentId, commentId) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + commentId + '/deleteAttachment/' + attachmentId, { _method: 'delete'})
        .then((response) => {
            const commentIndex = this.store.state.boards[this.boardIndex]
                .catalogs[this.catalogIndex].tasks[this.taskIndex].comments.findIndex((elem) => elem.id == commentId);

            this.store.commit(
                'deleteItemIn',
                {
                    name: 'attachments',
                    payload: attachmentId,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                        .tasks[this.taskIndex].comments[commentIndex].attachments,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: "Usunięto załącznik",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }
}