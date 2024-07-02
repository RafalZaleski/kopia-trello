<template>
  <div class="d-flex align-center justify-space-between ma-4">
    <div>Lista przyjaciół</div>
    <v-btn
      :disabled="store.getters.isLoading"
      @click="inviteFriend()"
      color="green"
    >Dodaj przyjaciela</v-btn>
  </div>
  <v-text-field
    v-model="search"
    label="Wyszukaj"
    :autofocus="false"
  ></v-text-field>
  <v-table>
    <thead>
      <tr>
        <th class="text-left">
          Nick
        </th>
        <th class="text-left">
          Email
        </th>
        <th class="text-right">
          Akcje
        </th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(friend, index) in showFriends"
        :key="friend.id"
        :class="[(index % 2) ? 'bg-surface' : 'bg-surface-light']"
      >
        <td>{{ friend.name }}</td>
        <td>{{ friend.email }}</td>
        <td class="text-right">
          <v-btn
            v-if="!friend.is_accepted && !friend.my_invitation"
            :disabled="store.getters.isLoading"
            @click="confirmFriend(friend.id)"
            color="green"
            class="ma-1"
          >
            <span class="mdi mdi-account-check"></span>
            <v-tooltip activator="parent" location="top" text="Akceptuj"></v-tooltip>
          </v-btn>
          <v-btn 
            :disabled="store.getters.isLoading"
            @click="deleteFriend(friend.id)"
            color="red"
            class="ma-1"
          >
            <span class="mdi mdi-delete"></span>
            <v-tooltip activator="parent" location="top" text="Usuń"></v-tooltip>
          </v-btn>
        </td>
      </tr>
    </tbody>
  </v-table>
  <v-pagination v-if="pagination.total > 1"
    v-model="pagination.current"
    :length="pagination.total"
    rounded="circle"
    class="mt-4"
  ></v-pagination>
  <router-view></router-view>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Friends } from '../helpers/apiFriends.js'; 

  const store = useStore();
  const apiFriends = new Friends(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.friends.length / perPage), perPage: perPage});
  const search = ref('');
  const timeout = ref(null);

  const showFriends = ref([]);

  function filterFriendToShow() {
    if (!store.getters.useLocalStorage) {
      return;
    }
    
    if (search.value != '') {
      let total = 0;
      const results = store.state.friends.filter(
            (val) => {
              if (val.name.toLowerCase().includes(search.value)) {
                total++;
                if (total > ((pagination.value.current - 1) * pagination.value.perPage)
                  && (total <= (pagination.value.current * pagination.value.perPage))) {
                    return true;
                  }
                }
              
              return false;
            });
      pagination.value.total = Math.ceil(total / pagination.value.perPage);
      showFriends.value = results;
    } else {
      pagination.value.total = Math.ceil(store.state.friends.length / pagination.value.perPage);
      showFriends.value = store.state.friends.filter(
            (val, key) => 
              (key >= ((pagination.value.current - 1) * pagination.value.perPage))
              && (key < (pagination.value.current * pagination.value.perPage)));
    }
  }

  async function inviteFriend() {
    store.state.router.push({ name: 'friendForm' });
  }

  async function confirmFriend(friendId) {
    await apiFriends.confirm(friendId);

    filterFriendToShow();
  }

  async function deleteFriend(friendId) {
    await apiFriends.delete(friendId);

    filterFriendToShow();
  }

  onMounted(async () => {
    if (store.getters.useLocalStorage) {
      await apiFriends.getAll();
    } else {
      await apiFriends.getAllPaginate(showFriends, pagination);
    }

    filterFriendToShow();
  });

  watch(
    () => store.state.friends,
    () => filterFriendToShow(),
  );

  watch(
    () => pagination.value.current,
    () => {
      if (store.getters.useLocalStorage) {
        filterFriendToShow();
      } else {
        apiFriends.getAllPaginate(showFriends, pagination);
      }
    }
  )

  watch(
    () => store.state.friends.length,
    () => {
      pagination.value.total = Math.ceil(store.state.friends.length / perPage);
      if (pagination.value.current > pagination.value.total) {
        pagination.value.current--; 
      } else {
        filterFriendToShow();
      }
    }
  );

  watch(
    () => search.value,
    () => {
      if (timeout.value) {
        clearTimeout(timeout.value);
      }

      timeout.value = setTimeout(() => {
        search.value = search.value.toLowerCase();
        pagination.value.current = 1;
        filterFriendToShow();
      }, 300);
    }
  );

</script>