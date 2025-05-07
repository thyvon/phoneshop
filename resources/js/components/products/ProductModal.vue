<template>
  <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form @submit.prevent="submit">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="productModalLabel">{{ isEditing ? 'Edit Product' : 'Create Product' }}</h5>
            <button type="button" class="close text-white" @click="hide" aria-label="Close">
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
            <button type="button" class="btn btn-secondary" @click="hide">Cancel</button>
            <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, defineExpose } from 'vue'

const emit = defineEmits(['submitted'])

const form = reactive({
  name: '',
  description: '',
  has_variants: false
})
const isEditing = ref(false)
const currentId = ref(null)

function show(product = null) {
  if (product) {
    form.name = product.name
    form.description = product.description
    form.has_variants = product.has_variants
    currentId.value = product.id
    isEditing.value = true
  } else {
    form.name = ''
    form.description = ''
    form.has_variants = false
    currentId.value = null
    isEditing.value = false
  }
  $('#productModal').modal('show')
  document.querySelector('.modal .close').focus()
}

function hide() {
  $('#productModal').modal('hide')
  document.querySelector('button.btn-success.mb-3')?.focus()
}

async function submit() {
  emit('submitted', {
    ...form,
    id: currentId.value,
    isEditing: isEditing.value
  })
  hide()
}

defineExpose({ show, hide })
</script>
