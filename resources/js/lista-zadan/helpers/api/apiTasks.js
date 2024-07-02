import axios from 'axios';
import { standardErrorApiHandler } from '../../../assets/helpers/standardErrorApiHandler.js';

export class Tasks {
    constructor(store) {
        this.store = store;
        this.name = 'tasks';
        this.boardIndex = this.store.state.boards.findIndex((elem) => elem.id == store.state.route.params.boardId);
        this.catalogIndex = this.store.state.boards[this.boardIndex]?.catalogs
            .findIndex((elem) => elem.id == store.state.route.params.catalogId);
        this.collectionName = 'boards';
    }

    async get(id, form) {
        this.store.commit('startLoading');

        let task = false;
        if (this.store.getters.useLocalStorage) {
            const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                .tasks.findIndex((elem) => elem.id == id);
            task = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex];

            if (task) {
                form.value = { ...task };
            }
        }

        if (!this.store.getters.useLocalStorage || !task) {
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
                this.store.dispatch(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks
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
                this.store.dispatch(
                    'editItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks,
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

                if (!this.store.getters.useLocalStorage) {
                    return;
                }

                const oldCatalogIndex = this.store.state.boards[this.boardIndex]
                    .catalogs.findIndex((elem) => elem.id == oldCatalogId);

                if (newCatalogId == oldCatalogId) {
                    this.store.dispatch(
                        'editItemIn',
                        {
                            name: this.name,
                            payload: response.data.data,
                            collectionName: this.collectionName,
                            collection: this.store.state.boards[this.boardIndex]?.catalogs[oldCatalogIndex]?.tasks,
                            itemId: taskId
                        }
                    );

                    for (const taskIndex in this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks) {
                        const task = this.store.state.boards[this.boardIndex].catalogs[oldCatalogIndex].tasks[taskIndex];
                        if (newPosition < oldPosition) {
                            if (task.position >= newPosition && task.position < oldPosition && task.id != taskId) {
                                task.position += 1;
                                this.store.dispatch(
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
                                this.store.dispatch(
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

                    this.store.dispatch(
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
                            this.store.dispatch(
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
                            this.store.dispatch(
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

                    this.store.dispatch(
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
            catalogIndex = this.store.state.boards[this.boardIndex]?.catalogs
                .findIndex((elem) => elem.id == catalogId);
        } else {
            catalogIndex = this.catalogIndex;
        }

        await axios.post('/api/tasks/' + taskId, { _method: 'delete'})
            .then((response) => {
                this.store.dispatch(
                    'deleteItemIn',
                    {
                        name: this.name,
                        payload: taskId,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs[catalogIndex]?.tasks,
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

    async getComment(taskId, commentId, form) {
        this.store.commit('startLoading');

        let comment = false;

        if (this.store.getters.useLocalStorage) {
            const taskIndex = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks
                .findIndex((elem) => elem.id == taskId);
            const commentIndex = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks[taskIndex]?.comments
                .findIndex((elem) => elem.id == commentId);
            comment = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks[taskIndex]?.comments[commentIndex];

            if (comment) {
                form.value = { ...comment };
            }
        }
        
        if (!this.store.getters.useLocalStorage || !comment) {
            await axios.get('/api/tasks/' + taskId + '/comment/' + commentId)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        }

        this.store.commit('stopLoading');
    }

    async addComment(taskId, form) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + taskId + '/comment', form.value)
        .then((response) => {
            form.value = { ...response.data.data };
            const taskIndex = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks
                .findIndex((elem) => elem.id == taskId);

            this.store.dispatch(
                'addItemIn',
                {
                    name: 'comments',
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks[taskIndex]?.comments,
                }
            );

            this.store.state.notify({
                type: 'success',
                title: 'Dodano komentarz',
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async editComment(taskId, commentId, form) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/tasks/' + taskId + '/comment/' + commentId, { ...form.value, _method: 'patch'})
            .then((response) => {
                const taskIndex = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks
                    .findIndex((elem) => elem.id == taskId);

                this.store.dispatch(
                    'editItemIn',
                    {
                        name: 'comments',
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks[taskIndex]?.comments,
                        itemId: commentId
                    }
                );
                
                this.store.state.notify({
                    type: 'success',
                    title: "Zmieniono komentarz",
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

    async deleteComment(commentId, taskId) {
        this.store.commit('startLoading');

        await axios.post('/api/tasks/' + taskId + '/comment/' + commentId, { _method: 'delete'})
        .then((response) => {
            const taskIndex = this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks.findIndex((elem) => elem.id == taskId);

            this.store.dispatch(
                'deleteItemIn',
                {
                    name: 'comments',
                    payload: commentId,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex]?.catalogs[this.catalogIndex]?.tasks[taskIndex]?.comments,
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

    async addCommentAttachment(taskId, commentId, file) {
        this.store.commit('startLoading');

        const formData = new FormData();
        formData.append('file', file);

        await axios.post('/api/tasks/' + taskId + '/comment/' + commentId + '/addAttachment', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        })
        .then((response) => {
            if (this.store.getters.useLocalStorage) {
                const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks.findIndex((elem) => elem.id == taskId);
                const commentIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks[taskIndex].comments.findIndex((elem) => elem.id == commentId);

                this.store.dispatch(
                    'addItemIn',
                    {
                        name: 'attachments',
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                            .tasks[taskIndex].comments[commentIndex].attachments,
                    }
                );
            }

            this.store.state.notify({
                type: 'success',
                title: 'Dodano załącznik',
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async deleteCommentAttachment(attachmentId, commentId, taskId) {
        this.store.commit('startLoading');

        await axios.post(
            '/api/tasks/' + taskId + '/comment/' + commentId + '/deleteAttachment/' + attachmentId,
            { _method: 'delete'}
        ).then((response) => {
            if (this.store.getters.useLocalStorage) {
                const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks.findIndex((elem) => elem.id == taskId);
                const commentIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks[taskIndex].comments.findIndex((elem) => elem.id == commentId);

                this.store.dispatch(
                    'deleteItemIn',
                    {
                        name: 'attachments',
                        payload: attachmentId,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                            .tasks[taskIndex].comments[commentIndex].attachments,
                    }
                );
            }

            this.store.state.notify({
                type: 'success',
                title: "Usunięto załącznik",
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
            if (this.store.getters.useLocalStorage) {
                const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks.findIndex((elem) => elem.id == taskId);

                this.store.dispatch(
                    'addItemIn',
                    {
                        name: 'attachments',
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex].attachments,
                    }
                );
            }

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
            if (this.store.getters.useLocalStorage) {
                const taskIndex = this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex]
                    .tasks.findIndex((elem) => elem.id == taskId);

                this.store.dispatch(
                    'deleteItemIn',
                    {
                        name: 'attachments',
                        payload: attachmentId,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex].catalogs[this.catalogIndex].tasks[taskIndex].attachments,
                    }
                );
            }

            this.store.state.notify({
                type: 'success',
                title: "Usunięto załącznik",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }
}