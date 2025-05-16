<template>
  <Teleport to="body">
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
            <button type="button" class="close text-danger" @click="close" aria-label="Close">
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
  </Teleport>
</template>

<script setup>
import { ref, computed, defineExpose } from 'vue'

const props = defineProps({
  id: { type: String, required: true },
  title: { type: String, default: '' },
  size: { type: String, default: 'xl' },
  headerClass: { type: String, default: 'bg-secondary text-white' }
})

const modalRef = ref(null)

const dialogClass = computed(() => {
  const sizeMap = {
    sm: 'modal-sm',
    md: '',
    lg: 'modal-lg',
    xl: 'modal-xl'
  }
  return `modal-dialog ${sizeMap[props.size] || 'modal-xl'} modal-dialog-centered modal-dialog-scrollable`
})

const show = () => {
  document.body.classList.remove('mobile-nav-on')
  $(`#${props.id}`).modal('show')
}

const close = () => {
  $(`#${props.id}`).modal('hide')
}

defineExpose({ show, close })
</script>

<style scoped>
.modal-header-slim {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  height: 3rem;
}

.modal-header .close {
  font-size: 1.5rem;
  margin-top: -0.25rem;
}
</style>
