<template>
    <!-- Modal Structure -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModalLabel">{{ modalTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div v-for="(field, key) in formFields" :key="key" class="form-group">
                <label :for="key">{{ field.label }}</label>
                <input
                  v-if="field.type === 'text' || field.type === 'number'"
                  type="text"
                  class="form-control"
                  :id="key"
                  v-model="formData[key]"
                  :placeholder="field.placeholder"
                  :required="field.required"
                />
                <textarea
                  v-if="field.type === 'textarea'"
                  class="form-control"
                  :id="key"
                  v-model="formData[key]"
                  :placeholder="field.placeholder"
                  :required="field.required"
                ></textarea>
                <input
                  v-if="field.type === 'datetime'"
                  type="datetime-local"
                  class="form-control"
                  :id="key"
                  v-model="formData[key]"
                  :required="field.required"
                />
              </div>
  
              <button type="submit" class="btn btn-primary">{{ modalAction }}</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  
  const props = defineProps({
    modelName: String, // e.g., 'product'
    modelData: Object, // Data to pre-populate when editing (or null for creating)
    isEditing: Boolean, // To check if it's in editing mode
    formFields: Array, // Array of form fields for dynamic generation
    apiUrl: String, // API URL for create/edit action
  })
  
  const emit = defineEmits()
  
  // Form data and logic
  const formData = ref({})
  
  // Compute modal title and action based on props
  const modalTitle = computed(() => (props.isEditing ? `Edit ${props.modelName}` : `Add New ${props.modelName}`))
  const modalAction = computed(() => (props.isEditing ? `Update ${props.modelName}` : `Create ${props.modelName}`))
  
  // Watch for changes in the modelData for pre-filling the form when editing
  watch(() => props.modelData, (newData) => {
    if (props.isEditing) {
      formData.value = { ...newData }
    } else {
      formData.value = {}
    }
  }, { immediate: true })
  
  // Handle form submission
  const submitForm = async () => {
    try {
      const action = props.isEditing ? 'edit' : 'create'
      const response = await axios.post(`${props.apiUrl}/${action}`, formData.value)
  
      // Emit an event for successful save and close modal
      emit('form-saved', response.data)
  
      // Close modal
      $('#formModal').modal('hide')
    } catch (error) {
      console.error('Error saving data:', error)
      // Optionally use SweetAlert2 to show an error
      errorAlert('Error', 'There was a problem saving the data.')
    }
  }
  </script>
  
  <style scoped>
  /* Optional: Customize modal styles here */
  </style>
  