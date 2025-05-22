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
                <select
                  ref="parentSelect"
                  v-model="form.parent_id"
                  class="form-control"
                  :disabled="!form.sub_taxonomy"
                >
                  <option :value="''">-- None --</option>
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
import { ref, nextTick, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/reusable/BaseModal.vue'
import { showAlert } from '@/utils/bootbox'
import { initSelect2, destroySelect2 } from '@/utils/select2'

const props = defineProps({ isEditing: Boolean })
const emit = defineEmits(['submitted'])

const showModal = ref(false)
const isSubmitting = ref(false)
const categories = ref([])
const parentSelect = ref(null)
const form = ref({
  id: null,
  name: '',
  code: '',
  description: '',
  is_active: true,
  sub_taxonomy: false,
  parent_id: null
})

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
  await nextTick()
  initParentSelect2()
}

const hideModal = () => {
  showModal.value = false
  destroyParentSelect2()
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    // --- Ensure parent_id is always null or a valid value ---
    if (form.value.parent_id === '' || form.value.parent_id === undefined) {
      form.value.parent_id = null
    }
    if (Array.isArray(form.value.parent_id)) {
      form.value.parent_id = form.value.parent_id.length ? form.value.parent_id[0] : null
    }

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
      parent_id:
        form.value.parent_id === null ||
        form.value.parent_id === '' ||
        form.value.parent_id === undefined ||
        form.value.parent_id === 'null' ||
        Number(form.value.parent_id) === 0
          ? null
          : Number(form.value.parent_id)
    }
        console.log('payload', payload)

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

// --- Select2 Integration ---
function initParentSelect2() {
  if (!parentSelect.value) return
  const $modal = window.$('#categoryModal')
  initSelect2(
    parentSelect.value,
    {
      placeholder: '-- None --',
      allowClear: true,
      width: '100%',
      dropdownParent: $modal
    },
    val => {
      form.value.parent_id = (val === '' || val === null || val === 'null') ? null : Number(val)
    }
  )
  // Set initial value after options are rendered
  nextTick(() => {
    // Check if the current parent_id exists in the options
    const exists = mainCategories.value.some(cat => cat.id == form.value.parent_id)
    if (exists) {
      window.$(parentSelect.value).val(form.value.parent_id).trigger('change')
    } else {
      window.$(parentSelect.value).val('').trigger('change')
      form.value.parent_id = null
    }
  })
}

function destroyParentSelect2() {
  if (parentSelect.value) {
    destroySelect2(parentSelect.value)
    window.$(parentSelect.value).off('change')
  }
}

// Watch for modal open/close to init/destroy select2
watch(showModal, async (val) => {
  if (val) {
    await nextTick()
    initParentSelect2()
  } else {
    destroyParentSelect2()
  }
})

// Watch for sub_taxonomy changes to clear parent_id and reset Select2 if unchecked
watch(() => form.value.sub_taxonomy, (val) => {
  if (!val) {
    form.value.parent_id = null
    nextTick(() => {
      if (parentSelect.value) {
        window.$(parentSelect.value).val('').trigger('change')
      }
    })
  }
})

// Watch for category list changes to re-init select2
watch(mainCategories, async () => {
  await nextTick()
  destroyParentSelect2()
  initParentSelect2()
})

defineExpose({ show })

onMounted(fetchCategories)
</script>