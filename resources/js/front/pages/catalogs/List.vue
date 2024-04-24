<template>
    <div>Lista katalogów</div>
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
          v-for="catalog in showCatalogs"
          :key="catalog.name"
        >
          <td><router-link :to="{ name: 'tasks', params: { id: catalog.id } }">{{ catalog.name }}</router-link></td>
          <td>{{ catalog.description }}</td>
          <td>
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
          </td>
        </tr>
      </tbody>
    </v-table>
    <v-btn
      :disabled="store.getters.isLoading"
      @click="editCatalogForm(0)"
      color="green"
      style="position:fixed; bottom: 30px; right: 30px;"
    >Dodaj katalog</v-btn>
    <Form v-if="catalogId !== false" :catalogId="catalogId" :isOpen="isModalOpened" @modal-close="closeModal()"></Form>
</template>

<script setup>

  import { ref, onMounted, watch } from 'vue';
  import { useStore } from 'vuex';
  import { Catalogs } from '../../helpers/api/apiCatalogs';
  import Form from './Form.vue';  

  const store = useStore();
  const apiCatalogs = new Catalogs(store);

  const perPage = 10;
  const pagination = ref({current: 1, total: Math.ceil(store.state.tasks.length / perPage), perPage: perPage});
  const showCatalogs = ref([]);
  const catalogId = ref(false);
  const search = ref('');
  const timeout = ref(null);

  const isModalOpened = ref(false);
  const openModal = () => {
    isModalOpened.value = true;
  };
  const closeModal = () => {
    isModalOpened.value = false;
    catalogId.value = false;
    showCatalogs.value = filterCatalogsToShow();
  };

  async function deleteCatalogFromList(id) {
    await apiCatalogs.delete(id)
    if (showCatalogs.value.length === 1) {
      pagination.value.current = pagination.value.current - 1;
    }

    showCatalogs.value = filterCatalogsToShow();
  }

  function filterCatalogsToShow() {
    if (search.value != '') {
      let total = 0;
      const results = store.state.catalogs.filter(
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
      pagination.value.total = Math.ceil(store.state.catalogs.length / pagination.value.perPage);
      return store.state.catalogs.filter(
            (val, key) => 
              (key >= ((pagination.value.current - 1) * pagination.value.perPage))
              && (key < (pagination.value.current * pagination.value.perPage)));
    }
  }

  function editCatalogForm(id) {
    catalogId.value = id;
    openModal();
  }

  onMounted(async () => {
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

  watch(
    () => search.value,
    () => {
      if (timeout.value) {
        clearTimeout(timeout.value);
      }

      timeout.value = setTimeout(() => {
        search.value = search.value.toLowerCase();
        pagination.value.current = 1;
        showCatalogs.value = filterCatalogsToShow();
      }, 300);
    }
  )
</script>