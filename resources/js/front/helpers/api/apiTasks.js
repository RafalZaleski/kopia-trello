import axios from 'axios';
import { standardErrorApiHandler } from '../standardErrorApiHandler.js';

export class Tasks {
    constructor(store) {
        this.store = store;
        this.name = 'tasks';
        this.boardIndex = this.store.state.boards.findIndex((elem) => elem.id == store.state.route.params.boardId);
        this.catalogIndex = this.store.state.boards[this.boardIndex]
            .catalogs.findIndex((elem) => elem.id == store.state.route.params.catalogId);
        this.collectionName = 'boards';
    }

    async get(id, form) {
        this.store.commit('startLoading');

        const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
            .tasks.findIndex((elem) => elem.id == id);
        const task = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex];
        
        if (this.store.getters.useLocalStorage && task) {
            form.value = { ...task };
        } else {
            await axios.get('/api/tasks/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks', form.value)
            .then((response) => {
                this.store.commit(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks
                    }
                );

                form.value = { ...response.data.data };
                
                this.store.state.notify({
                    type: 'success',
                    title: "Dodano zadanie",
                });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async edit(id, form) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + id, { ...form.value, _method: 'patch'})
        .then((response) => {
            this.store.commit(
                'editItemIn',
                {
                    name: this.name,
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks,
                    itemId: id
                }
            );
            
            this.store.state.notify({
                type: 'success',
                title: "Zmieniono zadanie",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async delete(id) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + id, { _method: 'delete'})
        .then((response) => {
            this.store.commit(
                'deleteItemIn',
                {
                    name: this.name,
                    payload: id,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: "Usunięto zadanie",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async addAttachment(taskId, file) {
        this.store.commit('startLoading');

        const formData = new FormData();
        formData.append('file', file);

        await axios.post('/api/tasks/' + taskId + '/addAttachment', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        })
        .then((response) => {
            const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                .tasks.findIndex((elem) => elem.id == taskId);

            this.store.commit(
                'addItemIn',
                {
                    name: 'attachments',
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex].attachments,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: 'Dodano załącznik',
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async deleteAttachment(attachmentId, taskId) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + taskId + '/deleteAttachment/' + attachmentId, { _method: 'delete'})
        .then((response) => {
            const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                .tasks.findIndex((elem) => elem.id == taskId);

            this.store.commit(
                'deleteItemIn',
                {
                    name: 'attachments',
                    payload: attachmentId,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex].attachments,
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