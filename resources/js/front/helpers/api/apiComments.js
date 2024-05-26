import axios from 'axios';
import { standardErrorApiHandler } from '../standardErrorApiHandler.js';

export class Comments {
    constructor(store) {
        this.store = store;
    }

    async getAll() {
        this.store.commit('startLoading');

        if (this.store.state.comments.length === 0) {
            await axios.get('/api/get-comments-all')
            .then((response) => {
                this.store.commit('syncItems', { name: 'comments', payload: response.data.data });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));
        }
        this.store.commit('stopLoading');
    }

    async getAllPaginate(comments, pagination) {
        this.store.commit('startLoading');

        await axios.get('/api/comments?page=' + pagination.value.current)
            .then((response) => {
                comments.value = response.data.data
                pagination.value.current = response.data.meta.current_page;
                pagination.value.total = response.data.meta.last_page;
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async get(id, form) {
        this.store.commit('startLoading');

        // const comment = this.store.state.comments.find((elem) => elem.id == id);
        // if (comment) {
        //     form.value = {...comment};
        // } else {
            await axios.get('/api/comments/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        // }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        await axios.post('/api/comments', form.value)
            .then((response) => {
                this.store.commit('addItemIn', { name: 'comments', payload: response.data.data });
                form.value = { ...response.data.data };
                this.store.state.notify({
                    type: 'success',
                    title: "Utworzono produkt",
                });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async edit(id, form) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + id, { ...form.value, _method: 'patch'})
        .then((response) => {
            this.store.commit('editItemIn', { name: 'comments', payload: response.data.data });
            form.value = { ...response.data.data };
            this.store.state.notify({
                type: 'success',
                title: "Zmieniono produkt",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async delete(id) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + id, { _method: 'delete'})
        .then((response) => {
            this.store.commit('deleteItemIn', { name: 'comments', payload: id });
            this.store.state.notify({
                type: 'success',
                title: "Usunięto produkt",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async addAttachment(id, file) {
        this.store.commit('startLoading');

        const formData = new FormData();
        formData.append('file', file);

        await axios.post('/api/comments/' + id + '/addAttachment', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
        })
        .then((response) => {
            this.store.state.notify({
                type: 'success',
                title: "dodano załącznik",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async deleteAttachment(id, commentId) {
        this.store.commit('startLoading');

        await axios.post('/api/comments/' + commentId + '/deleteAttachment/' + id, { _method: 'delete'})
        .then((response) => {
            this.store.state.notify({
                type: 'success',
                title: "usunięto załącznik",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    // async syncUpdated() {
    //     this.store.commit('startLoading');
        
    //     await axios.get('/api/comments/sync-updated?date=' + (this.store.state.commentsSyncDate ?? 0))
    //     .then((response) => {
    //         this.store.commit('syncItems', { name: 'comments', payload: response.data.data });
    //     })
    //     .catch((error) => standardErrorApiHandler(error, this.store));

    //     this.store.commit('stopLoading');
    // }

    // async syncDeleted() {
    //     this.store.commit('startLoading');
        
    //     await axios.get('/api/comments/sync-deleted?date=' + (this.store.state.commentsSyncDate ?? 0))
    //     .then((response) => {
    //         this.store.commit('syncDelete', { name: 'comments', payload: response.data.data });
    //     })
    //     .catch((error) => standardErrorApiHandler(error, this.store));

    //     this.store.commit('stopLoading');
    // }
}