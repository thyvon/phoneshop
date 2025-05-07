<template>
  <div v-if="$slots['additional-header']" class="datatable-header">
    <slot name="additional-header"></slot>
  </div>
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

const formatDateTime = dateString => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric', month: 'long', day: '2-digit',
    hour: '2-digit', minute: '2-digit', hour12: true
  })
}

const formatDate = dateString => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

// build DataTables columns: map data keys and render specific date formats
const dtColumns = computed(() => {
  const cols = keys.map(key => ({
    data: key,
    render: val => {
      if (key === 'created_at') return formatDate(val)
      if (key === 'updated_at') return formatDateTime(val)
      return val ?? ''
    }
  }))

  if (props.actions.length) {
    cols.push({
      data: null,
      orderable: false,
      className: 'text-center',
      render(row) {
        let html = `
          <div class='dropdown d-inline-block dropleft'>
            <a href='#' class='btn btn-sm btn-icon btn-outline-primary rounded-circle shadow-0' data-toggle='dropdown' aria-expanded='true' title='More options'>
              <i class="fal fa-ellipsis-v"></i>
            </a>
            <div class='dropdown-menu'>
        `
        for (let a of props.actions) {
          html += `<a class='dropdown-item' href='javascript:void(0);' data-action="${a}" data-id="${row.id}">${a.charAt(0).toUpperCase() + a.slice(1)}</a>`
        }
        html += `</div></div>`
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