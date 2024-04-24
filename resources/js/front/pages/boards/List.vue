<template>
    <div>Lista tablic</div>
    <v-text-field
      v-model="search"
      label="Wyszukaj"
      :autofocus="true"
    ></v-text-field>
    <v-pagination
      v-model="pagination.current"
      :length="pagination.total"
      rounded="circle"
    ></v-pagination>
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
          <td><router-link :to="{ name: 'components', params: { id: board.id } }">{{ board.name }}</router-link></td>
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
    <Form v-if="boardId !== false" :boardId="boardId" :isOpen="isModalOpened" @modal-close="closeModal()"></Form>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Boards } from '../../helpers/api/apiBoards';
  import Form from './Form.vue';  

  const store = useStore();
  const apiBoards = new Boards(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.tasks.length / perPage), perPage: perPage});
  const showBoards = ref([]);
  const boardId = ref(false);
  const search = ref('');
  const timeout = ref(null);

  const isModalOpened = ref(false);
  const openModal = () => {
    isModalOpened.value = true;
  };
  const closeModal = () => {
    isModalOpened.value = false;
    boardId.value = false;
    showBoards.value = filterBoardsToShow();
  };

  async function deleteBoardFromList(id) {
    await apiBoards.delete(id)
    if (showBoards.value.length === 1) {
      pagination.value.current = pagination.value.current - 1;
    }

    showBoards.value = filterBoardsToShow();
  }

  function filterBoardsToShow() {
    if (search.value != '') {
      let total = 0;
      const results = store.state.boards.filter(
            (val) => {
              if (val.name.includes(search.value)) {
                total++;
                if (total >= ((pagination.value.current - 1) * pagination.value.perPage)
                  && (total < (pagination.value.current * pagination.value.perPage))) {
                    return true;
                  }
                }
              
              return false;
            });
      pagination.value.total = Math.ceil(total / pagination.value.perPage);
      return results;
    } else {
      pagination.value.total = Math.ceil(store.state.boards.length / pagination.value.perPage);
      return store.state.boards.filter(
            (val, key) => 
              (key >= ((pagination.value.current - 1) * pagination.value.perPage))
              && (key < (pagination.value.current * pagination.value.perPage)));
    }
  }

  function editBoardForm(id) {
    boardId.value = id;
    openModal();
  }

  onMounted(async () => {
    await apiBoards.getAll();
    showBoards.value = filterBoardsToShow();
  })

  watch(
    () => pagination.value.current,
    () => showBoards.value = filterBoardsToShow()
  )

  watch(
    () => store.state.boards.length,
    () => pagination.value.total = Math.ceil(store.state.boards.length / perPage)
  )

  watch(
    () => search.value,
    () => {
      if (timeout.value) {
        clearTimeout(timeout.value);
      }

      timeout.value = setTimeout(() => {
        search.value = search.value.toLowerCase();
        pagination.value.current = 1;
        showBoards.value = filterBoardsToShow();
      }, 300);
    }
  )
</script>