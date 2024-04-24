import Home from './pages/Home.vue'
import TasksList from './pages/tasks/List.vue'
import BoardsList from './pages/boards/List.vue'
import CatalogsList from './pages/catalogs/List.vue'

export default [
    { path: '/', name: 'home', component: Home },
    { path: '/tablice', name: 'boards', component: BoardsList },
    { path: '/tablica/:id', name: 'components', component: CatalogsList },
    { path: '/katalog/:id', name: 'tasks', component: TasksList },
]