<template>
    <div class="modal fade" tabindex="-1" ref="modalEl" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h5>
            <button type="button" class="btn-close" aria-label="Close" @click="close"></button>
          </div>
          <form @submit.prevent="onSubmit">
            <div class="modal-body">
              <input v-if="isEdit" type="hidden" v-model="form.id" />
              <div class="mb-3">
                <label for="productName" class="form-label">Name</label>
                <input
                  id="productName"
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="productDescription" class="form-label">Description</label>
                <textarea
                  id="productDescription"
                  v-model="form.description"
                  class="form-control"
                  rows="3"
                ></textarea>
              </div>
              <div class="form-check mb-3">
                <input
                  v-model="form.has_variants"
                  class="form-check-input"
                  type="checkbox"
                  id="hasVariants"
                />
                <label class="form-check-label" for="hasVariants">
                  Has Variants
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
              <button type="submit" class="btn btn-primary">
                {{ isEdit ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, watch, onMounted, nextTick, onBeforeUnmount } from 'vue'
  
  const props = defineProps({
    modelValue: { type: Boolean, default: false },
    product: { type: Object, default: null }
  })
  const emit = defineEmits(['update:modelValue', 'save'])
  
  const form = ref({ id: null, name: '', description: '', has_variants: false })
  const isEdit = ref(false)
  let modalInstance = null
  const modalEl = ref(null)
  
  // initialize Bootstrap modal once
  onMounted(async () => {
    await nextTick()
    if (modalEl.value) {
      modalInstance = new bootstrap.Modal(modalEl.value, { backdrop: 'static' })
      // when user closes via UI, sync v-model
      modalEl.value.addEventListener('hidden.bs.modal', () => {
        emit('update:modelValue', false)
      })
    }
  })
  
  onBeforeUnmount(() => {
    if (modalInstance) {
      modalInstance.dispose()
    }
  })
  
  // watch open/close
  watch(
    () => props.modelValue,
    async visible => {
      if (visible) {
        // sync product data into form
        if (props.product) {
          form.value = { ...props.product }
          isEdit.value = true
        } else {
          form.value = { id: null, name: '', description: '', has_variants: false }
          isEdit.value = false
        }
        modalInstance.show()
      } else if (modalInstance) {
        modalInstance.hide()
      }
    }
  )
  
  function close() {
    emit('update:modelValue', false)
  }
  
  function onSubmit() {
    // emit a copy
    emit('save', { ...form.value })
  }
  </script>
  
  <style scoped>
  /* ensure modal z-index above DataTable */
  .modal {
    z-index: 1050;
  }
  </style>
  