import './bootstrap';
import { createApp } from 'vue';

// Import your Vue component
import Example from './components/Example.vue';
import Dashboard from './components/Dashboard.vue';
import RoleList from './components/roles/Index.vue';

createApp({
    components: {
        Example,
        Dashboard,
        RoleList,
    },
}).mount('#app')
