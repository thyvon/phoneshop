<template>
  <BaseModal
    v-model="showModal"
    id="categoryModal"
    :title="isEditing ? 'Edit Category' : 'Create Category'"
    size="lg"
  >
    <template #body>
      <form @submit.prevent="submitForm">
        <div class="card border shadow-sm mb-0">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold">Category Information</h6>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Name</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>
              <div class="form-group col-md-6">
                <label>Short Code</label>
                <input v-model="form.code" type="text" class="form-control" required />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-12">
                <label>Description</label>
                <textarea v-model="form.description" class="form-control" rows="2"></textarea>
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
                  <label class="custom-control-label" for="isActive">Active</label>
                </div>
              </div>
              <div class="form-group col-md-4">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="subTaxonomy"
                    v-model="form.sub_taxonomy"
                  />
                  <label class="custom-control-label" for="subTaxonomy">Is Sub Taxonomy</label>
                </div>
              </div>
              <div class="form-group col-md-4">
                <label>Parent Category</label>
                <select v-model="form.parent_id" class="form-control">
                  <option :value="null">-- None --</option>
                  <option
                    v-for="cat in mainCategories"
                    :key="cat.id"
                    :value="cat.id"
                  >
                    {{ cat.name }}
                  </option>
                </select>
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
import { ref, nextTick, computed, onMounted } from 'vue'
import axios from 'axios'
import BaseModal from '../BaseModal.vue'
import { showAlert } from '@/utils/bootbox'

const props = defineProps({ isEditing: Boolean })
const emit = defineEmits(['submitted'])

const showModal = ref(false)
const isSubmitting = ref(false)
const categories = ref([])
const form = ref({
  id: null,
  name: '',
  code: '',
  description: '',
  is_active: true,
  sub_taxonomy: false,
  parent_id: null
})

// Computed for main categories (not sub-taxonomy)
const mainCategories = computed(() =>
  categories.value.filter(cat => !cat.sub_taxonomy)
)

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories')
    const categoryList = Array.isArray(response.data) ? response.data : response.data.data
    categories.value = categoryList
  } catch (err) {
    console.error('Failed to load categories:', err)
  }
}

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    code: '',
    description: '',
    is_active: true,
    sub_taxonomy: false,
    parent_id: null
  }
}

const show = async (category = null) => {
  resetForm()
  await fetchCategories()
  if (category) {
    form.value = {
      id: category.id,
      name: category.name ?? '',
      code: category.code ?? '',
      description: category.description ?? '',
      is_active: category.is_active !== undefined ? !!category.is_active : true,
      sub_taxonomy: category.sub_taxonomy !== undefined ? !!category.sub_taxonomy : false,
      parent_id: category.parent_id ?? null
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
      ? `/api/categories/${form.value.id}`
      : '/api/categories'

    const payload = {
      name: form.value.name?.toString().trim() ?? '',
      code: form.value.code?.toString().trim() ?? '',
      description: form.value.description?.toString().trim() ?? '',
      is_active: !!form.value.is_active,
      sub_taxonomy: !!form.value.sub_taxonomy,
      parent_id: form.value.parent_id === null || form.value.parent_id === '' ? null : form.value.parent_id
    }

    await axios[method](url, payload)
    emit('submitted')
    hideModal()
    showAlert('Success', `Category ${props.isEditing ? 'updated' : 'created'} successfully.`, 'success')
  } catch (err) {
    console.error('Submit error:', err.response?.data || err)
    showAlert('Error', err.response?.data?.message || err.message || 'Failed to save category.', 'danger')
  } finally {
    isSubmitting.value = false
  }
}

defineExpose({ show })

onMounted(fetchCategories)
</script>