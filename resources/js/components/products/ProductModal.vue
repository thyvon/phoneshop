<template>
  <BaseModal
    id="productModal"
    :title="isEditing ? 'Edit Product' : 'Create Product'"
    :size="'xl'"
    ref="modal"
  >
    <template #body>
    <form @submit.prevent="submitForm">

      <!-- Basic Info -->
      <div class="card border shadow-sm mb-4">
        <div class="card-header py-2 bg-light">
          <h6 class="mb-0 font-weight-bold">Basic Information</h6>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Name</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
          </div>

          <!-- Toggle Variants -->
          <div class="form-check mt-2">
            <input v-model="form.has_variants" type="checkbox" class="form-check-input" id="hasVariants" />
            <label class="form-check-label" for="hasVariants">Has Variants</label>
          </div>
        </div>
      </div>

      <!-- Attribute Selection -->
      <div v-if="form.has_variants" class="card border border-primary shadow-sm mb-4">
        <div class="card-header py-2 bg-light">
          <h6 class="mb-0 font-weight-bold">Attribute Selection</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <template v-for="(attr, index) in availableAttributes" :key="index">
              <div v-if="attr?.values?.length > 0" class="col-md-6 col-lg-2 mb-3 px-1">
                <div class="card border h-100">
                  <div class="card-header py-2">
                    <h6 class="mb-0 font-weight-bold">{{ attr.name }}</h6>
                  </div>
                  <div class="card-body py-2 px-3">
                    <div class="form-group mb-0">
                      <div
                        v-for="val in attr.values"
                        :key="val.id"
                        class="custom-control custom-checkbox"
                      >
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="`attr-${attr.id}-val-${val.id}`"
                          :checked="selectedAttributes[attr.id]?.includes(val.id)"
                          @change="toggleAttribute(attr.id, val.id)"
                        />
                        <label
                          class="custom-control-label"
                          :for="`attr-${attr.id}-val-${val.id}`"
                        >
                          {{ val.value }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Variants or Single Product -->
      <div class="card border shadow-sm mb-4">
        <div
          :class="[
            'card-header py-2',
            form.has_variants ? 'bg-warning-50 text-warning' : 'bg-success-50 text-success'
          ]"
        >
          <h6 class="mb-0">
            {{ form.has_variants ? 'Variant Products' : 'Stock & Price' }}
          </h6>
        </div>
        <div class="card-body">
          <template v-if="form.has_variants">
            <div class="table-responsive">
              <table class="table table-striped table-bordered table-hover table-sm">
                <thead class="thead-light">
                  <tr>
                    <th>Variant</th>
                    <th>Price</th>
                    <th>Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(variant, index) in generatedVariants" :key="index">
                    <td class="align-middle">{{ variant.description }}</td>
                    <td>
                      <input
                        v-model="variant.price"
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Price"
                      />
                    </td>
                    <td>
                      <input
                        v-model="variant.stock"
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Stock"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <template v-else>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Price</label>
                <input v-model="form.price" type="number" class="form-control" />
              </div>
              <div class="form-group col-md-6">
                <label>Stock</label>
                <input v-model="form.stock" type="number" class="form-control" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </form>
    </template>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="hideModal">Cancel</button>
      <button type="submit" class="btn btn-primary" @click="submitForm">{{ isEditing ? 'Update' : 'Create' }}</button>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch} from 'vue'
import axios from 'axios'
import BaseModal from '../BaseModal.vue'

const props = defineProps({
  isEditing: Boolean,
  currentProduct: Object
})
const emit = defineEmits(['submitted'])

const modal = ref(null)
const form = ref({ name: '', description: '', has_variants: false, price: '', stock: '', variants: [] })
const availableAttributes = ref([])
const selectedAttributes = ref({})
const generatedVariants = ref([])

const show = async (product = null) => {
  resetForm()
  await loadAttributes()

  if (product) {
    form.value = { ...form.value, ...product, variants: product.variants || [] }
    selectedAttributes.value = getSelectedFromVariants(product.variants || [])
  } else {
    availableAttributes.value.forEach(attr => {
      if (attr.values?.length) selectedAttributes.value[attr.id] = []
    })
  }

  modal.value?.show()
}

const hideModal = () => modal.value?.close()

const resetForm = () => {
  form.value = { name: '', sku: '', description: '', has_variants: false, price: '', stock: '', variants: [] }
  selectedAttributes.value = {}
  generatedVariants.value = []
}

const loadAttributes = async () => {
  try {
    const res = await axios.get('/api/attributes-values')
    availableAttributes.value = res.data
  } catch (err) {
    console.error('Failed to load attributes:', err)
  }
}

const toggleAttribute = (attrId, valId) => {
  const current = selectedAttributes.value[attrId] || []
  const index = current.indexOf(valId)
  if (index === -1) current.push(valId)
  else current.splice(index, 1)
  selectedAttributes.value[attrId] = [...current]
}

const getSelectedFromVariants = (variants = []) => {
  const map = {}
  variants.forEach(variant => {
    if (variant.options) {
      variant.options.forEach(opt => {
        const attrId = opt.attribute?.id
        const valId = opt.id
        if (!attrId || !valId) return
        if (!map[attrId]) map[attrId] = []
        if (!map[attrId].includes(valId)) map[attrId].push(valId)
      })
    }
  })
  return map
}

const generateVariants = () => {
  const combinations = Object.entries(selectedAttributes.value)
    .filter(([_, values]) => values.length)
    .map(([attrId, values]) => values.map(valId => ({ attrId: Number(attrId), valId })))

  const allCombos = combinations.reduce((acc, curr) =>
    acc.length === 0 ? curr.map(c => [c]) : acc.flatMap(a => curr.map(c => [...a, c]))
  , [])

  const prev = new Map(generatedVariants.value.map(v => [v.description, v]))

  generatedVariants.value = allCombos.map(combo => {
    const desc = combo.map(({ attrId, valId }) => {
      const attr = availableAttributes.value.find(a => a.id === attrId)
      const val = attr?.values.find(v => v.id === valId)
      return `${attr?.name || 'Unknown'}: ${val?.value || 'Unknown'}`
    }).join(', ')

    return {
      description: desc,
      price: prev.get(desc)?.price || '',
      stock: prev.get(desc)?.stock || ''
    }
  })
}

watch(() => selectedAttributes.value, () => {
  if (form.value.has_variants) generateVariants()
}, { deep: true })

const submitForm = async () => {
  try {
    const method = props.isEditing ? 'put' : 'post'
    const url = props.isEditing ? `/api/products/${props.currentProduct.id}` : '/api/products'
    const payload = {
      ...form.value,
      variants: form.value.has_variants ? generatedVariants.value : []
    }

    // Log the payload to the console
    console.log('Submitting payload:', payload)

    await axios[method](url, payload)
    emit('submitted')
    hideModal()
  } catch (err) {
    console.error('Submit error:', err)
    alert('Failed to save product')
  }
}

defineExpose({ show })
</script>

<style scoped>
.card-header {
  font-size: 0.875rem; /* smaller font size */
  padding: 0.5rem 1rem; /* reduce padding */
  line-height: 3rem; /* Set line height to control the spacing */
}
</style>