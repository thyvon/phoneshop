<template>
  <div>
    <div v-if="$slots['additional-header']" class="datatable-header mb-2">
      <slot name="additional-header" />
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow text-gray-900 dark:text-gray-100">
      <table ref="table" class="table table-bordered w-100 table-sm">
        <thead>
          <tr>
            <th v-for="(h, i) in headers" :key="i">{{ h.text }}</th>
            <th v-if="actions.length">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  headers: Array, // [{ text: 'Name', value: 'name' }, ...]
  rows: Array,
  actions: { type: Array, default: () => [] },
  handlers: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ responsive: true, pageLength: 20 }) },
  fetchUrl: String,
  totalRecords: Number,
  fetchParams: Object,
})

const emit = defineEmits([
  'sort-change',
  'page-change',
  'length-change',
  'search-change',
])

const table = ref(null)

const formatDate = (dateString) => {
  return dateString
    ? new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
    : ''
}

const formatDateTime = (dateString) => {
  return dateString
    ? new Date(dateString).toLocaleString('en-US', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true })
    : ''
}

const dtColumns = computed(() => {
  const cols = props.headers.map((h) => ({
    data: h.value,
    width: h.width || undefined, // Apply custom width if provided
    render: (val) => renderColumnData(h.value, val),
    orderable: h.sortable !== false, // Ensure that sorting is enabled if 'sortable' is true
  }))

  // Add the actions column if actions are provided
  if (props.actions.length) {
    cols.push({
      data: null,
      orderable: false,
      className: 'text-center',
      width: '80px', // Fixed width for actions
      render: (row) => createActionButtons(row),
    })
  }

  return cols
})

const renderColumnData = (key, val) => {
  // Handle specific columns with custom rendering
  if (key === 'created_at') {
    return formatDate(val) // Format date if key is 'created_at'
  }
  if (key === 'updated_at') {
    return formatDateTime(val) // Format date-time if key is 'updated_at'
  }
  if (key === 'has_variants') {
    // Handle 'has_variants' column with badges
    const badgeClass = val ? 'badge badge-success' : 'badge badge-danger'
    const text = val ? 'Yes' : 'No'
    return `<span class="${badgeClass} text-center">${text}</span>`
  }

  // Default case for rendering
  return val ?? '' // Return value or empty string if null/undefined
}

const createActionButtons = (row) => {
  return `
    <div class="dropdown d-inline-block dropleft">
      <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle shadow-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fal fa-ellipsis-v"></i>
      </button>
      <div class="dropdown-menu">
        ${props.actions
          .map(
            (action) => `
          <a class="dropdown-item" href="javascript:void(0);" data-action="${action}" data-id="${row.id}">
            ${capitalize(action)}
          </a>
        `
          )
          .join('')}
      </div>
    </div>
  `
}

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const fetchData = async (params) => {
  try {
    const { data } = await axios.get(props.fetchUrl, { params })
    return data
  } catch (e) {
    console.error('Fetch error:', e)
    return { data: [], recordsTotal: 0, recordsFiltered: 0 }
  }
}

const initDataTable = () => {
  if (!window.$ || !table.value) return

  // Destroy existing DataTable instance if it exists
  if ($.fn.DataTable.isDataTable(table.value)) {
    $(table.value).DataTable().destroy()
  }

  const dt = $(table.value).DataTable({
    ...props.options,
    processing: true,
    serverSide: true,
    ajax: async (data, callback) => {
      const { column, dir } = data.order[0] ?? {}

      const sortHeader = props.headers[column]
      const sortColumn = sortHeader?.value || 'created_at'
      const sortDirection = dir || 'asc'

      emit('sort-change', { column: sortColumn, direction: sortDirection })
      emit('page-change', Math.ceil(data.start / data.length) + 1)
      emit('length-change', data.length)
      emit('search-change', data.search.value)

      const params = {
        ...props.fetchParams,
        page: Math.ceil(data.start / data.length) + 1,
        limit: data.length,
        sortColumn,
        sortDirection,
        search: data.search.value,
      }

      const { data: responseData, recordsTotal, recordsFiltered } = await fetchData(params)

      callback({
        draw: data.draw,
        recordsTotal,
        recordsFiltered,
        data: responseData,
      })
    },
    columns: dtColumns.value,
    createdRow: (rowEl, rowData) => {
      // Still add event listeners for desktop
      rowEl.querySelectorAll('[data-action]').forEach((el) => {
        el.addEventListener('click', (e) => {
          e.preventDefault()
          const action = el.dataset.action
          props.handlers[action]?.(rowData)
        })
      })
    },
  })

  // Fix for action buttons inside responsive (child) rows
  $(table.value)
    .off('click', '[data-action]')
    .on('click', '[data-action]', function (e) {
      e.preventDefault()

      const action = this.dataset.action
      let tr = $(this).closest('tr')

      // If this is a responsive child row, get the parent row
      if (tr.hasClass('child')) {
        tr = tr.prev()
      }

      const rowData = dt.row(tr).data()

      if (!rowData) {
        console.warn('No row data found for clicked action')
        return
      }

      if (props.handlers[action]) {
        props.handlers[action](rowData)
      } else {
        console.warn(`No handler for action "${action}"`)
      }
    })
}

// Watch for changes in rows and update the DataTable
watch(
  () => props.rows,
  async () => {
    if (table.value) {
      const dataTable = $(table.value).DataTable()
      dataTable.clear() // Clear the existing data
      dataTable.rows.add(props.rows) // Add the new rows
      dataTable.draw() // Redraw the table
    }
  },
  { deep: true }
)

// Initialize DataTable on mount
onMounted(async () => {
  await nextTick()
  initDataTable()
})

// Destroy DataTable on unmount
onUnmounted(() => {
  if (table.value) {
    $(table.value).DataTable().destroy(true)
  }
})
</script>