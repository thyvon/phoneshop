<template>
    <div
      class="modal fade"
      :id="id"
      tabindex="-1"
      role="dialog"
      :aria-labelledby="id + '-label'"
      aria-hidden="true"
      ref="modalRef"
    >
      <div :class="dialogClass" role="document">
        <div class="modal-content border-0 shadow-lg">
          <!-- Header -->
          <div
            class="modal-header d-flex align-items-center"
            :class="['modal-header-slim', headerClass]"
          >
            <h5 class="modal-title font-weight-bold mb-0" :id="id + '-label'">{{ title }}</h5>
            <button type="button" class="close text-white" @click="close" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
  
          <!-- Body -->
          <div class="modal-body">
            <slot name="body" />
          </div>
  
          <!-- Footer -->
          <div class="modal-footer bg-light">
            <slot name="footer">
              <button type="button" class="btn btn-secondary" @click="close">Close</button>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, defineExpose } from 'vue'
  
  // Props
  const props = defineProps({
    id: { type: String, required: true },
    title: { type: String, default: '' },
    size: { type: String, default: 'xl' }, // sm, md, lg, xl
    headerClass: { type: String, default: 'bg-secondary text-white' }
  })
  
  // Refs
  const modalRef = ref(null)
  
  // Classes
  const dialogClass = computed(() => {
    const sizes = ['sm', 'md', 'lg', 'xl']
    const validSize = sizes.includes(props.size) ? props.size : 'xl'
    return `modal-dialog modal-${validSize}`
  })
  
  // Show/hide functions
  const show = () => {
    $(`#${props.id}`).modal('show')
  }
  
  const close = () => {
    $(`#${props.id}`).modal('hide')
  }
  
  defineExpose({ show, close })
  </script>
  
  <style scoped>
  /* Slim down the header by reducing the padding and font size */
  .modal-header-slim {
    padding: 0.5rem 1rem;  /* Reduced padding */
    font-size: 0.875rem;   /* Reduced font size */
    height: 3rem;          /* Control the header height */
  }
  
  .modal-header .close {
    font-size: 1.5rem;  /* Adjust close button size if necessary */
    margin-top: -0.25rem;  /* Align close button with title */
  }
  </style>
  