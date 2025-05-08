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
    <ProductModal
      ref="productModal"
      :isEditing="isEditing"
      :form="form"
      @submitted="loadProducts"
    />
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
const form = ref({})

// Load products from backend
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

// Create
const openCreateModal = () => {
  isEditing.value = false
  form.value = {
    name: '',
    sku: '',
    description: '',
    has_variants: false,
    price: null,
    stock: null,
    variants: []
  }
  productModal.value.show()
}

// Edit
const openEditModal = (product) => {
  isEditing.value = true
  form.value = { ...product }
  productModal.value.show()
}

// Action handlers
const handleEdit = (product) => openEditModal(product)

const handleDelete = async (product) => {
  if (!confirm(`Delete "${product.name}"?`)) return
  try {
    await axios.delete(`/api/products/${product.id}`)
    products.value = products.value.filter(p => p.id !== product.id)
    alert('Deleted')
  } catch (e) {
    console.error(e)
    alert('Failed to delete')
  }
}

const handleApprove = (product) => {
  console.log('Approving product:', product)
}

// Reload after submit
const loadProducts = () => {
  fetchProducts()
}
</script>
