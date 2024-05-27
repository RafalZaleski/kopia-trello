import BoardsList from './pages/boards/List.vue'
import CatalogsList from './pages/catalogs/List.vue'
import KatalogForm from './pages/catalogs/Form.vue'
import TaskForm from './pages/tasks/Form.vue'
import CommentForm from './pages/comments/Form.vue'

export default [
    { path: '/', name: 'home', component: BoardsList },
    { path: '/tablice', name: 'boards', component: BoardsList },
    { path: '/tablica/:id', name: "catalogs", component: CatalogsList, children: [
        {
            path: "/tablica/:id/katalog/:catalogId",
            name: "catalogForm",
            component: KatalogForm
        },
        {
            path: "/tablica/:id/katalog/:catalogId/task/:taskId",
            name: "taskForm",
            component: TaskForm,
            children: [
                {
                    path: "/tablica/:id/katalog/:catalogId/task/:taskId/comment/:commentId",
                    name: "commentForm",
                    component: CommentForm
                },
            ]
        }
    ] },
]