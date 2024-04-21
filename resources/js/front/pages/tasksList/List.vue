<template>
    <div>Lista zadań</div>
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
            Kategoria
          </th>
          <th class="text-left">
            Link
          </th>
          <th class="text-left">
            Akcje
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="task in showTasks"
          :key="task.name"
        >
          <td>{{ task.name }}</td>
          <td>{{ task.category }}</td>
          <td><a v-if="task.shop_url" :href="task.shop_url" target="_blank">Link</a></td>
          <td>
              <v-btn
                :disabled="store.getters.isLoading"
                @click="editTaskForm(task.id)"
                color="green"
                class="ma-1"
              >
                <span class="mdi mdi-pencil"></span>
                <v-tooltip activator="parent" location="top" text="Edytuj"></v-tooltip>
              </v-btn>
            <v-btn 
              :disabled="store.getters.isLoading"
              @click="deleteTaskFromList(task.id)"
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
      @click="editTaskForm(0)"
      color="green"
      style="position:fixed; bottom: 30px; right: 30px;"
    >Dodaj zadanie</v-btn>
    <TaskForm v-if="taskId !== false" :taskId="taskId" :isOpen="isModalOpened" @modal-close="closeModal()"></TaskForm>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Tasks } from '../../helpers/api/apiTasks';
  import TaskForm from './TaskForm.vue';  

  const store = useStore();
  const apiTasks = new Tasks(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.tasks.length / perPage), perPage: perPage});
  const showTasks = ref([]);
  const taskId = ref(false);
  const search = ref('');
  const timeout = ref(null);

  const isModalOpened = ref(false);
  const openModal = () => {
    isModalOpened.value = true;
  };
  const closeModal = () => {
    isModalOpened.value = false;
    taskId.value = false;
    showTasks.value = filterTasksToShow();
  };

  async function deleteTaskFromList(id) {
    await apiTasks.delete(id)
    if (showTasks.value.length === 1) {
      pagination.value.current = pagination.value.current - 1;
    }

    showTasks.value = filterTasksToShow();
  }

  function filterTasksToShow() {
    if (search.value != '') {
      let total = 0;
      const results = store.state.tasks.filter(
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
      pagination.value.total = Math.ceil(store.state.tasks.length / pagination.value.perPage);
      return store.state.tasks.filter(
            (val, key) => 
              (key >= ((pagination.value.current - 1) * pagination.value.perPage))
              && (key < (pagination.value.current * pagination.value.perPage)));
    }
  }

  function editTaskForm(id) {
    taskId.value = id;
    openModal();
  }

  onMounted(async () => {
    await apiTasks.getAll();
    showTasks.value = filterTasksToShow();
  })

  watch(
    () => pagination.value.current,
    () => showTasks.value = filterTasksToShow()
  )

  watch(
    () => store.state.tasks.length,
    () => pagination.value.total = Math.ceil(store.state.tasks.length / perPage)
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
        showTasks.value = filterTasksToShow();
      }, 300);
    }
  )
</script>