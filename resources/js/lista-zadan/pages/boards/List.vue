<template>
  <div class="d-flex align-center justify-space-between ma-4">
    <div>Lista tablic</div>
    <v-btn
      :disabled="store.getters.isLoading"
      @click="editBoardForm(0)"
      color="green"
    >Dodaj tablice</v-btn>
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
          Nazwa
        </th>
        <th class="text-left">
          Opis
        </th>
        <th class="text-right">
          Akcje
        </th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(board, index) in showBoards"
        :key="board.id"
        :class="[(index % 2) ? 'bg-surface' : 'bg-surface-light']"
      >
        <td><router-link :to="{ name: 'catalogs', params: { boardId: board.id } }">{{ board.name }}</router-link></td>
        <td>{{ board.description }}</td>
        <td class="text-right">
          <v-btn
            color="blue"
            class="ma-1"
          >
            <span class="mdi mdi-dots-vertical px-1"></span>
            <span class="px-1">Opcje</span>
            <v-tooltip activator="parent" location="top" text="Opcje"></v-tooltip>

            <v-menu activator="parent">
                <v-list>
                  <v-list-item>
                    <v-btn
                      :disabled="store.getters.isLoading"
                      @click="editBoardForm(board.id)"
                      color="green"
                      class="my-1 mx-6 w-75"
                    >
                      <span class="mdi mdi-pencil px-2"></span>
                      <span class="px-2">Edytuj</span>
                    </v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn 
                      :disabled="store.getters.isLoading"
                      @click="deleteBoardFromList(board.id)"
                      color="red"
                      class="my-1 mx-6 w-75"
                    >
                      <span class="mdi mdi-delete px-2"></span>
                      <span class="px-2">Usuń</span>
                    </v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn 
                      :disabled="store.getters.isLoading"
                      @click="copy(board.id)"
                      color="blue"
                      class="my-1 mx-6 w-75"
                    >
                      <span class="mdi mdi-content-copy px-2"></span>
                      <span class="px-2">Utwórz kopię</span>
                    </v-btn>
                  </v-list-item>
                  <v-list-item>
                    <FriendMenu :itemId="board.id" :api="apiBoards"></FriendMenu>
                  </v-list-item>
                </v-list>
            </v-menu>
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
  import { Boards } from '../../helpers/api/apiBoards';
  import FriendMenu from '../../../friends/components/FriendMenu.vue'; 

  const store = useStore();
  const apiBoards = new Boards(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.boards.length / perPage), perPage: perPage});
  const search = ref('');
  const timeout = ref(null);

  const showBoards = ref([]);

  function filterBoardsToShow() {
    if (!store.getters.useLocalStorage) {
      return;
    }

    if (search.value != '') {
      let total = 0;
      const results = store.state.boards.filter(
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
      showBoards.value = results;
    } else {
      pagination.value.total = Math.ceil(store.state.boards.length / pagination.value.perPage);
      showBoards.value = store.state.boards.filter(
            (val, key) => 
              (key >= ((pagination.value.current - 1) * pagination.value.perPage))
              && (key < (pagination.value.current * pagination.value.perPage)));
    }
  }

  function editBoardForm(id) {
    store.state.router.push(
      {
        name: 'boardForm',
        params: { 
          boardId: id,
        }
      }
    );
  }

  async function deleteBoardFromList(id) {
    await apiBoards.delete(id);

    filterBoardsToShow();
  }

  async function copy(id) {
    await apiBoards.copy(id);

    filterBoardsToShow();
  }

  onMounted(async () => {
    if (store.getters.useLocalStorage) {
      await apiBoards.getAll();
    } else {
      await apiBoards.getAllPaginate(showBoards, pagination);
    }
    
    filterBoardsToShow();
  });

  watch(
    () => store.state.boards,
    () => filterBoardsToShow(),
  );

  watch(
    () => pagination.value.current,
    () => {
      if (store.getters.useLocalStorage) {
        filterBoardsToShow();
      } else {
        apiBoards.getAllPaginate(showBoards, pagination);
      }
    }
  )

  watch(
    () => store.state.boards.length,
    () => {
      pagination.value.total = Math.ceil(store.state.boards.length / perPage);
      if (pagination.value.current > pagination.value.total) {
        pagination.value.current--; 
      } else {
        filterBoardsToShow();
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
        filterBoardsToShow();
      }, 300);
    }
  );

</script>