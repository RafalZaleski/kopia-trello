<template>
    <div class="ma-4">Lista katalogów</div>
    <v-btn 
      @click="touchMoveActive = !touchMoveActive"
      color="blue"
      class="ma-1"
    >
      <span v-if="touchMoveActive">Wyłącz przesuwanie elem</span>
      <span v-else>Włącz przesuwanie elem</span>
    </v-btn>
  <div class="d-flex align-center justify-space-between ma-4">
    <router-link :to="{ name: 'boards' }">
      <v-btn 
        :disabled="store.getters.isLoading"
        color="green"
        class="ma-1"
      >
        Wstecz
      </v-btn>
    </router-link>
    <v-btn
      :disabled="store.getters.isLoading"
      @click="editCatalogForm(0)"
      color="green"
    >Dodaj katalog</v-btn>
  </div>

  <div class="board"
    @dragover.prevent
    @drop="handleDrop()"
    @dragenter="handleDrag($event)"
  >
    <div
      v-for="catalog in showCatalogs"
      :id="'catalog_' + catalog.id"
      class="catalog"
      draggable="true"
      @dragstart="handleDragStart({ ...catalog, type: 'catalog' })"
      @dragenter="handleDrag($event)"
      @dragover.prevent
      @dragover="checkArea($event)"
      @drop="handleDrop()"
      @touchstart="handleTouchStart({ ...catalog, type: 'catalog' }, $event)"
      @touchmove="handleTouchMove($event)"
      @touchend="handleTouchEnd($event)"
    >
      <div class="d-flex align-center justify-space-between">
        <div>
          <div class="catalog_name">{{ catalog.name }}</div>
          <div class="catalog_description">{{ catalog.description }}</div>
        </div>
        <v-btn
          color="blue"
          class="ma-1"
        >
          <span class="mdi mdi-dots-vertical px-1"></span>
          <v-tooltip activator="parent" location="top" text="Opcje"></v-tooltip>

          <v-menu activator="parent">
              <v-list>
                <v-list-item>
                  <v-btn
                    :disabled="store.getters.isLoading"
                    @click="setTaskId(0, catalog.id)"
                    color="green"
                    class="my-1 mx-6 w-75"
                  >
                    <span class="mdi mdi-plus px-2"></span>
                    <span class="px-2">Dodaj zadanie</span>
                  </v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn
                    :disabled="store.getters.isLoading"
                    @click="editCatalogForm(catalog.id)"
                    color="green"
                    class="my-1 mx-6 w-75"
                  >
                    <span class="mdi mdi-pencil px-2"></span>
                    <span class="px-2">Edytuj</span>
                    <v-tooltip activator="parent" location="top" text=""></v-tooltip>
                  </v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn 
                    :disabled="store.getters.isLoading"
                    @click="deleteCatalogFromList(catalog.id)"
                    color="red"
                    class="my-1 mx-6 w-75"
                  >
                    <span class="mdi mdi-delete px-2"></span>
                    <span class="px-2">Usuń</span>
                  </v-btn>
                </v-list-item>
              </v-list>
          </v-menu>
        </v-btn>
      </div>
      <div v-for="task in catalog.tasks"
        :id="'task_' + task.id"
        draggable="true"
        @dragstart="handleDragStart({ ...task, type: 'task' })"
        @dragenter="handleDrag($event)"
        @dragover="checkArea($event)"
        @touchstart="handleTouchStart({ ...task, type: 'task' }, $event)"
        @touchmove="handleTouchMove($event)"
        @touchend="handleTouchEnd($event)"
        @contextmenu="$event.preventDefault()"
        class="task d-flex align-top justify-space-between"
      >
        <div class="task_name w-100 pa-2" @click="setTaskId(task.id, catalog.id)">{{ task.name }}</div>
        <v-btn
          color="blue-lighten-3"
          class="ma-1"
        >
          <span class="mdi mdi-dots-vertical px-1"></span>
          <v-tooltip activator="parent" location="top" text="Opcje"></v-tooltip>

          <v-menu activator="parent">
              <v-list>
                <v-list-item>
                  <v-btn 
                    :disabled="store.getters.isLoading"
                    @click="deleteTaskFromList(task.id, catalog.id)"
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
                    @click="setTaskId(task.id, catalog.id)"
                    color="green"
                    class="my-1 mx-6 w-75"
                  >
                    <span class="mdi mdi-pencil px-2"></span>
                    <span class="px-2">Edytuj</span>
                  </v-btn>
                </v-list-item>
              </v-list>
            </v-menu>
          </v-btn>
      </div>
    </div>
  </div>
  <router-view></router-view>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
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
  const targetItem = ref(false);
  const copyTargetItem = ref(false);
  const touchLocation = ref(false);
  const touchMoveActive = ref(false);

  async function filterCatalogsToShow() {
    if (store.getters.useLocalStorage) {
      await apiBoards.getAll().then(
        () => {
          showCatalogs.value = store.state.boards
            .find((elem) => elem.id == store.state.route.params.boardId).catalogs
            .sort((a, b) => a.position - b.position);
        }
      ); 
    } else {
      const board = {};
      await apiBoards.get(store.state.route.params.boardId, board).then(
        () => {
          showCatalogs.value = board.value.catalogs
            .sort((a, b) => a.position - b.position);
        }
      ); 
    }
  }

  async function filterCatalogsToShowWithoutApi() {
    showCatalogs.value = store.state.boards
      .find((elem) => elem.id == store.state.route.params.boardId).catalogs
      .sort((a, b) => a.position - b.position);
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

  async function sortElements() {
    const board = {};
    await apiBoards.get(store.state.route.params.boardId, board);

    board.value.catalogs
      = [ ...board.value.catalogs.sort((a, b) => a.position - b.position) ]
    
    for (const catalogIndex in board.value.catalogs) {
      board.value.catalogs[catalogIndex].tasks
        = [ ...board.value.catalogs[catalogIndex].tasks.sort((a, b) => a.position - b.position) ];
    }

    store.commit('saveToLocalStorage', {
      name: 'boards',
      payload: store.state.board,
      collectionName: 'boards',
      collection: store.state.boards,
    });
  }

  function handleDragStart(itemData) {
    if (dragElem.value !== false) {
      return;
    }

    dragElem.value = itemData;
    document.getElementById(dragElem.value.type + '_' + dragElem.value.id).style.opacity = 0.5;
  }

  function checkArea(event) {
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

        // dropArea.value = false;
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

        // dropArea.value = false;
      }
    }
  }

  function handleDrag(event) {
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
      targetItem.children.length === 1
      || (
        targetItem.classList.contains("catalog") 
        && dragElem.value.type === "task"
        && targetItem.id !== "catalog_" + dragElem.value.catalog_id
      )
    ) {
      if (!targetItem.contains(document.getElementById(dragElem.value.type + '_' + dragElem.value.id))) {
        targetItem.appendChild(document.getElementById(dragElem.value.type + '_' + dragElem.value.id));
      }
    }
  }

  async function handleDrop () {
    if (dragElem.value === false) {
      return;
    }

    if (!handleDropStart.value) {
      handleDropStart.value = true;
    } else {
      return;
    }

    const dragElemDom = document.getElementById(dragElem.value.type + '_' + dragElem.value.id);
    dragElemDom.style.opacity = 1;
    const dragElemParent = dragElemDom.parentElement;

    let newPosition = 0;
    for ( let i = 0; i < dragElemParent.children.length; i++ ) {
      if (dragElemParent.children[i] == dragElemDom) {
        newPosition = i;
        break;
      }
    }

    if (dragElem.value.type === 'task') {
      newPosition -= 1;
      const newCatalogId = dragElemParent.id.substring(8);
      if (newPosition != dragElem.value.position || dragElem.value.catalog_id != newCatalogId) {
        showCatalogs.value = null;
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
  }

  function handleTouchStart(itemData, event) {
    if (!touchMoveActive.value) {
      return;
    }

    if (dragElem.value !== false) {
      return;
    }

    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
    document.getElementsByClassName('board')[0].style.overflow = 'hidden';

    targetItem.value = event.target;

    // ToDo max 5 prób, inaczej cancel
    while (!targetItem.value.classList.contains("catalog")
      && !targetItem.value.classList.contains("board")
      && !targetItem.value.classList.contains("task")
    ) {
      targetItem.value = targetItem.value.parentElement;
    }

    copyTargetItem.value = targetItem.value.cloneNode(true);
    copyTargetItem.value.style.position = "fixed";
    copyTargetItem.value.style.width = 300 + "px";
    
    targetItem.value.style.opacity = 0.5;
    if (itemData.type === 'task') {
      document.getElementsByClassName('catalog')[0].appendChild(targetItem.value);
    } else {
      document.getElementsByClassName('board')[0].appendChild(targetItem.value);
    }

    dragElem.value = itemData;

    document.body.appendChild(copyTargetItem.value);
  }

  function handleTouchMove(event) {
    if (!touchMoveActive.value) {
      return;
    }

    if (dragElem.value === false) {
      return;
    }

    touchLocation.value = event.targetTouches[0];

    if (touchLocation.value.pageX > window.screen.width * 3 / 4) {
      document.getElementsByClassName('board')[0].scrollLeft += 4;
    } else if (touchLocation.value.pageX < window.screen.width / 4) {
      document.getElementsByClassName('board')[0].scrollLeft -= 4;
    }

    copyTargetItem.value.style.left = (touchLocation.value.pageX + 20) + "px";
    copyTargetItem.value.style.top = (touchLocation.value.pageY - 20) + "px";

    const catalogs = document.getElementsByClassName('catalog');
    const scrollLeft = document.getElementsByClassName('board')[0].scrollLeft;

    if (dragElem.value.type === "catalog") {
      for (let i = 0; i < catalogs.length; i++) {
        if (targetItem.value != catalogs[i]
          && detectTouchEnd(
            catalogs[i].offsetLeft,
            catalogs[i].offsetTop,
            touchLocation.value.pageX + scrollLeft,
            touchLocation.value.pageY,
            catalogs[i].offsetWidth,
            catalogs[i].offsetHeight
          )
        ) {
          if (i === 0) {
            document.getElementsByClassName('board')[0].insertBefore(
              targetItem.value,
              catalogs[i]
            );
          } else {
            catalogs[i].after(targetItem.value);
          }
        }
      }
    } else if (dragElem.value.type === "task") {
      let newCatalog = null;

      for (let i = 0; i < catalogs.length; i++) {
        if (detectTouchEnd(
          catalogs[i].offsetLeft,
          catalogs[i].offsetTop,
          touchLocation.value.pageX + scrollLeft,
          touchLocation.value.pageY,
          catalogs[i].offsetWidth,
          catalogs[i].offsetHeight
        )) {
          newCatalog = catalogs[i];
          break;
        }
      }

      if (newCatalog) {
        newCatalog.appendChild(targetItem.value);
      } else {
        newCatalog = targetItem.value.parentElement;
      }

      if (touchLocation.value.pageY > window.screen.height * 3 / 4) {
        newCatalog.scrollTop += 5;
      } else if ((touchLocation.value.pageY - 100) < window.screen.height / 4) {
        newCatalog.scrollTop -= 5;
      }

      const catalogTasks = newCatalog.getElementsByClassName('task');

      for (let i = 0; i < catalogTasks.length; i++) {
        if (targetItem.value != catalogTasks[i]
          && detectTouchEnd(
            catalogTasks[i].offsetLeft,
            catalogTasks[i].offsetTop,
            touchLocation.value.pageX + scrollLeft,
            touchLocation.value.pageY + newCatalog.scrollTop,
            catalogTasks[i].offsetWidth,
            catalogTasks[i].offsetHeight
          )
        ) {
          newCatalog.insertBefore(
            targetItem.value,
            catalogTasks[i]
          );
        }
      }
    }    
  }

  async function handleTouchEnd(event) {
    if (!touchMoveActive.value) {
      return;
    }

    if (dragElem.value === false) {
      return;
    }

    const dragElemTemp = dragElem.value;
    dragElem.value = false;

    document.documentElement.style.overflow = 'auto';
    document.body.style.overflow = 'auto';
    document.getElementsByClassName('board')[0].style.overflow = 'auto';
    targetItem.value.style.opacity = 1;
    if (copyTargetItem.value.parentElement) {
      copyTargetItem.value.parentElement.removeChild(copyTargetItem.value);
    }
    
    const dragElemParent = targetItem.value.parentElement;
    let newPosition = 0;
    for (let i = 0; i < dragElemParent.children.length; i++) {
      if (dragElemParent.children[i] == targetItem.value) {
        newPosition = i;
        break;
      }
    }

    if (dragElemTemp.type === 'task') {
      newPosition -= 1;
      const newCatalogId = dragElemParent.id.substring(8);
      if (newPosition != dragElemTemp.position || dragElemTemp.catalog_id != newCatalogId) {
        showCatalogs.value = null;
        await apiTasks.editPosition(
          dragElemTemp.id,
          dragElemTemp.catalog_id,
          newCatalogId,
          dragElemTemp.position,
          newPosition
        );
        sortElements();
        await filterCatalogsToShow();
      }
    } else {
      if (newPosition != dragElemTemp.position) {
        await apiCatalogs.editPosition(dragElemTemp.id, dragElemTemp.position, newPosition);
      }
    }

    targetItem.value = false;
    copyTargetItem.value = false;
  }

  function detectTouchEnd(x1, y1, x2, y2, w, h) {
    if (x1 > x2 || x2 > x1 + w) 
      return false;
    if (y1 > y2 || y2 > y1 + h) 
      return false;
    return true;
  }

  watch(
    () => store.state.boards,
    () => filterCatalogsToShowWithoutApi(),
  );

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
    max-width: 400px;
    padding: 20px;
    max-height: 100%;
    overflow-y: auto;
  }

  .catalog_description {
    font-size: 10px;
    max-height: 30px;
    overflow: hidden;
  }

  .task {
    border: 1px solid #666;
    margin: 5px 0;
  }
</style>