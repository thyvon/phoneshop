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
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                title="Actions"
              >
                <i class="fal fa-cog"></i> Actions
              </button>
              <div class="dropdown-menu">
                <a
                  v-if="actions.includes('edit')"
                  class="dropdown-item"
                  href="#"
                  @click.prevent="handleEdit(row)"
                >
                  Edit
                </a>
                <a
                  v-if="actions.includes('delete')"
                  class="dropdown-item"
                  href="#"
                  @click.prevent="handleDelete(row)"
                >
                  Delete
                </a>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'

const props = defineProps({
  headers: Array,
  rows: Array,
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

const emit = defineEmits(['edit', 'delete'])

const table = ref(null)

const normalizeKey = (key) => key.toString().toLowerCase().replace(/\s+/g, '_')

const isDateColumn = (key) =>
  key.endsWith('_at') || key.endsWith('_date')

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  })
}

const handleEdit = (row) => {
  emit('edit', row)
}

const handleDelete = (row) => {
  emit('delete', row)
}

const initializeDataTable = () => {
  if (window.$ && table.value) {
    $(table.value).DataTable(props.options) // Initialize DataTable
  } else {
    console.warn('SmartAdmin or DataTables not loaded')
  }
}

onMounted(async () => {
  await nextTick() // Ensure Vue has updated the DOM
  initializeDataTable()
})

// Reinitialize DataTable when the data changes
watch(
  () => props.rows,
  () => {
    if (window.$ && table.value) {
      $(table.value).DataTable().clear().rows.add(props.rows).draw()
    }
  },
  { deep: true }
)
</script>

<style scoped>
/* Optional: Override SmartAdmin or Bootstrap styles here */
</style>
