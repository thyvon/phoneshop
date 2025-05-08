<template>
    <div
      class="modal fade"
      tabindex="-1"
      role="dialog"
      aria-labelledby="bootbox-modal-label"
      aria-hidden="true"
      ref="modalRef"
    >
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
            <button
              type="button"
              class="btn btn-danger"
              @click="onConfirm"
            >
              {{ confirmButton }}
            </button>
            <button
              type="button"
              class="btn btn-default"
              @click="onCancel"
            >
              {{ cancelButton }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, defineProps, defineEmits } from 'vue';
  
  // Props
  const props = defineProps({
    title: String,
    message: String,
    confirmButton: String,
    cancelButton: String,
  });
  
  // Emits
  const emit = defineEmits(['confirm', 'cancel']);
  
  // Refs
  const modalRef = ref(null);
  
  // Methods to handle the modal actions
  const closeModal = () => {
    emit('cancel');
  };
  
  const onConfirm = () => {
    emit('confirm');
  };
  
  const onCancel = () => {
    emit('cancel');
  };
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
  