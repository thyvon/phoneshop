<template>
  <div>
    <!-- DataTable -->
    <datatable
      :headers="['Name', 'Description', 'Has Variants', 'Created At']"
      :rows="products"
      :actions="['edit', 'delete', 'approve']"
      :handlers="{
        edit: handleEdit,
        delete: handleDelete,
        approve: handleApprove
      }"
      :options="{
        responsive: true,
        pageLength: pageLength,
        lengthMenu: [[20, 50, 100, 1000], [20, 50, 100, 1000]]
      }"
    >
      <template #additional-header>
        <!-- Create Product Button -->
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ProductModal from './ProductModal.vue'

const productModal = ref(null)
const products = ref([])
const pageLength = ref(20)
const isEditing = ref(false)

// Fetch products from the API
const fetchProducts = async () => {
  try {
    const { data } = await axios.get('/api/products')
    products.value = data
  } catch (e) {
    console.error('Error fetching products:', e)
  }
}

// Fetch products when the component is mounted
onMounted(() => {
  fetchProducts()
})

// Open Create Modal
const openCreateModal = () => {
  isEditing.value = false
  productModal.value.show({ isEditing: false })  // Pass an empty product for creating
}

// Open the Edit Modal
const openEditModal = (product) => {
  isEditing.value = true
  productModal.value.show({ isEditing: true, ...product })  // Pass the product for editing
}

// Handle Edit Action
const handleEdit = (p) => openEditModal(p)

// Handle Delete Action
const handleDelete = async (p) => {
  if (!confirm(`Delete "${p.name}"?`)) return
  try {
    await axios.delete(`/api/products/${p.id}`)
    products.value = products.value.filter(x => x.id !== p.id)
    alert('Deleted')
  } catch (e) {
    console.error(e)
    alert('Failed to delete')
  }
}

// Handle Approve Action (Add implementation as needed)
const handleApprove = (p) => {
  console.log('Approving', p)
}

// Refresh products after submit
const loadProducts = () => {
  fetchProducts()  // Reload the products after submitting the form in the modal
}
</script>

<style scoped>
/* Add any styles you may need for the parent component */
</style>
