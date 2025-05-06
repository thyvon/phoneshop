<template>
  <div class="bg-white dark:bg-gray-800 p-6 rounded shadow text-gray-900 dark:text-gray-100">
    <table ref="table" class="table table-bordered w-100 table-sm">
      <thead>
        <tr>
          <th v-for="(h, i) in headers" :key="i">{{ h }}</th>
          <th v-if="actions.length">Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, nextTick } from 'vue'

const props = defineProps({
  headers:     Array,
  rows:        Array,
  actions:     { type: Array, default: () => [] },
  handlers:    { type: Object, default: () => ({}) },
  options:     { type: Object, default: () => ({ responsive: true, pageLength: 20 }) },
})

const table = ref(null)

// normalize header labels to object keys
const keys = props.headers.map(h => h.toString().toLowerCase().replace(/\s+/g,'_'))

const isDateColumn = key => key.endsWith('_at') || key.endsWith('_date')

const formatDate = dateString => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric', month: 'long', day: '2-digit',
    hour: '2-digit', minute: '2-digit', hour12: true
  })
}

// build DataTables columns: map data keys and render dates
const dtColumns = computed(() => {
  const cols = keys.map(key => ({
    data: key,
    render: val => isDateColumn(key) ? formatDate(val) : (val ?? '')
  }))
  if (props.actions.length) {
    cols.push({
      data: null,
      orderable: false,
      render(row) {
        let html = `
          <div class="btn-group">
            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              Actions
            </button>
            <ul class="dropdown-menu">
        `
        for (let a of props.actions) {
          html += `<li><a class="dropdown-item" href="#" data-action="${a}" data-id="${row.id}">${a.charAt(0).toUpperCase() + a.slice(1)}</a></li>`
        }
        html += `</ul></div>`
        return html
      }
    })
  }
  return cols
})

function init() {
  if (!window.$ || !table.value) return
  $(table.value).DataTable({
    ...props.options,
    data: props.rows,
    columns: dtColumns.value,
    createdRow(rowEl, rowData) {
      rowEl.querySelectorAll('[data-action]').forEach(el => {
        el.addEventListener('click', e => {
          e.preventDefault()
          const action = el.dataset.action
          props.handlers[action]?.(rowData)
        })
      })
    }
  })
}

onMounted(async () => {
  await nextTick()
  init()
})

watch(() => props.rows, async () => {
  if (window.$ && table.value) {
    $(table.value).DataTable().destroy()
    await nextTick()
    init()
  }
}, { deep: true })
</script>

<style scoped>
/* any overrides… */
</style>