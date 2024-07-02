import axios from 'axios';
import { standardErrorApiHandler } from '../../../assets/helpers/standardErrorApiHandler.js';

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
                    this.store.dispatch(
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

        let board = false;
        if (this.store.getters.useLocalStorage) {
            board = this.store.state.boards.find((elem) => elem.id == id);
            if (board) {
                form.value = { ...board };
            }
        }

        if (!this.store.getters.useLocalStorage || !board) {
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
                this.store.dispatch(
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
                this.store.dispatch(
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
       
        await axios.post('/api/boards/' + id, { _method: 'delete'})
            .then((response) => {
                this.store.dispatch(
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
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }

    async copy(id) {
        this.store.commit('startLoading');

        await axios.get('/api/boards/' + id + '/copy')
            .then((response) => {
                this.store.dispatch(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state[this.collectionName]
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Utworzono kopię tablicy",
                });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }

    async share(boardId, userId) {
        this.store.commit('startLoading');

        await axios.get('/api/boards/' + boardId + '/share/' + userId)
            .then((response) => {
                this.store.state.notify({
                    type: 'success',
                    title: "Udostępniono tablicę",
                });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }
}