<template>
  <BaseModal
    v-model="showModal"
    id="brandModal"
    :title="isEditing ? 'Edit Brand' : 'Create Brand'"
    size="lg"
  >
    <template #body>
      <form @submit.prevent="submitForm">
        <div class="card border shadow-sm mb-0">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold">Brand Information</h6>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  required
                />
              </div>
              <div class="form-group col-md-6">
                <label>Short Code</label>
                <input
                  v-model="form.code"
                  type="text"
                  class="form-control"
                  required
                />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-12">
                <label>Description</label>
                <input
                  v-model="form.description"
                  type="text"
                  class="form-control"
                  rows="2"
                />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="isActive"
                    v-model="form.is_active"
                  />
                  <label class="custom-control-label" for="isActive"
                    >Active</label
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </template>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="hideModal">Cancel</button>
      <button
        type="submit"
        class="btn btn-primary"
        @click="submitForm"
        :disabled="isSubmitting"
      >
        <span v-if="isSubmitting" class="spinner-border spinner-border-sm mr-1"></span>
        {{ isEditing ? 'Update' : 'Create' }}
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, nextTick, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/reusable/BaseModal.vue'
import { showAlert } from '@/utils/bootbox'
import { initSelect2, destroySelect2 } from '@/utils/select2'

const props = defineProps({ isEditing: Boolean })
const emit = defineEmits(['submitted'])

const showModal = ref(false)
const isSubmitting = ref(false)
const brands = ref([])
const form = ref({
  id: null,
  name: '',
  code: '',
  description: '',
  is_active: true,
})

const fetchBrands = async () => {
  try {
    const response = await axios.get('/api/brands')
    const brandList = Array.isArray(response.data) ? response.data : response.data.data
    brands.value = brandList
  } catch (err) {
    console.error('Failed to load brands:', err)
  }
}

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    code: '',
    description: '',
    is_active: true,
  }
}

const show = async (brand = null) => {
  resetForm()
  await fetchBrands()
  if (brand) {
    form.value = {
      id: brand.id,
      name: brand.name ?? '',
      code: brand.code ?? '',
      description: brand.description ?? '',
      is_active: brand.is_active !== undefined ? !!brand.is_active : true,
    }
  }
  await nextTick()
  showModal.value = true
}

const hideModal = () => {
  showModal.value = false
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    const method = props.isEditing ? 'put' : 'post'
    const url = props.isEditing && form.value.id
      ? `/api/brands/${form.value.id}`
      : '/api/brands'

    const payload = {
      name: form.value.name?.toString().trim() ?? '',
      code: form.value.code?.toString().trim() ?? '',
      description: form.value.description?.toString().trim() ?? '',
      is_active: !!form.value.is_active,
    }
    console.log('payload', payload)

    await axios[method](url, payload)
    emit('submitted')
    hideModal()
    showAlert('Success', `Brand ${props.isEditing ? 'updated' : 'created'} successfully.`, 'success')
  } catch (err) {
    console.error('Submit error:', err.response?.data || err)
    showAlert('Error', err.response?.data?.message || err.message || 'Failed to save brand.', 'danger')
  } finally {
    isSubmitting.value = false
  }
}

defineExpose({ show })

onMounted(fetchBrands)
</script>