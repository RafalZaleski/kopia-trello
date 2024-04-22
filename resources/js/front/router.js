import Home from './pages/Home.vue'
import TasksList from './pages/tasksList/List.vue'

export default [
    { path: '/', name: 'home', component: Home },
    { path: '/lista-zadan', name: 'tasks', component: TasksList },
]