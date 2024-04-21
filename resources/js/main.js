import { createApp } from 'vue'
import App from './front/App.vue'

import store from './front/store'

import { createRouter, createWebHistory } from 'vue-router'
import routes from './front/router'

import Notifications from '@kyvg/vue3-notification'

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

const router = createRouter({
    history: createWebHistory(),
    routes,
  })

const vuetify = createVuetify({
  components,
  directives,
})

createApp(App).use(store).use(vuetify).use(router).use(Notifications).mount('#app')