import Home from './pages/auth/Home.vue'
import Login from './pages/auth/Login.vue'
import Logout from './pages/auth/Logout.vue'
import Register from './pages/auth/Register.vue'

export default [
    { path: '/', name: 'home', component: Home },
    { path: '/zaloguj', name: 'login', component: Login },
    { path: '/wyloguj', name: 'logout', component: Logout },
    { path: '/rejestracja', name: 'register', component: Register },
]