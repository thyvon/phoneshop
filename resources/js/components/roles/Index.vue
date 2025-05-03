<template>
    <div>
      <h1 class="text-2xl font-bold mb-4">Roles</h1>
      <router-link to="/roles/create" class="btn btn-primary mb-3">Create Role</router-link>
  
      <table id="rolelist" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td class="p-2">{{ role.name }}</td>
            <td class="p-2">
              <router-link :to="`/roles/${role.id}/edit`" class="btn btn-sm btn-info mr-2">Edit</router-link>
              <button @click="deleteRole(role.id)" class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup>
  import { defineProps, onMounted } from 'vue'
  
  const props = defineProps({
    roles: Array
  })
  
  const deleteRole = async (id) => {
    if (!confirm('Are you sure you want to delete this role?')) return
  
    try {
      await axios.delete(`/roles/${id}`)
      window.location.reload() // Or use emit to update roles
    } catch (err) {
      alert('Error deleting role')
      console.error(err)
    }
  }
  
  // Initialize DataTable when the component is mounted
  onMounted(() => {
    $(document).ready(function() {
      $('#rolelist').DataTable({
        responsive: true
      });
    });
  });
  </script>
  
  <style scoped>
  /* Add custom styles if needed */
  </style>
  