import './bootstrap'
import { createApp } from 'vue'
import axios from 'axios'
// import 'bootstrap/dist/css/bootstrap.min.css';  // Import Bootstrap 5 CSS
// import 'bootstrap';  // Import Bootstrap 5 JS


// Axios configuration
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// CSRF token from Blade's <meta>
const token = document.querySelector('meta[name="csrf-token"]')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
  console.warn('CSRF token not found. Make sure <meta name="csrf-token" content="..."> is present in your layout.')
}

// Expose Axios globally for Blade scripts
window.axios = axios

// Vue Components
import Example from './components/Example.vue'
import Dashboard from './components/Dashboard.vue'
import RolePage from './components/roles/RolePage.vue'
import Datatable from './components/Datatable.vue'
import ProductPage from './components/products/ProductPage.vue'

//User Management
import UserPage from './components/users-management/User.vue'

// Create Vue app instance
const app = createApp({})

// Register global components
app.component('example', Example)
app.component('dashboard', Dashboard)
app.component('role-page', RolePage)
app.component('datatable', Datatable)
app.component('product-page', ProductPage)

// User Management
app.component('user-page', UserPage)

// Mount the Vue app
app.mount('#app')
