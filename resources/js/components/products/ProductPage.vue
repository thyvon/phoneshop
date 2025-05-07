<template>
  <div>
    <!-- DataTable -->
    <datatable
      :headers="['Name', 'Description', 'Has Variants', 'Created At']"
      :rows="products"
      :actions="['edit', 'delete', 'approve']"
      :handlers="{ edit: handleEdit, delete: handleDelete, approve: handleApprove }"
      :options="{
        responsive: true,
        pageLength: pageLength,
        lengthMenu: [[20, 50, 100, 1000], [20, 50, 100, 1000]]
      }"
    >
    <template #additional-header>
        <button class="btn btn-success" @click="openCreateModal">
          <i class="fal fa-plus"></i> Create Product
        </button>
      </template>
  </datatable>

    <!-- Product Modal -->
    <ProductModal ref="productModal" @submitted="onProductSubmitted" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ProductModal from './ProductModal.vue'

const productModal = ref(null)
const products = ref([])
const pageLength = ref(20)

const fetchProducts = async () => {
  try {
    const { data } = await axios.get('/api/products')
    products.value = data
  } catch (e) {
    console.error('Error fetching products:', e)
  }
}

onMounted(() => {
  fetchProducts()
})

// Open the Create Modal
const openCreateModal = () => productModal.value.show()

// Open the Edit Modal
const openEditModal = (product) => productModal.value.show(product)

// Handle Product Submit (Create/Update)
const onProductSubmitted = async (product) => {
  try {
    if (product.isEditing) {
      await axios.put(`/api/products/${product.id}`, product)
      alert('Product updated')
    } else {
      await axios.post('/api/products', product)
      alert('Product created')
    }
    await fetchProducts()
  } catch (e) {
    console.error(e)
    alert('Failed to save product')
  }
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

// Handle Approve Action
const handleApprove = async (p) => {
  try {
    await axios.post(`/api/products/${p.id}/approve`)
    alert('Approved')
  } catch (e) {
    console.error(e)
    alert('Failed to approve')
  }
}
</script>
