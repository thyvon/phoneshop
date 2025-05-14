<template>
  <div>
    <datatable
      :headers="datatableHeaders"
      :fetch-url="datatableFetchUrl"
      :fetch-params="datatableParams"
      :rows="roles"
      :total-records="totalRecords"
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
          <i class="fal fa-plus"></i> Create Role
        </button>
      </template>
    </datatable>

    <!-- Uncomment and implement roleModal if needed -->
    <!-- <RoleModal ref="roleModal" :isEditing="isEditing" @submitted="loadRoles" /> -->
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'
// import RoleModal from './RoleModal.vue'

const roleModal = ref(null)
const roles = ref([])
const totalRecords = ref(0)
const pageLength = ref(10)
const isEditing = ref(false)

const datatableParams = reactive({
  page: 1,
  limit: pageLength.value,
  search: '',
  sortColumn: 'created_at',
  sortDirection: 'desc',
})

// ✅ Structured headers: display text + backend key
const datatableHeaders = [
  { text: 'Name', value: 'name' },
  { text: 'Guard Name', value: 'guard_name' },
  { text: 'Created At', value: 'created_at' },
]

const datatableFetchUrl = '/api/roles'
const datatableActions = ['edit', 'delete']
const datatableOptions = {
  responsive: true,
  pageLength: pageLength.value,
  lengthMenu: [[10, 20, 50, 100, 1000], [10 ,20, 50, 100, 1000]],
}

const openCreateModal = () => {
  isEditing.value = false
  roleModal.value?.show({ isEditing: false })
}

const openEditModal = (role) => {
  isEditing.value = true
  roleModal.value?.show({ isEditing: true, ...role })
}

const handleEdit = openEditModal

const handleDelete = async (role) => {
  if (!confirm(`Delete "${role.name}"?`)) return
  try {
    await axios.delete(`/api/roles/${role.id}`)
    roles.value = roles.value.filter(x => x.id !== role.id)
    alert('Deleted')
  } catch (e) {
    console.error(e)
    alert('Failed to delete')
  }
}

const handleSortChange = ({ column, direction }) => {
  datatableParams.sortColumn = column
  datatableParams.sortDirection = direction
}

const handlePageChange = (page) => {
  datatableParams.page = page
}

const handleLengthChange = (length) => {
  datatableParams.limit = length
}

const handleSearchChange = (search) => {
  datatableParams.search = search
}

const fetchRoles = async () => {
  try {
    const { data } = await axios.get(datatableFetchUrl, { params: datatableParams })
    roles.value = data.data
    totalRecords.value = data.recordsTotal
  } catch (e) {
    console.error('Error fetching roles:', e)
  }
}

watch(datatableParams, fetchRoles, { deep: true })
onMounted(fetchRoles)

const datatableHandlers = {
  edit: handleEdit,
  delete: handleDelete,
}

const loadRoles = fetchRoles
</script>
