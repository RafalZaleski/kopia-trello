<template>
    <v-layout>
      <v-app-bar>
        <router-link :to="{ name: 'home' }">
          <img src="zarro_black.svg" style="height: 45px; padding: 10px; margin-left: 20px; margin-top: 5px;">
        </router-link>
        <v-app-bar-nav-icon app variant="text" @click.stop="drawer = !drawer" style="position: absolute; right: 0;"></v-app-bar-nav-icon>
      </v-app-bar>

      <v-navigation-drawer v-model="drawer">
        <v-list>
          <div v-if="store.getters.isLogin">
            <router-link :to="{ name: 'boards' }"><v-list-item title="Lista tablic"></v-list-item></router-link>
            <router-link :to="{ name: 'friends' }"><v-list-item title="Lista przyjaciół"></v-list-item></router-link>
            <router-link :to="{ name: 'logout' }">
              <v-btn color="green" class="mx-6 mt-3" style="width: 80%;">Wyloguj się</v-btn>
            </router-link>
            <v-btn @click="sync(store)" color="green" class="mx-6 mt-3" style="width: 80%;">Synchronizuj</v-btn>
            <v-btn
              :disabled="store.getters.isLoading"
              @click="restartState()"
              color="green"
              class="mx-6 mt-3"
              style="width: 80%;"
            >Resetuj stan apki</v-btn>
            <v-switch
              :model-value="store.getters.useLocalStorage"
              class="mx-6 mt-3"
              @change="toggleUseLocalStore()"
              label="użyj local storage"
            ></v-switch>
          </div>
          <div v-else>
            <router-link v-if="!store.getters.isLogin" :to="{ name: 'login' }">
              <v-btn color="green" class="mx-6 mt-3" style="width: 80%;">Zaloguj się</v-btn>
            </router-link>
            <router-link v-if="!store.getters.isLogin" :to="{name: 'register'}">
              <v-btn color="green" class="mx-6 mt-3" style="width: 80%;">Zarejestruje się</v-btn>
            </router-link>
          </div>
          <v-switch
            class="mx-6 mt-0"
            @change="toggleTheme()"
            :label="'change to ' + themeName"
        ></v-switch>
        </v-list>
      </v-navigation-drawer>

      <v-main style="min-height: 300px; padding-bottom: 100px;">
        <router-view></router-view>
        <v-overlay scrollStrategy="block" v-model="store.getters.isLoading"></v-overlay>
      </v-main>
      <notifications position="bottom right"/>
    </v-layout>
</template>

<script setup>
  import { useTheme } from 'vuetify';
  import { ref, computed } from 'vue';
  import { useStore } from 'vuex';
  import { useNotification } from "@kyvg/vue3-notification";
  import { useRoute } from 'vue-router';
  import { useRouter } from 'vue-router';
  import { Auth } from './auth/helpers/apiAuth.js';
  import { onMounted } from 'vue';
  import { sync, syncAuto } from './assets/helpers/sync.js';

  const store = useStore();
  const route = useRoute();
  const router = useRouter();
  const { notify } = useNotification();
  const theme = useTheme();
  const apiAuth = new Auth(store);

  const drawer = ref(true);

  const themeName = computed(() => {
    return theme.global.current.value.dark ? 'light' : 'dark';
  });

  function toggleTheme() {
    theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark';
    store.commit('setViewMode', theme.global.name.value);
  };

  function toggleUseLocalStore() {
    store.commit('setUseLocalStorage');
  };

  function restartState() {
    store.commit('restartState', true);
  };

  onMounted(async () => {
    store.commit('setRoute', route);
    store.commit('setRouter', router);
    store.commit('setNotify', notify);
    store.commit('getFromLocalStorage', 'boards');
    store.commit('getFromLocalStorage', 'friends');

    theme.global.name.value = store.state.viewMode;

    await apiAuth.isLogged().then(() => {
      setTimeout(() => {
          sync(store);
          syncAuto(store);
      }, 500);
    });
  });

</script>