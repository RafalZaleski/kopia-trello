import axios from 'axios';
import { standardErrorApiHandler } from '../../../assets/helpers/standardErrorApiHandler.js';

export class Catalogs {
    constructor(store) {
        this.store = store;
        this.name = 'catalogs';
        this.boardIndex = this.store.state.boards.findIndex((elem) => elem.id == store.state.route.params.boardId);
        this.collectionName = 'boards';
    }

    async get(id, form) {
        this.store.commit('startLoading');

        let catalog = false;
        if (this.store.getters.useLocalStorage) {
            const catalogIndex = this.store.state.boards[this.boardIndex].catalogs.findIndex((elem) => elem.id == id);
            catalog = this.store.state.boards[this.boardIndex].catalogs[catalogIndex];

            if (catalog) {
                form.value = { ...catalog };
            }
        }

        if (!this.store.getters.useLocalStorage || !catalog) {
            await axios.get('/api/catalogs/' + id)
                .then((response) => {
                    form.value = { ...response.data.data };
                })
                .catch((error) => standardErrorApiHandler(error, this.store));
        }

        this.store.commit('stopLoading');
    }

    async add(form) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/catalogs', form.value)
            .then((response) => {
                this.store.dispatch(
                    'addItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs,
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Utworzono listę",
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

        const ans = await axios.post('/api/catalogs/' + id, { ...form.value, _method: 'patch'})
            .then((response) => {
                this.store.dispatch(
                    'editItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs,
                        itemId: id
                    }
                );

                this.store.state.notify({
                    type: 'success',
                    title: "Zmieniono listę",
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

    async editPosition(catalogId, oldPosition, newPosition) {
        this.store.commit('startLoading');

        await axios.post('/api/catalogs/' + catalogId, { position: newPosition, _method: 'patch'})
            .then((response) => {
                
                if (!this.store.getters.useLocalStorage) {
                    return;
                }
                
                this.store.commit(
                    'editItemIn',
                    {
                        name: this.name,
                        payload: response.data.data,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs,
                        itemId: catalogId
                    }
                );
  
                if (newPosition < oldPosition) {
                    this.store.state.boards[this.boardIndex].catalogs
                        .filter((item) => item.position >= newPosition && item.position < oldPosition && item.id != catalogId)
                        .map((item) => item.position += 1);
                } else {
                    this.store.state.boards[this.boardIndex].catalogs
                        .filter((item) => item.position <= newPosition && item.position > oldPosition && item.id != catalogId)
                        .map((item) => item.position -= 1);
                }

                this.store.commit('saveToLocalStorage', {
                    name: this.name,
                    payload: response.data.data,
                    collectionName: this.collectionName,
                    collection: this.store.state.boards[this.boardIndex]?.catalogs,
                });

                this.store.state.notify({
                    type: 'success',
                    title: "Zmieniono listę",
                });
            })
            .catch((error) => standardErrorApiHandler(error, this.store));

        this.store.commit('stopLoading');
    }

    async delete(id) {
        this.store.commit('startLoading');

        const ans = await axios.post('/api/catalogs/' + id, { _method: 'delete'})
            .then((response) => {
                this.store.dispatch(
                    'deleteItemIn',
                    {
                        name: this.name,
                        payload: id,
                        collectionName: this.collectionName,
                        collection: this.store.state.boards[this.boardIndex]?.catalogs,
                    }
                );
                
                this.store.state.notify({
                    type: 'success',
                    title: "Usunięto listę",
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
}