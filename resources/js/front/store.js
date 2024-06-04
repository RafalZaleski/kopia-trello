import { createStore } from 'vuex'

export default createStore({
  state() {
    return {
      login: false,
      loadingCounter: 0,
      viewMode: localStorage.getItem('viewMode') ?? 'light',
      route: null,
      router: null,
      notify: null,
      useLocalStorage: localStorage.getItem('useLocalStorage') ?? false,
      boards: JSON.parse(localStorage.getItem('boards')) ?? [],
      boardsSyncDate: localStorage.getItem('boardsSyncDate') ?? null,
    }
  },
  getters: {
      isLoading(state) {
          return state.loadingCounter !== 0;
      },
      isLogin(state) {
        return state.login;
      },
      useLocalStorage(state) {
        return (state.useLocalStorage == 'true' || state.useLocalStorage == true) ?? false;
      },
  },
  mutations: {
    restartState(state) {
      localStorage.removeItem('boards');
      localStorage.removeItem('boardsSyncDate');
      state.useLocalStorage = false,
      state.login = false;
      state.boards = [];
      state.boardsSyncDate = null;
      state.router.push({ name: 'home' });
    },
    login(state) {
      state.login = true;
    },
    logout() {
      this.commit('restartState');
    },
    startLoading(state) {
      state.loadingCounter++;
    },
    stopLoading(state) {
      state.loadingCounter--;
    },
    setRoute(state, payload) {
      state.route = payload;
    },
    setRouter(state, payload) {
      state.router = payload;
    },
    setNotify(state, payload) {
      state.notify = payload;
    },
    setViewMode(state, payload) {
      state.viewMode = payload;
      localStorage.setItem('viewMode', payload);
    },
    setUseLocalStorage(state) {
      const useLocalStorage = (state.useLocalStorage == 'false' || state.useLocalStorage == false) ?? false;
      state.useLocalStorage = useLocalStorage;
      localStorage.setItem('useLocalStorage', useLocalStorage);
    },
    addItemIn(state, payload) {
      payload.collection.push(payload.payload)
      localStorage.setItem(payload.collectionName, JSON.stringify(state[payload.collectionName]));
    },
    editItemIn(state, payload) {
      const itemIndex = payload.collection.findIndex((elem) => elem.id == payload.itemId);
      payload.collection[itemIndex] = { ...payload.payload }
      localStorage.setItem(payload.collectionName, JSON.stringify(state[payload.collectionName]));
    },
    deleteItemIn(state, payload) {
      const itemIndex = payload.collection.findIndex((elem) => elem.id == payload.payload);
      payload.collection.splice(itemIndex, 1);
      localStorage.setItem(payload.collectionName, JSON.stringify(state[payload.collectionName]));
    },
    syncItems(state, payload) {
      state[payload.name + 'SyncDate'] = Date.now() / 1000;
      for (let i = 0; i < payload.payload.length; i++) {
        const item = state[payload.name].find((element) => element.id === payload.payload[i].id);
        
        if (item) {
          this.commit(
            'editItemIn',
            {
                name: payload.name,
                payload: payload.payload[i],
                collectionName: payload.collectionName,
                collection: payload.collection,
                itemId: payload.payload[i].id 
            }
          );
        } else {
          this.commit(
            'addItemIn',
            {
              name: payload.name,
              payload: payload.payload[i],
              collectionName: payload.collectionName,
              collection: payload.collection 
            }
          );
        }
      }
    },
    syncDelete(state, payload) {
      for (let i = 0; i < payload.payload.length; i++) {
        const elemIndex = state[payload.name].findIndex((elem) => elem.id == payload.payload[i].id);
        if (elemIndex > -1) {
          this.commit(
            'deleteItemIn',
            { 
                name: payload.name,
                payload: payload.payload[i].id,
                collectionName: payload.collectionName,
                collection: payload.collection,
            }
          );
          this.commit('deleteItemIn', { name: payload.name, payload: payload.payload[i].id });
        }
      }
    },
  }
})