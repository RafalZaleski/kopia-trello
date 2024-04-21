import { createStore } from 'vuex'

export default createStore({
  state() {
    return {
      login: false,
      loadingCounter: 0,
      router: null,
      notify: null,
      tasks: JSON.parse(localStorage.getItem('tasks')) ?? [],
      tasksSyncDate: localStorage.getItem('tasksSyncDate') ?? null,
    }
  },
  getters: {
      isLoading(state) {
          return state.loadingCounter !== 0;
      },
      isLogin(state) {
        return state.login;
    },
  },
  mutations: {
    restartState(state) {
      localStorage.removeItem('tasks')
      localStorage.removeItem('tasksSyncDate')
      state.login = false;
      state.tasks = [];
      state.tasksSyncDate = null;
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
    setRouter(state, payload) {
      state.router = payload;
    },
    setNotify(state, payload) {
      state.notify = payload;
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