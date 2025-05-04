import './bootstrap'
import { createApp } from 'vue'

// Import your Vue components
import Example from './components/Example.vue'
import Dashboard from './components/Dashboard.vue'
import RoleList from './components/roles/Index.vue'
import Datatable from './components/Datatable.vue'

// Initialize Vue app
const app = createApp({})

// Register global components (optional)
app.component('example', Example)
app.component('dashboard', Dashboard)
app.component('role-list', RoleList)
app.component('datatable', Datatable)

// Mount Vue app to #app div
app.mount('#app')
