<template>
  <div>
    <datatable
      ref="datatableRef"
      :headers="datatableHeaders"
      :fetch-url="datatableFetchUrl"
      :fetch-params="datatableParams"
      :actions="datatableActions"
      :handlers="datatableHandlers"
      :options="datatableOptions"
      @sort-change="handleSortChange"
      @page-change="handlePageChange"
      @length-change="handleLengthChange"
      @search-change="handleSearchChange"
    >
      <template #additional-header>
        <button class="btn btn-success" @click="openCreateModal">
          <i class="fal fa-plus"></i> Create Category
        </button>
      </template>
    </datatable>

    <!-- Category Modal -->
    <CategoryModal ref="categoryModal" :isEditing="isEditing" @submitted="reloadDatatable" />
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'
import CategoryModal from './CategoryModal.vue'
import { confirmAction, showAlert } from '@/utils/bootbox'

// Refs and reactive state
const datatableRef = ref(null)
const categoryModal = ref(null)
const isEditing = ref(false)

// Parameters for datatable fetch - reactive to enable updates
const datatableParams = reactive({
  sortColumn: 'created_at',
  sortDirection: 'desc',
  page: 1,
  limit: 10,
  search: ''
})

const datatableHeaders = [
  { text: 'Name', value: 'name', width: '20%', sortable: true },
  { text: 'Description', value: 'description', width: '25%', sortable: true },
  { text: 'Active', value: 'is_active', width: '10%', sortable: true },
  { text: 'Sub Taxonomy', value: 'sub_taxonomy', width: '10%', sortable: true },
  { text: 'Parent Category', value: 'parent_name', width: '15%', sortable: false },
  { text: 'Created', value: 'created_at', width: '10%', sortable: true },
  { text: 'Updated', value: 'updated_at', width: '10%', sortable: false }
]

const datatableFetchUrl = '/api/categories'
const datatableActions = ['edit', 'delete']

const datatableOptions = {
  responsive: true,
  pageLength: datatableParams.limit,
  lengthMenu: [
    [10, 20, 50, 100, 1000],
    [10, 20, 50, 100, 1000]
  ]
}

// Modal handlers
const openCreateModal = () => {
  isEditing.value = false
  categoryModal.value.show()
}

const openEditModal = async (category) => {
  try {
    const response = await axios.get(`/api/categories/${category.id}/edit`)
    const fullCategory = response.data.category || response.data.categories || {}

    isEditing.value = true
    categoryModal.value.show(fullCategory)
  } catch (error) {
    console.error('Failed to fetch category data for editing:', error)
    showAlert('Error', 'Failed to load category data for editing.', 'danger')
  }
}

// Action handlers for datatable
const handleEdit = openEditModal

const handleDelete = async (category) => {
  const confirmed = await confirmAction(
    `Delete "${category.name}"?`,
    '<strong>Warning:</strong> This action cannot be undone!'
  )
  if (!confirmed) return

  try {
    await axios.delete(`/api/categories/${category.id}`)
    showAlert('Deleted', `"${category.name}" was deleted successfully.`, 'success')
    reloadDatatable()
  } catch (e) {
    console.error(e)
    showAlert('Failed to delete', e.response?.data?.message || 'Something went wrong.', 'danger')
  }
}

// Datatable event handlers
const handleSortChange = ({ column, direction }) => {
  datatableParams.sortColumn = column
  datatableParams.sortDirection = direction
  reloadDatatable()
}

const handlePageChange = (page) => {
  datatableParams.page = page
  reloadDatatable()
}

const handleLengthChange = (length) => {
  datatableParams.limit = length
  reloadDatatable()
}

const handleSearchChange = (search) => {
  datatableParams.search = search
  datatableParams.page = 1 // reset page on search
  reloadDatatable()
}

// Map actions to handlers
const datatableHandlers = {
  edit: handleEdit,
  delete: handleDelete
}

// Reload datatable data
const reloadDatatable = () => {
  datatableRef.value?.reload()
}

// Optional: watch params and auto reload (if your datatable doesn't emit events for all changes)
// watch(datatableParams, () => {
//   reloadDatatable()
// }, { deep: true })

</script>
