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

        const ans = await axios.post('/api/tasks', form.value)
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

                return true;
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
                return false;
            });

        this.store.commit('stopLoading');
        return ans;
    }

    async edit(id, form) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/tasks/' + id, { ...form.value, _method: 'patch'})
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

                return true;
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
                return false;
            });

        this.store.commit('stopLoading');
        return ans;
    }

    async editPosition(taskId, oldCatalogId, newCatalogId, oldPosition, newPosition) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + taskId, { catalog_id: newCatalogId, position: newPosition, _method: 'patch'})
            .then((response) => {
                const oldCatalogIndex = this.store.state.boards[this.boardIndex]
                    .catalogs.findIndex((elem) => elem.id == oldCatalogId);

                if (newCatalogId == oldCatalogId) {
                    this.store.commit(
                        'editItemIn',
                        {
                            name: this.name,
                            payload: response.data.data,
                            collectionName: this.collectionName,
                            collection: this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks,
                            itemId: taskId
                        }
                    );

                    for (const taskIndex in this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks) {
                        const task = this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks[taskIndex];
                        if (newPosition < oldPosition) {
                            if (task.position >= newPosition && task.position < oldPosition && task.id != taskId) {
                                task.position += 1;
                                this.store.commit(
                                    'editItemIn',
                                    {
                                        name: this.name,
                                        payload: task,
                                        collectionName: this.collectionName,
                                        collection: this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks,
                                        itemId: task.id
                                    }
                                );
                            }
                        } else {
                            if (task.position <= newPosition && task.position > oldPosition && task.id != taskId) {
                                task.position -= 1;
                                this.store.commit(
                                    'editItemIn',
                                    {
                                        name: this.name,
                                        payload: task,
                                        collectionName: this.collectionName,
                                        collection: this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks,
                                        itemId: task.id
                                    }
                                );
                            }
                        }
                    }
                } else {
                    const newCatalogIndex = this.store.state.boards[this.boardIndex]
                        .catalogs.findIndex((elem) => elem.id == newCatalogId);

                    this.store.commit(
                        'addItemIn',
                        {
                            name: this.name,
                            payload: response.data.data,
                            collectionName: this.collectionName,
                            collection: this.store.state.boards[this.boardIndex].catalogs[newCatalogIndex].tasks
                        }
                    );

                    for (const taskIndex1 in this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks) {
                        const task = this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks[taskIndex1];
                        if (task.position > oldPosition) {
                            task.position -= 1;
                            this.store.commit(
                                'editItemIn',
                                {
                                    name: this.name,
                                    payload: task,
                                    collectionName: this.collectionName,
                                    collection: this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks,
                                    itemId: task.id
                                }
                            );
                        }
                    }

                    for (const taskIndex2 in this.store.state.boards[this.boardIndex].catalogs[newCatalogIndex].tasks) {
                        const task = this.store.state.boards[this.boardIndex].catalogs[newCatalogIndex].tasks[taskIndex2];
                        if (task.position >= newPosition && task.id != taskId) {
                            task.position += 1;
                            this.store.commit(
                                'editItemIn',
                                {
                                    name: this.name,
                                    payload: task,
                                    collectionName: this.collectionName,
                                    collection: this.store.state.boards[this.boardIndex].catalogs[newCatalogIndex].tasks,
                                    itemId: task.id
                                }
                            );
                        }
                    }

                    this.store.commit(
                        'deleteItemIn',
                        {
                            name: this.name,
                            payload: taskId,
                            collectionName: this.collectionName,
                            collection: this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks,
                        }
                    );
                }

                this.store.state.notify({
                    type: 'success',
                    title: "Zmieniono zadanie",
            });
        }).catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async delete(taskId, catalogId = null) {
        this.store.commit('startLoading');

        let catalogIndex;

        if (catalogId) {
            catalogIndex = this.store.state.boards[this.boardIndex]
                .catalogs.findIndex((elem) => elem.id == catalogId);
        } else {
            catalogIndex = this.catalogIndex;
        }

        await axios.post('/api/tasks/' + taskId, { _method: 'delete'})
        .then((response) => {
            this.store.commit(
                'deleteItemIn',
                {
                    name: this.name,
                    payload: taskId,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex].catalogs[catalogIndex].tasks,
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