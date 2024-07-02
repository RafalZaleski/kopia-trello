import { standardErrorApiHandler } from './standardErrorApiHandler.js';

export async function sync(store) {
    if (store.getters.isLogin && store.getters.useLocalStorage) {
        await axios.get('/api/sync?date=' + (store.state.lastSync ?? 0))
            .then((response) => {
                store.commit('startLoading');
                for(const toUpdateKey in response.data.toUpdate) {
                    store.dispatch(
                        'syncItems',
                        {
                            name: toUpdateKey,
                            payload: response.data.toUpdate[toUpdateKey],
                            collectionName: toUpdateKey,
                            collection: store.state[toUpdateKey]
                        }
                    );
                }

                for(const toDeleteKey in response.data.toDelete) {
                    store.dispatch(
                        'syncDelete',
                        {
                            name: toDeleteKey,
                            payload: response.data.toDelete[toDeleteKey],
                            collectionName: toDeleteKey,
                            collection: store.state[toDeleteKey]
                        }
                    );
                }
                store.state.lastSync = Date.now() / 1000;
                localStorage.setItem('lastSync', store.state.lastSync);
                store.commit('stopLoading');
            })
            .catch((error) => {
                standardErrorApiHandler(error, store);
            });
        }

        // store.state.notify({
        //     type: 'success',
        //     title: 'Zakończono synchronizację.',
        // });
}

export function syncAuto(store) {
    setInterval(sync, 1000 * 60 * 1, store);
}