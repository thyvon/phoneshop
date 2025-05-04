<template>
  <div class="bg-white dark:bg-gray-800 p-6 rounded shadow text-gray-900 dark:text-gray-100">
    <table ref="table" class="table table-bordered table-hover table-striped w-100 table-sm">
      <thead>
        <tr>
          <th>#</th>
          <th v-for="(header, index) in headers" :key="index">
            {{ header }}
          </th>
          <th v-if="actions.length">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
          <td>{{ rowIndex + 1 }}</td>
          <td v-for="(header, colIndex) in headers" :key="colIndex">
            <span v-if="isDateColumn(normalizeKey(header))">
              {{ formatDate(row[normalizeKey(header)]) }}
            </span>
            <span v-else>
              {{ row[normalizeKey(header)] ?? '' }}
            </span>
          </td>
          <td v-if="actions.length">
            <div class="btn-group" role="group">
              <button
                type="button"
                class="btn btn-secondary btn-sm dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                Actions
              </button>
              <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
                <li v-if="actions.includes('edit')">
                  <a class="dropdown-item" href="#" @click="handleEdit(row.id)">Edit</a>
                </li>
                <li v-if="actions.includes('delete')">
                  <a class="dropdown-item" href="#" @click="handleDelete(row.id)">Delete</a>
                </li>
              </ul>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  headers: {
    type: Array,
    required: true,
  },
  rows: {
    type: Array,
    required: true,
  },
  actions: {
    type: Array,
    default: () => [],
  },
  options: {
    type: Object,
    default: () => ({
      responsive: true,
      pageLength: 20,
      lengthMenu: [[20, 50, 100, 1000], [20, 50, 100, 1000]],
    }),
  },
})

const table = ref(null)

const normalizeKey = (key) => {
  return key.toString().toLowerCase().replace(/\s+/g, '_')
}

const isDateColumn = (key) => {
  return key.endsWith('_at') || key.endsWith('_date')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const options = {
    year: 'numeric',
    month: 'long',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  }
  return new Date(dateString).toLocaleString('en-US', options)
}

const handleEdit = (id) => {
  console.log('Edit action for ID:', id)
  // Emit an event or use router navigation for editing
}

const handleDelete = (id) => {
  console.log('Delete action for ID:', id)
  // Emit an event or trigger modal/confirmation for deletion
}

onMounted(async () => {
  await nextTick()
  if (window.$ && table.value) {
    $(table.value).DataTable(props.options)  // Initialize DataTable using jQuery
  } else {
    console.warn('jQuery or DataTable is not loaded')
  }

  // Initialize Bootstrap dropdown if needed (SmartAdmin or Bootstrap)
  if (window.bootstrap) {
    const dropdowns = document.querySelectorAll('.dropdown-toggle')
    dropdowns.forEach((dropdown) => {
      new bootstrap.Dropdown(dropdown)
    })
  } else {
    console.warn('Bootstrap JS is not loaded')
  }
})
</script>

<style scoped>
/* You can add custom styles if needed */
</style>
