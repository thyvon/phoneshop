<template>
  <div v-if="isVisible" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bootbox-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <!-- Modal Header -->
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="bootbox-modal-label">
            <i class="fal fa-times-circle mr-2"></i> {{ title }}
          </h5>
          <button type="button" class="close" @click="closeModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
          <span><strong>Warning:</strong> {{ message }}</span>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" @click="onConfirm">{{ confirmButton }}</button>
          <button type="button" class="btn btn-default" @click="onCancel">{{ cancelButton }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, defineExpose } from 'vue';

// Props
const props = defineProps({
  title: { type: String, default: 'Confirm Deletion' },
  message: { type: String, default: 'Are you sure you want to delete this item? This action cannot be undone.' },
  confirmButton: { type: String, default: 'Yes' },
  cancelButton: { type: String, default: 'No' },
});

// Emits
const emit = defineEmits(['confirm', 'cancel']);

// Refs
const isVisible = ref(false);

// Methods to handle the modal actions
const closeModal = () => {
  emit('cancel');
  isVisible.value = false; // Hide modal when canceled
};

const onConfirm = () => {
  emit('confirm');
  isVisible.value = false; // Hide modal after confirmation
};

const onCancel = () => {
  emit('cancel');
  isVisible.value = false; // Hide modal when canceled
};

// Method to show the modal
const show = () => {
  isVisible.value = true; // Show the modal
};

// Method to hide the modal (optional, can be used for manual control)
const hide = () => {
  isVisible.value = false; // Hide the modal
};

// Expose the `show` method to the parent component
defineExpose({ show });
</script>

<style scoped>
/* Customize modal header, body, and footer as needed */
.modal-header {
  background-color: #dc3545;
  color: white;
}

.modal-body {
  font-size: 16px;
  color: #721c24;
}

.modal-footer {
  background-color: #f8d7da;
}
</style>
