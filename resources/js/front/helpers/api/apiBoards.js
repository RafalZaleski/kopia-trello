import axios from 'axios';
import { standardErrorApiHandler } from '../standardErrorApiHandler.js';

export class Boards {
    constructor(store) {
        this.store = store;
        this.name = 'boards';
        this.collectionName = 'boards';
    }

    async getAll() {
        this.store.commit('startLoading');

        if (!this.store.getters.useLocalStorage || this.store.state.boards.length === 0) {
            await axios.get('/api/get-boards-all')
                .then((response) => {
                    this.store.commit(
                        'syncItems',
                        {
                            name: this.name,
                            payload: response.data.data,
                            collectionName: this.collectionName,
                            collection: this.store.state.boards
                        }
                    );
                })
                .catch((error) => {
                    standardErrorApiHandler(error, this.store);
                });
        }

        this.store.commit('stopLoading');
    }

    // async getAllPaginate(boards, pagination) {
    //     this.store.commit('startLoading');

    //     await axios.get('/api/boards?page=' + pagination.value.current)
    //         .then((response) => {
    //             boards.value = response.data.data
    //             pagination.value.current = response.data.meta.current_page;
    //             pagination.value.total = response.data.meta.last_page;
    //         })
    //         .catch((error) => standardErrorApiHandler(error, this.store));

    //     this.store.commit('stopLoading');
    // }

    async get(id, form) {
        this.store.commit('startLoading');

        const board = this.store.state.boards.find((elem) => elem.id == id);
        if (this.store.getters.useLocalStorage && board) {
            form.value = { ...board };
        } else {
            await axios.get('/api/boards/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => {
                    standardErrorApiHandler(error, this.store);
                });
        }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/boards', form.value)
            .then((response) => {
                this.store.commit(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Utworzono tablicę",
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
       
        const ans = await axios.post('/api/boards/' + id, { ...form.value, _method: 'patch'})
            .then((response) => {
                this.store.commit(
                    'editItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards,
                        itemId: id 
                    }
                );
                
                this.store.state.notify({
                    type: 'success',
                    title: "Zmieniono tablicę",
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

    async delete(id) {
        this.store.commit('startLoading');
       
        const ans = await axios.post('/api/boards/' + id, { _method: 'delete'})
            .then((response) => {
                this.store.commit(
                    'deleteItemIn',
                    { 
                        name: this.name,
                        payload: id,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards,
                    }
                );
                this.store.state.notify({
                    type: 'success',
                    title: "Usunięto tablicę",
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