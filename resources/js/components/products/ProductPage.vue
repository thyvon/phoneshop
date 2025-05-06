<template>
  <div>
    <!-- Create Button -->
    <button class="btn btn-success mb-3" @click="() => { resetForm(); showModal() }">
      <i class="fal fa-plus"></i> Create Product
    </button>

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
    />

    <!-- SmartAdmin Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form @submit.prevent="submitForm">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="productModalLabel">{{ isEditing ? 'Edit Product' : 'Create Product' }}</h5>
              <button type="button" class="close text-white" @click="hideModal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label>Name</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>
              <div class="form-group">
                <label>
                  <input v-model="form.has_variants" type="checkbox" />
                  Has Variants
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="hideModal">Cancel</button>
              <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Create' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])
const pageLength = ref(20)

const isEditing = ref(false)
const currentId = ref(null)
const form = ref({
  name: '',
  description: '',
  has_variants: false
})

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

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    has_variants: false
  }
  currentId.value = null
  isEditing.value = false
}

const showModal = () => {
  $('#productModal').modal('show')
  // Set focus to the close button or a specific element inside the modal
  document.querySelector('.modal .close').focus()
}

const hideModal = () => {
  $('#productModal').modal('hide')
  // Restore focus to the button that triggered the modal
  document.querySelector('button.btn-success.mb-3').focus()
}

const submitForm = async () => {
  try {
    if (isEditing.value && currentId.value) {
      await axios.put(`/api/products/${currentId.value}`, form.value)
      alert('Product updated')
    } else {
      await axios.post('/api/products', form.value)
      alert('Product created')
    }
    hideModal()  // Hide modal after submitting
    await fetchProducts()  // Refresh products
  } catch (e) {
    console.error(e)
    alert('Failed to save product')
  }
}

const handleEdit = (p) => {
  isEditing.value = true
  currentId.value = p.id
  form.value = {
    name: p.name,
    description: p.description,
    has_variants: p.has_variants
  }
  showModal()  // Show modal in edit mode
}

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
