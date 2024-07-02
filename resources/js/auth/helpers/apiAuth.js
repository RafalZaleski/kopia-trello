import axios from 'axios';
import { standardErrorApiHandler } from '../../assets/helpers/standardErrorApiHandler.js';

export class Auth {
    constructor(store) {
        this.store = store;
    }

    async isLogged() {
        this.store.commit('startLoading');

        axios.get('/api/isLogged')
            .then((response) => {
                if (response.data) {
                    this.store.commit('login');
                } else {
                    this.store.commit('logout');
                }
            })
            .catch((error) => {
                this.store.commit('logout');
            });

        this.store.commit('stopLoading');
    }

    async getUser() {
        this.store.commit('startLoading');

        await axios.get('/api/user')
            .then((response) => {
                this.store.commit('login');
            })
            .catch((error) => {
                this.store.commit('logout');
            });

        this.store.commit('stopLoading');
    }

    async login(form) {
        this.store.commit('startLoading');

        await axios.post('/api/auth/login', form.value)
            .then((response) => {
                this.store.state.notify({
                    type: 'success',
                    title: "Zalogowano",
                });
                this.store.commit('login');
                return this.store.state.router.push({ name: 'home' });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }

    async logout() {
        this.store.commit('startLoading');

        await axios.post('/api/auth/logout', {})
            .then((response) => {
                this.store.state.notify({
                    type: 'success',
                    title: "Wylogowano",
                });
                this.store.commit('logout');
                return this.store.state.router.push({ name: 'login' });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }

    async register(form) {
        this.store.commit('startLoading');

        await axios.post('/api/auth/register', form.value)
            .then((response) => {
                this.store.state.notify({
                    type: 'success',
                    title: "Rejestracja udana",
                });
                this.isLogged();
                return this.store.state.router.push({ name: 'home' });
            })
            .catch((error) => {
                standardErrorApiHandler(error, this.store);
            });

        this.store.commit('stopLoading');
    }
}