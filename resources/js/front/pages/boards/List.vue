<template>
    <div>Lista tablic</div>
    <v-table>
      <thead>
        <tr>
          <th class="text-left">
            Nazwa
          </th>
          <th class="text-left">
            Opis
          </th>
          <th class="text-left">
            Akcje
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="board in showBoards"
          :key="board.name"
        >
          <td><router-link :to="{ name: 'catalogs', params: { boardId: board.id } }">{{ board.name }}</router-link></td>
          <td>{{ board.description }}</td>
          <td>
            <v-btn
              :disabled="store.getters.isLoading"
              @click="editBoardForm(board.id)"
              color="green"
              class="ma-1"
            >
              <span class="mdi mdi-pencil"></span>
              <v-tooltip activator="parent" location="top" text="Edytuj"></v-tooltip>
            </v-btn>
            <v-btn 
              :disabled="store.getters.isLoading"
              @click="deleteBoardFromList(board.id)"
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
    <v-btn
      :disabled="store.getters.isLoading"
      @click="editBoardForm(0)"
      color="green"
      style="position:fixed; bottom: 30px; right: 30px;"
    >Dodaj tablice</v-btn>
    <router-view></router-view>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Boards } from '../../helpers/api/apiBoards'; 

  const store = useStore();
  const apiBoards = new Boards(store);

  const showBoards = ref([]);

  async function deleteBoardFromList(id) {
    await apiBoards.delete(id);

    showBoards.value = filterBoardsToShow();
  }

  function filterBoardsToShow() {
    return store.state.boards;
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

  onMounted(async () => {
    if (!store.getters.useLocalStorage) {
        localStorage.removeItem('boards')
        store.state.boards = [];
    }

    await apiBoards.getAll();
    showBoards.value = filterBoardsToShow();
  })

</script>