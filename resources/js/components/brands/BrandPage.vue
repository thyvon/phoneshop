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
          <i class="fal fa-plus"></i> Create Brand
        </button>
      </template>
      <template #cell-is_active="{ value }">
        <span :class="value ? 'badge badge-success' : 'badge badge-secondary'">
          {{ value ? 'Active' : 'Inactive' }}
        </span>
      </template>
    </datatable>

    <!-- Brand Modal -->
    <BrandModal
      ref="brandModal"
      :isEditing="isEditing"
      @submitted="reloadDatatable"
    />
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'
import BrandModal from './BrandModal.vue'
import { confirmAction, showAlert } from '@/utils/bootbox'

// Refs and state
const datatableRef = ref(null)
const brandModal = ref(null)
const isEditing = ref(false)
const pageLength = ref(10)

// Datatable configuration
const datatableParams = reactive({
  sortColumn: 'created_at',
  sortDirection: 'desc',
  // Optionally add: page: 1, limit: 10, search: ''
})

const datatableHeaders = [
  { text: 'Name', value: 'name', width: '20%', sortable: true },
  { text: 'Code', value: 'code', width: '15%', sortable: true },
  { text: 'Description', value: 'description', width: '25%', sortable: true },
  { text: 'Active', value: 'is_active', width: '10%', sortable: true },
  { text: 'Created', value: 'created_at', width: '10%', sortable: true },
  { text: 'Updated', value: 'updated_at', width: '10%', sortable: false }
]

const datatableFetchUrl = '/api/brands'
const datatableActions = ['edit', 'delete']
const datatableOptions = {
  responsive: true,
  pageLength: pageLength.value,
  lengthMenu: [[10, 20, 50, 100, 1000], [10, 20, 50, 100, 1000]],
}

// Modal handling
const openCreateModal = () => {
  isEditing.value = false
  brandModal.value.show({ isEditing: false })
}

const openEditModal = async (brand) => {
  try {
    const response = await axios.get(`/api/brands/${brand.id}/edit`)
    const fullBrand = response.data.data || {}
    isEditing.value = true
    brandModal.value.show({
      isEditing: true,
      ...fullBrand
    })
  } catch (error) {
    console.error('Failed to fetch brand data for editing:', error)
    showAlert('Error', 'Failed to load brand data for editing.', 'danger')
  }
}

// Action handlers
const handleEdit = openEditModal

const handleDelete = async (brand) => {
  const confirmed = await confirmAction(
    `Delete "${brand.name}"?`,
    '<strong>Warning:</strong> This action cannot be undone!'
  )
  if (!confirmed) return

  try {
    await axios.delete(`/api/brands/${brand.id}`)
    showAlert('Deleted', `"${brand.name}" was deleted successfully.`, 'success')
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
}

const handlePageChange = (page) => {
  // Uncomment and implement if your datatable supports pagination
  // datatableParams.page = page
}

const handleLengthChange = (length) => {
  // Uncomment and implement if your datatable supports page length
  // datatableParams.limit = length
}

const handleSearchChange = (search) => {
  datatableParams.search = search
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
</script>