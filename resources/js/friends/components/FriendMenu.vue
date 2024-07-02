<template>
    <v-btn
        :disabled="store.getters.isLoading"
        color="blue"
        class="mx-6 w-75"
    >
        <span class="mdi mdi-share px-2"></span>
        <span class="px-2">Udostępnij</span>

        <v-menu activator="parent">
            <v-list>
                <v-list-item v-for="(item) in store.state.friends" class="ma-0 pa-0">
                    <v-list-item-title class="cursor-pointer px-4 py-2" @click="share(item.id)">{{ item.name }}</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="store.state.friends.length === 0">
                    <v-list-item-title class="cursor-pointer px-4 py-2" @click="addFriend()">Dodaj przyjaciół</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu>
    </v-btn>
</template>

<script setup>

    import { useStore } from 'vuex';
    import { Friends } from '../helpers/apiFriends.js';

    const store = useStore();
    const apiFriends = new Friends(store);
    
    const props = defineProps({
        api: Object,
        itemId: Number
    });

    function share(userId) {
        props.api.share(props.itemId, userId);
    }

    function addFriend() {
        store.state.router.push(
            {
                name: 'friendForm',
            }
        );
    }

    apiFriends.getAll();

</script>