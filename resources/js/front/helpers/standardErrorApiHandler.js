export async function standardErrorApiHandler(error, store) {
    
    if (error.response?.status === 401) {
        store.commit('logout');
        store.state.notify({
            type: 'warn',
            title: "Wylogowano",
        });
        return store.state.router.push({ name: 'login' });
    } else if (error.response?.status === 400) {
        store.state.notify({
            type: 'error',
            title: 'Błąd',
            text: error.response.data.message,
        });
    } else if (error.response?.status === 422) {
        store.state.notify({
            type: 'error',
            title: 'Błąd',
            text: error.response.data.message,
        });
    } else {
        console.error(error);
        store.state.notify({
            type: 'error',
            title: 'Wystąpił błąd',
            text: 'Administrator został powiadomiony. Spróbuj ponownie później.',
        })
    }
}