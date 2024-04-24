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
          <!-- <div v-if="store.getters.isLogin"> -->
            <router-link :to="{ name: 'boards' }"><v-list-item title="Lista tablic"></v-list-item></router-link>
            <!-- <router-link :to="{ name: 'logout' }"> -->
              <!-- <v-btn color="green" style="position: fixed; bottom: 85px; left: 30px;">Wyloguj się</v-btn> -->
            <!-- </router-link> -->
            <!-- <v-btn @click="sync(store)" color="green" style="position: fixed; bottom: 140px; left: 30px;">Synchronizuj</v-btn> -->
          <!-- </div> -->
          <!-- <div v-else> -->
            <!-- <router-link v-if="!store.getters.isLogin" :to="{ name: 'login' }"> -->
              <!-- <v-btn color="green" style="position: fixed; bottom: 85px; left: 30px;">Zaloguj się</v-btn> -->
            <!-- </router-link> -->
            <!-- <router-link v-if="!store.state.login" :to="{name: 'register'}"><v-list-item title="Zarejestruje się"></v-list-item></router-link> -->
          <!-- </div> -->
          <v-switch
          style="position: fixed; bottom: 0px; left: 30px;"
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
  import { useRouter } from 'vue-router';
  import { onMounted } from 'vue';
  // import { sync, syncAuto } from './helpers/sync';

  const store = useStore();
  const router = useRouter();
  const { notify } = useNotification();
  const theme = useTheme();

  const drawer = ref(false);

  const themeName = computed(() => {
    return theme.global.current.value.dark ? 'light' : 'dark';
  });

  function toggleTheme() {
    theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark';
    store.commit('setViewMode', theme.global.name.value);
  };

  onMounted(async () => {
    store.commit('setRouter', router);
    store.commit('setNotify', notify);
    
    theme.global.name.value = store.state.viewMode;
    // await apiAuth.isLogged().then(() => {
      // setTimeout(() => {
        // if (store.getters.isLogin) {
          // sync(store);
          // syncAuto(store);
        // }
      // }, 500);
    // });
  });

</script>