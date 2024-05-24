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
      catalogs: JSON.parse(localStorage.getItem('catalogs')) ?? [],
      catalogsSyncDate: localStorage.getItem('catalogsSyncDate') ?? null,
      tasks: JSON.parse(localStorage.getItem('tasks')) ?? [],
      tasksSyncDate: localStorage.getItem('tasksSyncDate') ?? null,
      comments: JSON.parse(localStorage.getItem('comments')) ?? [],
      commentsSyncDate: localStorage.getItem('commentsSyncDate') ?? null,
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
      localStorage.removeItem('boards')
      localStorage.removeItem('boardsSyncDate')
      localStorage.removeItem('catalogs')
      localStorage.removeItem('catalogsSyncDate')
      localStorage.removeItem('tasks')
      localStorage.removeItem('tasksSyncDate')
      localStorage.removeItem('comments')
      localStorage.removeItem('commentsSyncDate')
      state.useLocalStorage = false,
      state.login = false;
      state.tasks = [];
      state.tasksSyncDate = null;
      state.comments = [];
      state.commentsSyncDate = null;
      state.boards = [];
      state.boardsSyncDate = null;
      state.catalogs = [];
      state.catalogsSyncDate = null;
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
      state[payload.name].push(payload.payload);
      
      const storage = JSON.parse(localStorage.getItem(payload.name)) ?? [];
      storage.push(payload.payload);
      localStorage.setItem(payload.name, JSON.stringify(storage));
    },
    editItemIn(state, payload) {
      const elemIndex = state[payload.name].findIndex((elem) => elem.id == payload.payload.id);
      if (elemIndex > -1) {
          state[payload.name][elemIndex] = { ...payload.payload };
      }

      const storage = JSON.parse(localStorage.getItem(payload.name)) ?? [];
      const elemIndexStorage = storage.findIndex((elem) => elem.id == payload.payload.id);
      if (elemIndexStorage > -1) {
        storage[elemIndexStorage] = { ...payload.payload };
        localStorage.setItem(payload.name, JSON.stringify(storage));
      }
    },
    deleteItemIn(state, payload) {
      const elemIndex = state[payload.name].findIndex((elem) => elem.id == payload.payload);
      if (elemIndex > -1) {
          state[payload.name].splice(elemIndex, 1);
      }

      const storage = JSON.parse(localStorage.getItem(payload.name)) ?? [];
      const elemIndexStorage = storage.findIndex((elem) => elem.id == payload.payload);
      if (elemIndexStorage > -1) {
        storage.splice(elemIndexStorage, 1);
        localStorage.setItem(payload.name, JSON.stringify(storage));
      }
    },
    syncItems(state, payload) {
      state[payload.name + 'SyncDate'] = Date.now() / 1000;
      for (let i = 0; i < payload.payload.length; i++) {
        const item = state[payload.name].find((element) => element.id === payload.payload[i].id);
        if (item) {
          this.commit('editItemIn', { name: payload.name, payload: payload.payload[i] });
        } else {
          this.commit('addItemIn', { name: payload.name, payload: payload.payload[i] });
        }
      }
    },
    syncDelete(state, payload) {
      for (let i = 0; i < payload.payload.length; i++) {
        const elemIndex = state[payload.name].findIndex((elem) => elem.id == payload.payload[i].id);
        if (elemIndex > -1) {
          this.commit('deleteItemIn', { name: payload.name, payload: payload.payload[i].id });
        }
      }
    },
  }
})