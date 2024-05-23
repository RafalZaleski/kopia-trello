<template>
    <div>Lista katalogów</div>
    <router-link :to="{ name: 'boards' }">
      <v-btn 
        :disabled="store.getters.isLoading"
        color="green"
        class="ma-1"
      >
        Wstecz
      </v-btn>
    </router-link>

    <div class="board"
      v-on:dragover.prevent
      v-on:drop="handleDrop()"
      v-on:dragenter="handleDrag($event)"
      >
      <div
        v-for="catalog in showCatalogs"
        :key="catalog.position"
        :id="'catalog_' + catalog.id"
        class="catalog"
        draggable="true"
        v-on:dragstart="handleDragStart({ ...catalog, type: 'catalog' })"
        v-on:dragenter="handleDrag($event)"
        v-on:dragover.prevent
        v-on:dragover="checkArea($event)"
        v-on:drop="handleDrop()"
        >
        <v-btn
          :disabled="store.getters.isLoading"
          @click="setTaskId(catalog.id, 0)"
          color="green"
          class="ma-1"
        >
          <span class="mdi mdi-plus"></span>
          <v-tooltip activator="parent" location="top" text="dodaj zadanie"></v-tooltip>
        </v-btn>
        <v-btn
          :disabled="store.getters.isLoading"
          @click="editCatalogForm(catalog.id)"
          color="green"
          class="ma-1"
        >
          <span class="mdi mdi-pencil"></span>
          <v-tooltip activator="parent" location="top" text="Edytuj"></v-tooltip>
        </v-btn>
        <v-btn 
          :disabled="store.getters.isLoading"
          @click="deleteCatalogFromList(catalog.id)"
          color="red"
          class="ma-1"
        >
          <span class="mdi mdi-delete"></span>
          <v-tooltip activator="parent" location="top" text="Usuń"></v-tooltip>
        </v-btn>
        <div class="catalog_name">{{ catalog.name }}</div>
        <div class="catalog_description">{{ catalog.description }}</div>
        <br>
        <div v-for="task in catalog.tasks"
          :key="task.position"
          :id="'task_' + task.id"
          draggable="true"
          v-on:dragstart="handleDragStart({ ...task, type: 'task' })"
          v-on:dragenter="handleDrag($event)"
          v-on:dragover="checkArea($event)"
          class="task"
        >
          <div class="task_name" @click="setTaskId(catalog.id, task.id)">{{ task.name }}</div>
        </div>
      </div>
    </div>

    <v-btn
      :disabled="store.getters.isLoading"
      @click="editCatalogForm(0)"
      color="green"
      style="position:fixed; bottom: 30px; right: 30px;"
    >Dodaj katalog</v-btn>
    <Form v-if="catalogId !== false" :catalogId="catalogId" :isOpen="isModalOpened" @modal-close="closeModal()"></Form>
    <TaskForm v-if="taskId !== false" :taskId="taskId" :catalogId="catalogId" :isOpen="isTaskModalOpened" @modal-close="closeTaskModal()"></TaskForm>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Catalogs } from '../../helpers/api/apiCatalogs';
  import Form from './Form.vue';
  import TaskForm from '../tasks/Form.vue';
  import { Tasks } from '../../helpers/api/apiTasks';

  const store = useStore();
  const apiCatalogs = new Catalogs(store);
  const apiTasks = new Tasks(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.tasks.length / perPage), perPage: perPage});
  const showCatalogs = ref([]);
  const catalogId = ref(false);
  const taskId = ref(false);
  const dragElem = ref(false);
  const dropArea = ref(false);

  const isModalOpened = ref(false);
  const isTaskModalOpened = ref(false);
  const openModal = () => {
    isModalOpened.value = true;
  };
  const openTaskModal = () => {
    isTaskModalOpened.value = true;
  };
  const closeModal = () => {
    isModalOpened.value = false;
    catalogId.value = false;
    showCatalogs.value = filterCatalogsToShow();
  };

  const closeTaskModal = async () => {
    isTaskModalOpened.value = false;
    taskId.value = false;
    catalogId.value = false;
    // if (!store.getters.useLocalStorage) {
    //     localStorage.removeItem('catalogs')
    //     store.state.catalogs = [];
    // }

    // await apiCatalogs.getAll();
    showCatalogs.value = filterCatalogsToShow();
  };

  function setTaskId(catalog_id, id) {
    catalogId.value = catalog_id;
    taskId.value = id;
    openTaskModal();
  }

  async function deleteCatalogFromList(id) {
    await apiCatalogs.delete(id)
    if (showCatalogs.value.length === 1) {
      pagination.value.current = pagination.value.current - 1;
    }

    showCatalogs.value = filterCatalogsToShow();
  }

  function filterCatalogsToShow() {
    const results = store.state.boards.find((elem) => elem.id == store.state.route.params.id).catalogs;
    pagination.value.total = Math.ceil(results.length / pagination.value.perPage);
    return results.filter(
          (val, key) => 
            (key >= ((pagination.value.current - 1) * pagination.value.perPage))
            && (key < (pagination.value.current * pagination.value.perPage)));
  }

  function editCatalogForm(id) {
    catalogId.value = id;
    openModal();
  }

  onMounted(async () => {
    if (!store.getters.useLocalStorage) {
        localStorage.removeItem('catalogs')
        store.state.catalogs = [];
    }

    await apiCatalogs.getAll();
    showCatalogs.value = filterCatalogsToShow();
  })

  watch(
    () => pagination.value.current,
    () => showCatalogs.value = filterCatalogsToShow()
  )

  watch(
    () => store.state.catalogs.length,
    () => pagination.value.total = Math.ceil(store.state.catalogs.length / perPage)
  )

  const handleDragStart = (itemData) => {
    if (dragElem.value !== false) {
      return;
    }

    dragElem.value = itemData;
  };

  const checkArea = (event) => {
    if (!dropArea.value) {
      return;
    }

    if (dragElem.value.type === "catalog") {
      if (Math.abs(dropArea.value.cursorX - event.clientX) > dropArea.value.width / 4) {
        if (dropArea.value.elem.parentElement.lastElementChild == dropArea.value.elem) {
          dropArea.value.elem.parentElement.appendChild(document.getElementById(dragElem.value.type + '_' + dragElem.value.id));
        } else {
          if (dropArea.value.cursorX - event.clientX > dropArea.value.width / 4) {
            dropArea.value.elem.parentElement.insertBefore(
              document.getElementById(dragElem.value.type + '_' + dragElem.value.id),
              dropArea.value.elem
            );
          } else if (dropArea.value.cursorX - event.clientX < -dropArea.value.width / 4) {
            dropArea.value.elem.parentElement.insertBefore(
              document.getElementById(dragElem.value.type + '_' + dragElem.value.id),
              dropArea.value.elem.nextSibling
            );
          }
        }

        dropArea.value = false;
      }
    } else if (dragElem.value.type === "task") {
      if (Math.abs(dropArea.value.cursorY - event.clientY) > dropArea.value.height / 4) {
        if (dropArea.value.elem.parentElement.lastElementChild == dropArea.value.elem) {
          dropArea.value.elem.parentElement.appendChild(document.getElementById(dragElem.value.type + '_' + dragElem.value.id));
        } else {
          if (dropArea.value.cursorY - event.clientY > dropArea.value.height / 4) {
            dropArea.value.elem.parentElement.insertBefore(
              document.getElementById(dragElem.value.type + '_' + dragElem.value.id),
              dropArea.value.elem
            );
          } else if (dropArea.value.cursorY - event.clientY < -dropArea.value.height / 4) {
            dropArea.value.elem.parentElement.insertBefore(
              document.getElementById(dragElem.value.type + '_' + dragElem.value.id),
              dropArea.value.elem.nextSibling
            );
          }
        }

        dropArea.value = false;
      }
    }
  }

  const handleDrag = (event) => {
    if (dragElem.value === false) {
      return;
    }
    
    const clientX = event.clientX;
    const clientY = event.clientY;
    
    let targetItem = event.target;
    // ToDo max 5 prób, inaczej cancel
    while (!targetItem.classList.contains("catalog")
      && !targetItem.classList.contains("board")
      && !targetItem.classList.contains("task")
    ) {
      targetItem = targetItem.parentElement;
    }

    if (
      targetItem.classList.contains(dragElem.value.type)
      && targetItem.id !== dragElem.value.type + '_' + dragElem.value.id
    ) {
      dropArea.value = {
        elem: targetItem,
        width: targetItem.offsetWidth,
        height: targetItem.offsetHeight,
        cursorX: clientX,
        cursorY: clientY
      }
    } else {
      dropArea.value = false;
    }

   

    if (
      targetItem.classList.contains("catalog") 
      && dragElem.value.type === "task"
      && targetItem.id != "catalog_" + dragElem.value.catalog_id
    ) {
      targetItem.appendChild(document.getElementById(dragElem.value.type + '_' + dragElem.value.id));
    }
  }

  const handleDrop = async () => {
    if (dragElem.value === false) {
      return;
    }

    const dragElemDom = document.getElementById(dragElem.value.type + '_' + dragElem.value.id);
    const dragElemParent = dragElemDom.parentElement;

    let newPosition = 0;
    for ( let i = 0; i < dragElemParent.children.length; i++ ) {
      if (dragElemParent.children[i] == dragElemDom) {
        newPosition = i;
        break;
      }
    }

    if (dragElem.value.type === 'task') {
      newPosition -= 6;
      dragElem.value.position = newPosition;
      dragElem.value.catalog_id = dragElemParent.id.substring(8);
      await apiTasks.edit(dragElem.value.id, dragElem);
    } else {
      dragElem.value.position = newPosition;
      await apiCatalogs.edit(dragElem.value.id, dragElem);
    }

    dragElem.value = false;
    dropArea.value = false;
  };

</script>

<style>
  .board {
    display: flex;
    gap: 20px;
    margin: 20px;
    border: 1px solid #000;
  }

  .catalog {
    border: 1px solid #000;
    width: 270px;
    padding: 20px;
  }

  .catalog_description {
    font-size: 10px;
  }

  .task {
    border: 1px solid #666;
    margin: 5px 0;
  }
</style>