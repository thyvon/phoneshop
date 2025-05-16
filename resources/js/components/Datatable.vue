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
  headers: Array,
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
let dataTableInstance = null

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
    width: h.width || undefined,
    render: (val) => renderColumnData(h.value, val),
    orderable: h.sortable !== false,
  }))

  if (props.actions.length) {
    cols.push({
      data: null,
      orderable: false,
      className: 'text-center',
      width: '80px',
      render: (row) => createActionButtons(row),
    })
  }

  return cols
})

const renderColumnData = (key, val) => {
  if (key === 'created_at') {
    return formatDate(val)
  }
  if (key === 'updated_at') {
    return formatDateTime(val)
  }
  if (key === 'has_variants') {
    const badgeClass = val ? 'badge badge-success' : 'badge badge-danger'
    const text = val ? 'Yes' : 'No'
    return `<span class="${badgeClass} text-center">${text}</span>`
  }
  return val ?? ''
}

const createActionButtons = (row) => {
  const encodedRow = encodeURIComponent(JSON.stringify(row))

  return `
    <div class="dropdown d-inline-block dropleft">
      <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle shadow-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fal fa-ellipsis-v"></i>
      </button>
      <div class="dropdown-menu">
        ${props.actions
          .map(
            (action) => `
              <a class="dropdown-item" href="#" data-action="${action}" data-row="${encodedRow}">
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

  if ($.fn.DataTable.isDataTable(table.value)) {
    $(table.value).DataTable().destroy()
  }

  dataTableInstance = $(table.value).DataTable({
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
  })
}

const onActionClick = (e) => {
  const target = e.target.closest('[data-action]')
  if (!target) return

  e.preventDefault()

  const action = target.dataset.action
  const rowEncoded = target.dataset.row

  try {
    const rowData = JSON.parse(decodeURIComponent(rowEncoded))
    if (props.handlers[action]) {
      props.handlers[action](rowData)
    } else {
      console.warn(`No handler found for action: ${action}`)
    }
  } catch (err) {
    console.error('Error parsing row data:', err)
  }
}

watch(
  () => props.rows,
  async () => {
    if (table.value && dataTableInstance) {
      dataTableInstance.clear()
      dataTableInstance.rows.add(props.rows)
      dataTableInstance.draw()
    }
  },
  { deep: true }
)

onMounted(async () => {
  await nextTick()
  initDataTable()

  // Attach event listener once on document
  document.addEventListener('click', onActionClick)
})

onUnmounted(() => {
  if (dataTableInstance) {
    dataTableInstance.destroy(true)
    dataTableInstance = null
  }
  document.removeEventListener('click', onActionClick)
})
</script>