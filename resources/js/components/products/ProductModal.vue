<template>
  <BaseModal
    v-model="showModal"
    id="productModal"
    :title="isEditing ? 'Edit Product' : 'Create Product'"
    size="xl"
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
              <div class="form-group col-md-3">
                <label>SKU</label>
                <input v-model="form.sku" type="text" class="form-control" />
              </div>
              <div class="form-group col-md-3">
                <label>Barcode Type</label>
                <select ref="barcodeSelect" v-model="form.barcode" class="form-control">
                  <option value="">Select Barcode Type</option>
                  <option value="EAN13">EAN-13</option>
                  <option value="UPC">UPC</option>
                  <option value="QR">QR Code</option>
                  <option value="CODE128">Code 128</option>
                  <!-- Add more types as needed -->
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-3">
                <label>Unit</label>
                <select ref="unitSelect" v-model="form.unit_id" class="form-control">
                  <option value="">Select Unit</option>
                  <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Brand</label>
                <select ref="brandSelect" v-model="form.brand_id" class="form-control">
                  <option value="">Select Brand</option>
                  <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Category</label>
                <select ref="categorySelect" v-model="form.category_id" class="form-control">
                  <option value="">Select Category</option>
                  <option v-for="cat in mainCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Sub-Category</label>
                <select ref="subCategorySelect" v-model="form.sub_category_id" class="form-control">
                <option value="">Select Sub Category</option>
                <option v-for="cat in subCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-9">
                <label>Description</label>
                <textarea v-model="form.description" class="form-control" rows="1"></textarea>
              </div>
              <div class="form-group col-md-3">
                <label>Image URL</label>
                <input v-model="form.image" type="text" class="form-control" />
              </div>
            </div>

            <div class="form-row mb-3">
              <div class="form-group col-md-2">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="manageStock"
                    v-model="form.manage_stock"
                  />
                  <label class="custom-control-label" for="manageStock">Manage Stock</label>
                </div>
              </div>
              <div class="form-group col-md-2" v-if="form.manage_stock">
                <label>Alert Quantity</label>
                <input v-model="form.alert_qty" type="number" class="form-control" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-2">
                <label>Tax (%)</label>
                <input v-model="form.tax" type="number" class="form-control" />
              </div>
              <div class="form-group col-md-2">
                <label>Tax Type</label>
                <select v-model="form.include_tax" class="form-control">
                  <option :value="0">Exclusive</option>
                  <option :value="1">Inclusive</option>
                </select>
              </div>
              <div class="form-group col-md-2">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="notSale"
                    v-model="form.not_sale"
                  />
                  <label class="custom-control-label" for="notSale">Not for Sale</label>
                </div>
              </div>
              <div class="form-group col-md-2">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="serialDes"
                    v-model="form.serial_des"
                  />
                  <label class="custom-control-label" for="serialDes">Serial Description</label>
                </div>
              </div>
              <div class="form-group col-md-2">
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
              <div class="form-group col-md-2">
                <div class="custom-control custom-checkbox mt-4">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="hasVariants"
                    v-model="form.has_variants"
                  />
                  <label class="custom-control-label" for="hasVariants">Has Variants</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Attributes -->
        <div v-if="form.has_variants" class="card border shadow-sm mb-4">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold">Attribute Selection</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <template v-for="(attr, index) in availableAttributes" :key="index">
                <div v-if="attr?.values?.length > 0" class="col-6 col-md-4 col-lg-3 mb-3">
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

        <!-- Variants Table (Always Show) -->
        <div class="card shadow-sm mb-4">
          <div
            :class="[
              'card-header py-2',
              form.has_variants ? 'bg-secondary-50' : 'bg-success-50'
            ]"
          >
            <h6 class="mb-0">
              Variant Products
            </h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th style="min-width: 160px;">Variant Description</th>
                      <th style="min-width: 100px;">SKU</th>
                      <th style="min-width: 80px;">Price</th>
                      <th style="min-width: 80px;">Stock</th>
                      <th style="min-width: 120px;">Purchase Price</th>
                      <th style="min-width: 80px;">Margin</th>
                      <th style="min-width: 100px;">Sale Price</th>
                      <th style="min-width: 120px;">Image</th>
                      <th style="min-width: 70px;">Active</th>
                      <th style="min-width: 70px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(variant, index) in generatedVariants" :key="index">
                      <td class="align-middle">{{ variant.description }}</td>
                      <td>
                        <input v-model="variant.sku" type="text" class="form-control form-control-sm" placeholder="SKU" />
                      </td>
                      <td>
                        <input v-model="variant.price" type="number" class="form-control form-control-sm" placeholder="Price" />
                      </td>
                      <td>
                        <input v-model="variant.stock" type="number" class="form-control form-control-sm" placeholder="Stock" />
                      </td>
                      <td>
                        <input v-model="variant.default_purchase_price" type="number" class="form-control form-control-sm" placeholder="Purchase Price" />
                      </td>
                      <td>
                        <input v-model="variant.default_margin" type="number" class="form-control form-control-sm" placeholder="Margin" />
                      </td>
                      <td>
                        <input v-model="variant.default_sale_price" type="number" class="form-control form-control-sm" placeholder="Sale Price" />
                      </td>
                      <td>
                        <input v-model="variant.image" type="text" class="form-control form-control-sm" placeholder="Image URL" />
                      </td>
                      <td class="text-center">
                        <div class="custom-control custom-checkbox d-inline-block">
                          <input
                            v-model="variant.is_active"
                            type="checkbox"
                            class="custom-control-input"
                            :id="`variant-active-${index}`"
                          />
                          <label class="custom-control-label" :for="`variant-active-${index}`"></label>
                        </div>
                      </td>
                      <td class="text-center">
                        <button
                          v-if="generatedVariants.length > 1"
                          type="button"
                          class="btn btn-sm btn-danger"
                          @click="removeVariant(index)"
                          title="Remove this variant"
                        >
                          <i class="fal fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
              </table>
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
import { ref, nextTick, watch, onMounted, computed } from 'vue'
import axios from 'axios'
import BaseModal from '@/components/reusable/BaseModal.vue'
import { showAlert } from '@/utils/bootbox'
import { initSelect2, destroySelect2 } from '@/utils/select2'

// --- Props & Emits ---
const props = defineProps({
  isEditing: Boolean,
  currentProduct: Object
})
const emit = defineEmits(['submitted'])

// --- Computed ---

const subCategories = computed(() =>
  categories.value.filter(cat =>
    cat.sub_taxonomy === 1 && cat.parent_id === Number(form.value.category_id)
  )
)

const mainCategories = computed(() =>
  categories.value.filter(cat => cat.sub_taxonomy === 0)
)

// --- End of Computed ---

// --- State ---
const showModal = ref(false)
const brands = ref([])
const categories = ref([])
const units = ref([])
const form = ref({
  name: '',
  sku: '',
  description: '',
  barcode: 'CODE128',
  brand_id: '',
  category_id: '',
  sub_category_id: null,
  unit_id: '',
  manage_stock: true,
  alert_qty: 0,
  image: '',
  not_sale: false,
  serial_des: false,
  tax: 0,
  include_tax: 0,
  is_active: true,
  has_variants: false,
  variants: []
})
const availableAttributes = ref([])
const selectedAttributes = ref({})
const generatedVariants = ref([])

const barcodeSelect = ref(null)
const unitSelect = ref(null)
const brandSelect = ref(null)
const categorySelect = ref(null)
const subCategorySelect = ref(null)
const taxTypeSelect = ref(null)
const isSubmitting = ref(false)
const skipCategoryWatcher = ref(false)

// --- Lifecycle ---
onMounted(async () => {
  await loadInitialData()
  await loadAttributes()
})

// --- Methods ---
const loadInitialData = async () => {
  brands.value = (await axios.get('/api/product-brands')).data
  categories.value = (await axios.get('/api/product-categories')).data
  units.value = (await axios.get('/api/product-units')).data
}

const removeVariant = (idx) => {
  if (generatedVariants.value.length > 1) {
    generatedVariants.value.splice(idx, 1)
  }
}

const loadAttributes = async () => {
  try {
    const res = await axios.get('/api/attributes-values')
    availableAttributes.value = res.data
  } catch (err) {
    console.error('Failed to load attributes:', err)
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    sku: '',
    description: '',
    barcode: 'CODE128', // <-- Set default here
    brand_id: '',
    category_id: '',
    sub_category_id: null,
    unit_id: '',
    manage_stock: true,
    alert_qty: 0,
    image: '',
    not_sale: false,
    serial_des: false,
    tax: 0,
    include_tax: 0,
    is_active: true,
    has_variants: false,
    variants: []
  }
  selectedAttributes.value = {}
  generatedVariants.value = [{
    description: '',
    sku: '',
    price: '',
    stock: '',
    default_purchase_price: '',
    default_sale_price: '',
    default_margin: '',
    image: '',
    is_active: true,
    variant_value_ids: [],
  }]
}

const show = async (product = null) => {
  resetForm()
  await loadAttributes()
  await loadInitialData()

  if (product) {
    skipCategoryWatcher.value = true
    form.value = {
      ...form.value,
      ...product,
      id: product.id,
      sku: product.sku ?? '',
      has_variants: Boolean(product.has_variants),
      variants: product.variants || [],
      barcode: product.barcode ? product.barcode : 'CODE128',
      brand_id: product.brand_id ?? '',
      category_id: product.category_id ?? '',
      sub_category_id: product.sub_category_id ?? null,
      unit_id: product.unit_id ?? '',
      manage_stock: product.manage_stock !== undefined ? !!product.manage_stock : true,
      alert_qty: product.alert_qty ?? 0,
      image: product.image ?? '',
      not_sale: !!product.not_sale,
      serial_des: !!product.serial_des,
      tax: product.tax ?? 0,
      include_tax: product.include_tax ?? 0,
      is_active: product.is_active !== undefined ? !!product.is_active : true,
    }
    await nextTick()
    skipCategoryWatcher.value = false
    selectedAttributes.value = getSelectedFromVariants(product.variants || [])
    generateVariants()
  } else {
    availableAttributes.value.forEach(attr => {
      if (attr.values?.length) selectedAttributes.value[attr.id] = []
    })
    generatedVariants.value = [{
      description: '',
      sku: '',
      price: '',
      stock: '',
      default_purchase_price: '',
      default_sale_price: '',
      default_margin: '',
      image: '',
      is_active: true,
      variant_value_ids: [],
    }]
  }

  showModal.value = true
}

const hideModal = () => {
  showModal.value = false
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
    if (variant.values) {
      variant.values.forEach(val => {
        const attrId = val.attribute?.id
        const valId = val.id
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

  const existingVariantMap = new Map(
    form.value.variants.map(v => {
      const key = (v.values || []).map(val => val.id).sort((a, b) => a - b).join('-')
      return [key, v]
    })
  )

  const currentVariantMap = new Map(
    generatedVariants.value.map(v => {
      const key = (v.variant_value_ids || []).sort((a, b) => a - b).join('-')
      return [key, v]
    })
  )

  let defaultVariant = null
  if (
    form.value.variants.length === 1 &&
    (!form.value.variants[0].values || form.value.variants[0].values.length === 0)
  ) {
    defaultVariant = form.value.variants[0]
  }

  // Find the last user input as a fallback for new combos
  const lastUserInput = generatedVariants.value.length
    ? generatedVariants.value[generatedVariants.value.length - 1]
    : null

  generatedVariants.value = allCombos.length
  ? allCombos.map((combo, idx) => {
      const valIds = combo.map(({ valId }) => valId).sort((a, b) => a - b)
      const key = valIds.join('-')
      const userInput = currentVariantMap.get(key)
      const existing = existingVariantMap.get(key)
      const desc = combo
        .map(({ attrId, valId }) => {
          const attr = availableAttributes.value.find(a => a.id === attrId)
          const val = attr?.values.find(v => v.id === valId)
          return {
            ordinal: attr?.ordinal ?? 0,
            text: `${attr?.name || 'Unknown'}: ${val?.value || 'Unknown'}`
          }
        })
        .sort((a, b) => a.ordinal - b.ordinal)
        .map(item => item.text)
        .join(', ')
      let sku = userInput?.sku ?? existing?.sku
      if (!sku) {
        sku = ''
      }
      // If this is a new combination (no userInput and no existing), clear all price-related fields
      const isNewCombo = !userInput && !existing
      return {
        id: existing?.id || undefined,
        description: desc,
        sku,
        price: isNewCombo ? '' : (userInput?.price ?? existing?.price ?? defaultVariant?.price ?? lastUserInput?.price ?? ''),
        stock: isNewCombo ? '' : (userInput?.stock ?? existing?.stock ?? defaultVariant?.stock ?? lastUserInput?.stock ?? ''),
        default_purchase_price: isNewCombo ? '' : (userInput?.default_purchase_price ?? existing?.default_purchase_price ?? defaultVariant?.default_purchase_price ?? lastUserInput?.default_purchase_price ?? ''),
        default_sale_price: isNewCombo ? '' : (userInput?.default_sale_price ?? existing?.default_sale_price ?? defaultVariant?.default_sale_price ?? lastUserInput?.default_sale_price ?? ''),
        default_margin: isNewCombo ? '' : (userInput?.default_margin ?? existing?.default_margin ?? defaultVariant?.default_margin ?? lastUserInput?.default_margin ?? ''),
        image: isNewCombo ? '' : (userInput?.image ?? existing?.image ?? defaultVariant?.image ?? lastUserInput?.image ?? ''),
        is_active: userInput?.is_active !== undefined
          ? !!userInput.is_active
          : (existing?.is_active !== undefined
              ? !!Number(existing.is_active)
              : (defaultVariant?.is_active !== undefined
                  ? !!Number(defaultVariant.is_active)
                  : (lastUserInput?.is_active !== undefined ? !!lastUserInput.is_active : true))),
        variant_value_ids: valIds,
      }
    })
  : (form.value.has_variants
      ? [{
          description: '',
          sku: '',
          price: '',
          stock: '',
          default_purchase_price: '',
          default_sale_price: '',
          default_margin: '',
          image: '',
          is_active: true,
          variant_value_ids: [],
        }]
      : form.value.variants.length
        ? form.value.variants.map(v => ({
            ...v,
            description: '',
            variant_value_ids: [],
          }))
        : [{
            description: '',
            sku: '',
            price: '',
            stock: '',
            default_purchase_price: '',
            default_sale_price: '',
            default_margin: '',
            image: '',
            is_active: true,
            variant_value_ids: [],
          }]
    )
}

// --- Watchers ---
watch(() => form.value.has_variants, (newVal) => {
  if (newVal) {
    generateVariants()
  } else {
    // If not using variants, always show at least one row
    generatedVariants.value = form.value.variants.length
      ? form.value.variants.map(v => ({
          ...v,
          description: '',
          variant_value_ids: [],
        }))
      : [{
          description: '',
          sku: '',
          price: '',
          stock: '',
          default_purchase_price: '',
          default_sale_price: '',
          default_margin: '',
          image: '',
          is_active: true,
          variant_value_ids: [],
        }]
  }
})

watch(() => selectedAttributes.value, () => {
  if (form.value.has_variants) generateVariants()
}, { deep: true })

watch(() => form.value.category_id, () => {
  if (skipCategoryWatcher.value) return
  form.value.sub_category_id = null
})

watch(showModal, async (val) => {
  if (val) {
    await nextTick()
    const $modal = window.$('#productModal')
    initSelect2(barcodeSelect.value, { placeholder: 'Select Barcode Type', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.barcode = v)
    initSelect2(unitSelect.value, { placeholder: 'Select Unit', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.unit_id = v)
    initSelect2(brandSelect.value, { placeholder: 'Select Brand', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.brand_id = v)
    initSelect2(categorySelect.value, { placeholder: 'Select Category', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.category_id = v)
    initSelect2(subCategorySelect.value, { placeholder: 'Select Sub-Category', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.sub_category_id = v)
    // Set sub-category value after Select2 and options are ready
    await nextTick()
    window.$(subCategorySelect.value).val(form.value.sub_category_id).trigger('change')
    initSelect2(taxTypeSelect.value, { placeholder: 'Select Tax Type', width: '100%', allowClear:true, dropdownParent: $modal }, v => form.value.include_tax = v)
  } else {
    destroySelect2(barcodeSelect.value)
    destroySelect2(unitSelect.value)
    destroySelect2(brandSelect.value)
    destroySelect2(categorySelect.value)
    destroySelect2(subCategorySelect.value)
    destroySelect2(taxTypeSelect.value)
  }
})

watch(subCategories, async () => {
  await nextTick()
  destroySelect2(subCategorySelect.value)
  const $modal = window.$('#productModal')
  initSelect2(
    subCategorySelect.value,
    { placeholder: 'Select Sub-Category', width: '100%', allowClear:true, dropdownParent: $modal },
    v => form.value.sub_category_id = v
  )
  // Set value if it exists in the options
  const exists = subCategories.value.some(cat => cat.id == form.value.sub_category_id)
  await nextTick()
    if (exists) {
    window.$(subCategorySelect.value).val(form.value.sub_category_id).trigger('change')
    } else {
    window.$(subCategorySelect.value).val('').trigger('change')
    form.value.sub_category_id = null
    }
})

// --- End of Watchers ---

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {

    if (form.value.sub_category_id === '' || form.value.sub_category_id === undefined) {
      form.value.sub_category_id = null
    }
    if (Array.isArray(form.value.sub_category_id)) {
      form.value.sub_category_id = form.value.sub_category_id.length ? form.value.sub_category_id[0] : null
    }

    const method = props.isEditing ? 'put' : 'post'
    const url = props.isEditing
      ? `/api/products/${form.value.id}`
      : '/api/products'
    const payload = {
      ...form.value,
      variants: generatedVariants.value
    }
    await axios[method](url, payload)
    emit('submitted')
    hideModal()
    showAlert('Success', `Product ${props.isEditing ? 'updated' : 'created'} successfully.`, 'success')
  } catch (err) {
    console.error('Submit error:', err)
    showAlert('Error', err.response?.data?.message || 'Failed to save product.', 'danger')
  } finally {
    isSubmitting.value = false
  }
}

defineExpose({ show })
</script>

<style scoped>
.table th,
.table td {
  min-width: 70px;
}
</style>
