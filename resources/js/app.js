import './bootstrap';
import { createApp } from 'vue';

// Import your Vue component
import Example from './components/Example.vue';
import Dashboard from './components/Dashboard.vue'

createApp({
    components: {
        Example,
        Dashboard,
    },
}).mount('#app')
