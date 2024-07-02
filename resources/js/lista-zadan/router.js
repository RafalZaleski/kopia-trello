import BoardsList from './pages/boards/List.vue'
import CatalogsList from './pages/catalogs/List.vue'
import BoardForm from './pages/boards/Form.vue'
import KatalogForm from './pages/catalogs/Form.vue'
import TaskForm from './pages/tasks/Form.vue'
import CommentForm from './pages/comments/Form.vue'

export default [
    { path: '/tablice', name: 'boards', component: BoardsList, children: [
        {
            path: "/tablice/:boardId",
            name: "boardForm",
            component: BoardForm
        },
    ] },
    { path: '/tablica/:boardId', name: "catalogs", component: CatalogsList, children: [
        {
            path: "/tablica/:boardId/katalog/:catalogId",
            name: "catalogForm",
            component: KatalogForm
        },
        {
            path: "/tablica/:boardId/katalog/:catalogId/task/:taskId",
            name: "taskForm",
            component: TaskForm,
            children: [
                {
                    path: "/tablica/:boardId/katalog/:catalogId/task/:taskId/comment/:commentId",
                    name: "commentForm",
                    component: CommentForm
                },
            ]
        }
    ] },
]