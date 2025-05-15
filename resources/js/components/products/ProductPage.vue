<template>
  <div>
    <datatable
      :headers="datatableHeaders"
      :fetch-url="datatableFetchUrl"
      :fetch-params="datatableParams"
      :rows="products"
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
          <i class="fal fa-plus"></i> Create Product
        </button>
      </template>
    </datatable>

    <!-- Product Modal -->
    <ProductModal ref="productModal" :isEditing="isEditing" @submitted="loadProducts" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'
import ProductModal from './ProductModal.vue'
import { confirmAction, showAlert } from '@/utils/bootbox';

// Refs and reactive state
const productModal = ref(null)
const products = ref([])
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

// Derived data for the datatable component
const datatableHeaders = [
  { text: 'Name', value: 'name', width: '20%', sortable: true },
  { text: 'Description', value: 'description', width: '30%', sortable: true },
  { text: 'Has Variants', value: 'has_variants', width: '10%', sortable: false },
  { text: 'Created', value: 'created_at', width: '20%', sortable: true },
  { text: 'Updated', value: 'updated_at', width: '20%', sortable: false }
];
const datatableFetchUrl = '/api/products'
const datatableActions = ['edit', 'delete', 'approve']
const datatableOptions = {
  responsive: false,
  pageLength: pageLength.value,
  lengthMenu: [[10, 20, 50, 100, 1000], [10 ,20, 50, 100, 1000]],
}

// Modal handling
const openCreateModal = () => {
  isEditing.value = false
  productModal.value.show({ isEditing: false })
}

const openEditModal = async (product) => {
  try {
    const response = await axios.get(`/api/products/${product.id}/edit`)
    const fullProduct = response.data.product

    isEditing.value = true
    productModal.value.show({
      isEditing: true,
      ...fullProduct
    })
  } catch (error) {
    console.error('Failed to fetch product data for editing:', error)
  }
}

// Action handlers
const handleEdit = openEditModal

const handleDelete = async (product) => {
  const confirmed = await confirmAction(
    `Delete "${product.name}"?`,
    '<strong>Warning:</strong> This action cannot be undone!'
  )

  if (!confirmed) return

  try {
    await axios.delete(`/api/products/${product.id}`)
    products.value = products.value.filter(x => x.id !== product.id)

    showAlert('Deleted', `"${product.name}" was deleted successfully.`, 'success')
  } catch (e) {
    console.error(e)
    showAlert('Failed to delete', e.response?.data?.message || 'Something went wrong.', 'danger')
  }
}

const handleApprove = (product) => {
  console.log('Approving', product)
}

// Event handlers for the datatable
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

// Fetch products from the API
const fetchProducts = async () => {
  try {
    const { data } = await axios.get(datatableFetchUrl, { params: datatableParams })
    products.value = data.data
    totalRecords.value = data.recordsTotal
  } catch (e) {
    console.error('Error fetching products:', e)
  }
}

// Auto-fetch when any param changes
watch(datatableParams, fetchProducts, { deep: true })

// Lifecycle hook
onMounted(fetchProducts)

// Handlers for the datatable
const datatableHandlers = {
  edit: handleEdit,
  delete: handleDelete,
  approve: handleApprove,
}

// Refresh product list after modal actions
const loadProducts = fetchProducts
</script>
