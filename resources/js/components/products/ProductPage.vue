<template>
    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow text-gray-900 dark:text-gray-100">
      <table ref="table" class="table table-bordered table-hover table-striped w-100 table-sm">
        <thead>
          <tr>
            <th>#</th>
            <th v-for="(header, index) in headers" :key="index">
              {{ header }}
            </th>
            <th v-if="actions.length || $slots.actions">Actions</th>
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
            <td v-if="actions.length || $slots.actions">
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
                  <!-- Loop through dynamic actions -->
                  <a
                    v-for="(action, actionIndex) in actions"
                    :key="actionIndex"
                    class="dropdown-item"
                    href="#"
                    @click.prevent="handleAction(action, row.id)"
                  >
                    {{ action.label }}
                  </a>
                  <!-- Use slot for additional custom actions -->
                  <slot name="actions" :row="row"></slot>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, nextTick } from 'vue'
  
  // Props for the component
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
  
  // Table reference for DataTable initialization
  const table = ref(null)
  
  // Normalize header keys for formatting
  const normalizeKey = (key) => key.toString().toLowerCase().replace(/\s+/g, '_')
  
  // Check if the column contains a date value
  const isDateColumn = (key) =>
    key.endsWith('_at') || key.endsWith('_date')
  
  // Format date columns to a readable format
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
  
  // Handle dynamic actions like edit, delete, etc.
  const handleAction = (action, rowId) => {
    if (action.handler) {
      action.handler(rowId) // Call the handler function passed with action
    }
  }
  
  // Initialize DataTable once the component is mounted
  onMounted(async () => {
    await nextTick()
    if (window.$ && table.value) {
      $(table.value).DataTable(props.options) // Initialize DataTable
    } else {
      console.warn('SmartAdmin or DataTables not loaded')
    }
  })
  </script>
  
  <style scoped>
  /* Optional: Override SmartAdmin or Bootstrap styles here */
  </style>
  