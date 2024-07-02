import axios from 'axios';
import { standardErrorApiHandler } from '../../assets/helpers/standardErrorApiHandler.js';

export class Friends {
    constructor(store) {
        this.store = store;
        this.name = 'friends';
        this.collectionName = 'friends';
    }

    async getAll() {
        this.store.commit('startLoading');

        if (!this.store.getters.useLocalStorage || this.store.state.friends.length === 0) {
            await axios.get('/api/friends/get-all')
                .then((response) => {
                    this.store.dispatch(
                        'syncItems',
                        {
                            name: this.name,
                            payload: response.data.data,
                            collectionName: this.collectionName,
                            collection: this.store.state[this.collectionName]
                        }
                    );
                })
                .catch((error) => {
                    standardErrorApiHandler(error, this.store);
                });
        }

        this.store.commit('stopLoading');
    }

    async getAllPaginate(friends, pagination) {
        this.store.commit('startLoading');

        await axios.get('/api/friends?page=' + pagination.value.current)
            .then((response) => {
                friends.value = response.data.data
                pagination.value.current = response.data.meta.current_page;
                pagination.value.total = response.data.meta.last_page;
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async get(id, form) {
        this.store.commit('startLoading');

        let friend = false;
        if (this.store.getters.useLocalStorage) {
            friend = this.store.state.friend.find((elem) => elem.id == id);
            if (friend) {
                form.value = { ...friend };
            }
        }

        if (!this.store.getters.useLocalStorage || !friend) {
            await axios.get('/api/friends/' + id)
                .then((response) => {
                    this.store.dispatch(
                        'syncItems',
                        {
                            name: this.name,
                            payload: [ response.data.data ],
                            collectionName: this.collectionName,
                            collection: this.store.state[this.collectionName]
                        }
                    );

                    form.value = { ...response.data.data };
                })
                .catch((error) => {
                    standardErrorApiHandler(error, this.store);
                });
        }

        this.store.commit('stopLoading');
    }

    async invite(form) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/friends/invite', form.value)
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
                    title: "Wysłano zaproszenie do listy przyjaciół.",
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

    async confirm(id) {
        this.store.commit('startLoading');

        await axios.post('/api/friends/' + id, { _method: 'patch'})
            .then((response) => {
                this.store.dispatch(
                    'editItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state[this.collectionName],
                        itemId: id
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Potwierdzono.",
                });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }

    async delete(id) {
        this.store.commit('startLoading');

        await axios.post('/api/friends/' + id, { _method: 'delete'})
            .then((response) => {
                this.store.dispatch(
                    'deleteItemIn',
                    { 
                        name: this.name,
                        payload: id,
                        collectionName: this.collectionName,
                        collection: this.store.state[this.collectionName],
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Usunięto użytkownika z listy przyjaciół.",
                });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }
}