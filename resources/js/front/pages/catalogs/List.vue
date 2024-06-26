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
        @click="setTaskId(0, catalog.id)"
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
        :id="'task_' + task.id"
        draggable="true"
        v-on:dragstart="handleDragStart({ ...task, type: 'task' })"
        v-on:dragenter="handleDrag($event)"
        v-on:dragover="checkArea($event)"
        class="task"
      >
        <div class="task_name" @click="setTaskId(task.id, catalog.id)">{{ task.name }}</div>
        <v-btn 
          :disabled="store.getters.isLoading"
          @click="deleteTaskFromList(task.id, catalog.id)"
          color="red"
          class="ma-1"
        >
          <span class="mdi mdi-delete"></span>
          <v-tooltip activator="parent" location="top" text="Usuń"></v-tooltip>
        </v-btn>
      </div>
    </div>
  </div>

  <v-btn
    :disabled="store.getters.isLoading"
    @click="editCatalogForm(0)"
    color="green"
    style="position:fixed; bottom: 30px; right: 30px;"
  >Dodaj katalog</v-btn>
  <router-view></router-view>
</template>

<script setup>

  import { ref, onMounted } from 'vue';
  import { useStore } from 'vuex';
  import { Boards } from '../../helpers/api/apiBoards';
  import { Catalogs } from '../../helpers/api/apiCatalogs';
  import { Tasks } from '../../helpers/api/apiTasks';

  const store = useStore();
  const apiBoards = new Boards(store);
  const apiCatalogs = new Catalogs(store);
  const apiTasks = new Tasks(store);

  const showCatalogs = ref([]);
  const dragElem = ref(false);
  const dropArea = ref(false);
  const handleDropStart = ref(false);

  async function filterCatalogsToShow() {
    await apiBoards.getAll().then(
      () => {
        showCatalogs.value = store.state.boards
          .find((elem) => elem.id == store.state.route.params.boardId).catalogs
          .sort((a, b) => a.position - b.position);
      }
    );
  }

  function editCatalogForm(catalogId) {
    store.state.router.push({ name: 'catalogForm', params: { boardId: store.state.route.params.boardId, catalogId: catalogId } });
  }

  async function deleteCatalogFromList(catalogId) {
    await apiCatalogs.delete(catalogId);
  }

  function setTaskId(taskId, catalogId) {
    store.state.router.push(
      {
        name: 'taskForm',
        params: { 
          boardId: store.state.route.params.boardId,
          catalogId: catalogId,
          taskId: taskId 
        }
      }
    );
  }

  async function deleteTaskFromList(taskId, catalogId) {
    await apiTasks.delete(taskId, catalogId);
  }

  onMounted(async () => {
    sortElements();
    await filterCatalogsToShow();
  })

  function sortElements() {
    const boardIndex = store.state.boards
      .findIndex((elem) => elem.id == store.state.route.params.boardId);

    store.state.boards[boardIndex].catalogs
      = [ ...store.state.boards[boardIndex].catalogs.sort((a, b) => a.position - b.position) ]
    
    for (const catalogIndex in store.state.boards[boardIndex].catalogs) {
      store.state.boards[boardIndex].catalogs[catalogIndex].tasks
        = [ ...store.state.boards[boardIndex].catalogs[catalogIndex].tasks.sort((a, b) => a.position - b.position) ];
    }

    localStorage.setItem('boards', JSON.stringify(store.state.boards));
  }

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

    if (!handleDropStart.value) {
      handleDropStart.value = true;
    } else {
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
      showCatalogs.value = null;
      newPosition -= 6;
      const newCatalogId = dragElemParent.id.substring(8);
      if (newPosition != dragElem.value.position || dragElem.value.catalog_id != newCatalogId) {
        await apiTasks.editPosition(
          dragElem.value.id,
          dragElem.value.catalog_id,
          newCatalogId,
          dragElem.value.position,
          newPosition
        );
        sortElements();
        await filterCatalogsToShow();
      }
    } else {
      if (newPosition != dragElem.value.position) {
        await apiCatalogs.editPosition(dragElem.value.id, dragElem.value.position, newPosition);
      }
    }

    dragElem.value = false;
    dropArea.value = false;
    handleDropStart.value = false;
  };

</script>

<style>
  .board {
    display: flex;
    gap: 20px;
    margin: 20px;
    max-height: 60vh;
    overflow-x: auto;
  }

  .catalog {
    border: 1px solid #000;
    min-width: 300px;
    padding: 20px;
    max-height: 100%;
    overflow-y: auto;
  }

  .catalog_description {
    font-size: 10px;
  }

  .task {
    border: 1px solid #666;
    margin: 5px 0;
  }
</style>