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
      useLocalStorage: localStorage.getItem('useLocalStorage') ?? true,
      lastSync: localStorage.getItem('lastSync') ?? null,
      boards: [],
      friends: [],
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
    removeLocalStorageItem(state, name) {
      localStorage.removeItem(name);
    },
    async getFromLocalStorage(state, name) {
      if (state.useLocalStorage) {
        let jsonState = localStorage.getItem(name);
        
        try {
          if (jsonState?.length) {
            jsonState = await JSON.parse(jsonState);
          } else {
            jsonState = [];
          }
        } catch (error) {
          console.log(name);
          console.log(error);
          jsonState = [];
        }

        state[name] = jsonState;
      }
    },
    restartState(state) {
      state.useLocalStorage = true;
      // state.login = false;
      
      localStorage.removeItem('lastSync');
      state.lastSync = null;
      this.commit('removeLocalStorageItem', 'boards');
      this.commit('removeLocalStorageItem', 'friends');
      state.boards = [];
      state.friends = [];
      state.router.push({ name: 'home' });
    },
    login(state) {
      state.login = true;
    },
    logout() {
      this.commit('restartState', false);
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
    saveToLocalStorage(state, payload) {
      if (state.useLocalStorage) {
        try {
          localStorage.setItem(payload.collectionName, JSON.stringify(state[payload.collectionName]));
        } catch (error) {
          this.commit('restartState', false);
          state.useLocalStorage = false;
          localStorage.setItem('useLocalStorage', false);
        }
      }
    } 
  },
  actions: {
    addItemIn({ state }, payload) {
      return new Promise((resolve) => {
        payload.collection.push(payload.payload);
        this.commit('saveToLocalStorage', payload);        
        resolve();
      });
    },
    editItemIn({ state }, payload) {
      return new Promise((resolve) => {
        const itemIndex = payload.collection.findIndex((elem) => elem.id == payload.itemId);
        payload.collection[itemIndex] = { ...payload.payload };
        state[payload.collectionName] = [ ...state[payload.collectionName] ];
        this.commit('saveToLocalStorage', payload);
        resolve();
      });
    },
    deleteItemIn({ state }, payload) {
      return new Promise((resolve) => {
        const itemIndex = payload.collection.findIndex((elem) => elem.id == payload.payload);
        payload.collection.splice(itemIndex, 1);
        this.commit('saveToLocalStorage', payload);
        resolve();
      });
    },
    async syncItems({ state }, payload) {
      for (let i = 0; i < payload.payload.length; i++) {
        const item = state[payload.name].find((element) => element.id === payload.payload[i].id);
        
        if (item) {
          const itemIndex = payload.collection.findIndex((elem) => elem.id == payload.payload[i].id);
          state[payload.collectionName][itemIndex] = { ...payload.payload[i] };
        } else {
          state[payload.collectionName].push(payload.payload[i]);
        }
      }

      state[payload.collectionName] = [ ...state[payload.collectionName] ];
      this.commit('saveToLocalStorage', payload);
    },
    async syncDelete({ state }, payload) {
      for (let i = 0; i < payload.payload.length; i++) {
        const elemIndex = state[payload.name].findIndex((elem) => elem.id == payload.payload[i].id);
        if (elemIndex > -1) {
          payload.collection.splice(elemIndex, 1);
        }
      }

      this.commit('saveToLocalStorage', payload);
    },
  },
})