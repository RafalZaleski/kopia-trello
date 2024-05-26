import axios from 'axios';
import { standardErrorApiHandler } from '../standardErrorApiHandler.js';

export class Boards {
    constructor(store) {
        this.store = store;
    }

    async getAll() {
        this.store.commit('startLoading');

        // if (this.store.state.boards.length === 0) {
            await axios.get('/api/get-boards-all')
            .then((response) => {
                this.store.commit('syncItems', { name: 'boards', payload: response.data.data });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));
        // }
        this.store.commit('stopLoading');
    }

    async getAllPaginate(boards, pagination) {
        this.store.commit('startLoading');

        await axios.get('/api/boards?page=' + pagination.value.current)
            .then((response) => {
                boards.value = response.data.data
                pagination.value.current = response.data.meta.current_page;
                pagination.value.total = response.data.meta.last_page;
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async get(id, form) {
        this.store.commit('startLoading');

        const task = this.store.state.boards.find((elem) => elem.id == id);
        if (task) {
            form.value = {...task};
        } else {
            await axios.get('/api/boards/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        await axios.post('/api/boards', form.value)
            .then((response) => {
                this.store.commit('addItemIn', { name: 'boards', payload: response.data.data });
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

        await axios.post('/api/boards/' + id, { ...form.value, _method: 'patch'})
        .then((response) => {
            this.store.commit('editItemIn', { name: 'boards', payload: response.data.data });
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

        await axios.post('/api/boards/' + id, { _method: 'delete'})
        .then((response) => {
            this.store.commit('deleteItemIn', { name: 'boards', payload: id });
            this.store.state.notify({
                type: 'success',
                title: "Usunięto produkt",
            });
        })
        .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    // async syncUpdated() {
    //     this.store.commit('startLoading');
        
    //     await axios.get('/api/boards/sync-updated?date=' + (this.store.state.boardsSyncDate ?? 0))
    //     .then((response) => {
    //         this.store.commit('syncItems', { name: 'boards', payload: response.data.data });
    //     })
    //     .catch((error) => standardErrorApiHandler(error, this.store));

    //     this.store.commit('stopLoading');
    // }

    // async syncDeleted() {
    //     this.store.commit('startLoading');
        
    //     await axios.get('/api/boards/sync-deleted?date=' + (this.store.state.boardsSyncDate ?? 0))
    //     .then((response) => {
    //         this.store.commit('syncDelete', { name: 'boards', payload: response.data.data });
    //     })
    //     .catch((error) => standardErrorApiHandler(error, this.store));

    //     this.store.commit('stopLoading');
    // }
}