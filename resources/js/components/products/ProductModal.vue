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
            <div class="form-group">
              <label>Name</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="form.description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Barcode</label>
                <input v-model="form.barcode" type="text" class="form-control" />
              </div>
              <div class="form-group col-md-6">
                <label>Image URL</label>
                <input v-model="form.image" type="text" class="form-control" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Brand</label>
                <select v-model="form.brand_id" class="form-control">
                  <option value="">Select Brand</option>
                  <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Category</label>
                <select v-model="form.category_id" class="form-control">
                  <option value="">Select Category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Unit</label>
                <select v-model="form.unit_id" class="form-control">
                  <option value="">Select Unit</option>
                  <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Alert Quantity</label>
                <input v-model="form.alert_qty" type="number" class="form-control" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Tax (%)</label>
                <input v-model="form.tax" type="number" class="form-control" />
              </div>
              <div class="form-group col-md-4">
                <label>Tax Type</label>
                <select v-model="form.include_tax" class="form-control">
                  <option :value="0">Exclusive</option>
                  <option :value="1">Inclusive</option>
                </select>
              </div>
            </div>
            <div class="form-row">
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

        <!-- Variants or Price/Stock -->
        <div class="card shadow-sm mb-4">
          <div
            :class="[
              'card-header py-2',
              form.has_variants ? 'bg-secondary-50' : 'bg-success-50'
            ]"
          >
            <h6 class="mb-0">
              {{ form.has_variants ? 'Variant Products' : 'Stock & Price' }}
            </h6>
          <div class="custom-control custom-checkbox mt-2">
          <input
            v-model="form.has_variants"
            type="checkbox"
            class="custom-control-input"
            id="hasVariants"
            :disabled="props.isEditing && !form.has_variants"
          />
            <label class="custom-control-label" for="hasVariants">Has Variants ?</label>
          </div>
          </div>
          <div class="card-body">
            <template v-if="form.has_variants">
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>Variant Description</th>
                      <th>SKU</th>
                      <th>Price</th>
                      <th>Stock</th>
                      <th>Purchase Price</th>
                      <th>Margin</th>
                      <th>Sale Price</th>
                      <th>Image</th>
                      <th>Active</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(variant, index) in generatedVariants" :key="index">
                      <td class="align-middle">{{ form.name }}<span v-if="variant.description"> - {{ variant.description }}</span></td>
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
                        <div class="custom-control custom-checkbox">
                          <input
                            v-model="variant.is_active"
                            type="checkbox"
                            class="custom-control-input"
                            :id="`variant-active-${index}`"
                          />
                          <label class="custom-control-label" :for="`variant-active-${index}`"></label>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
            <template v-else>
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>SKU</th>
                      <th>Price</th>
                      <th>Stock</th>
                      <th>Purchase Price</th>
                      <th>Margin</th>
                      <th>Sale Price</th>
                      <th>Image</th>
                      <th>Active</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <input v-model="form.sku" type="text" class="form-control form-control-sm" placeholder="SKU" />
                      </td>
                      <td>
                        <input v-model="form.price" type="number" class="form-control form-control-sm" placeholder="Price" />
                      </td>
                      <td>
                        <input v-model="form.stock" type="number" class="form-control form-control-sm" placeholder="Stock" />
                      </td>
                      <td>
                        <input v-model="form.default_purchase_price" type="number" class="form-control form-control-sm" placeholder="Purchase Price" />
                      </td>
                      <td>
                        <input v-model="form.default_margin" type="number" class="form-control form-control-sm" placeholder="Margin" />
                      </td>
                      <td>
                        <input v-model="form.default_sale_price" type="number" class="form-control form-control-sm" placeholder="Sale Price" />
                      </td>
                      <td>
                        <input v-model="form.image" type="text" class="form-control form-control-sm" placeholder="Image URL" />
                      </td>
                      <td class="text-center">
                        <div class="custom-control custom-checkbox">
                          <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="custom-control-input"
                            id="single-active"
                          />
                          <label class="custom-control-label" for="single-active"></label>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
          </div>
        </div>
      </form>
    </template>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="hideModal">Cancel</button>
      <button type="submit" class="btn btn-primary" @click="submitForm">
        {{ isEditing ? 'Update' : 'Create' }}
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import BaseModal from '../BaseModal.vue'
import { showAlert } from '@/utils/bootbox'

const props = defineProps({
  isEditing: Boolean,
  currentProduct: Object
})
const emit = defineEmits(['submitted'])

const showModal = ref(false)
const brands = ref([])
const categories = ref([])
const units = ref([])
const form = ref({
  name: '',
  description: '',
  barcode: '',
  brand_id: '',
  category_id: '',
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
  price: '',
  stock: '',
  variants: []
})
const availableAttributes = ref([])
const selectedAttributes = ref({})
const generatedVariants = ref([])

onMounted(async () => {
  brands.value = (await axios.get('/api/brands')).data
  categories.value = (await axios.get('/api/categories')).data
  units.value = (await axios.get('/api/units')).data
  await loadAttributes()
})

const show = async (product = null) => {
  resetForm()
  await loadAttributes()
  brands.value = (await axios.get('/api/brands')).data
  categories.value = (await axios.get('/api/categories')).data
  units.value = (await axios.get('/api/units')).data

  if (product) {
    form.value = {
      ...form.value,
      ...product,
      id: product.id,
      has_variants: Boolean(product.has_variants),
      variants: product.variants || [],
      price: product.price ?? null,
      stock: product.stock ?? null,
      barcode: product.barcode ?? '',
      brand_id: product.brand_id ?? '',
      category_id: product.category_id ?? '',
      unit_id: product.unit_id ?? '',
      manage_stock: product.manage_stock !== undefined ? !!product.manage_stock : true,
      alert_qty: product.alert_qty ?? 0,
      image: product.image ?? '',
      not_sale: !!product.not_sale,
      serial_des: !!product.serial_des,
      tax: product.tax ?? 0,
      include_tax: product.include_tax ?? 0,
      is_active: product.is_active !== undefined ? !!product.is_active : true,

      default_purchase_price: '',
      default_margin: '',
      default_sale_price: '',
    }
    selectedAttributes.value = getSelectedFromVariants(product.variants || [])
    if (form.value.has_variants) {
      generateVariants()
    } else if (product.variants && product.variants.length > 0) {
      const v = product.variants[0]
      form.value.price = v.price
      form.value.stock = v.stock
      form.value.default_purchase_price = v.default_purchase_price
      form.value.default_margin = v.default_margin
      form.value.default_sale_price = v.default_sale_price
      form.value.sku = v.sku
      form.value.image = v.image
      form.value.is_active = !!v.is_active
    }
  } else {
    availableAttributes.value.forEach(attr => {
      if (attr.values?.length) selectedAttributes.value[attr.id] = []
    })
  }

  showModal.value = true
}

const hideModal = () => {
  showModal.value = false
}

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    barcode: '',
    brand_id: '',
    category_id: '',
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
    price: '',
    stock: '',
    variants: []
  }
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

  generatedVariants.value = allCombos.map(combo => {
    const valIds = combo.map(({ valId }) => valId).sort((a, b) => a - b)
    const key = valIds.join('-')
    const existing = existingVariantMap.get(key)
    const desc = combo.map(({ attrId, valId }) => {
      const attr = availableAttributes.value.find(a => a.id === attrId)
      const val = attr?.values.find(v => v.id === valId)
      return `${attr?.name || 'Unknown'}: ${val?.value || 'Unknown'}`
    }).join(', ')
    return {
      id: existing?.id || undefined,
      description: desc,
      sku: existing?.sku || '',
      price: existing?.price || '',
      stock: existing?.stock || '',
      default_purchase_price: existing?.default_purchase_price || '',
      default_sale_price: existing?.default_sale_price || '',
      default_margin: existing?.default_margin || '',
      image: existing?.image || '',
      is_active: existing?.is_active ?? true,
      variant_value_ids: valIds,
    }
  })
}

watch(() => selectedAttributes.value, () => {
  if (form.value.has_variants) generateVariants()
}, { deep: true })

const submitForm = async () => {
  try {
    const method = props.isEditing ? 'put' : 'post'
    const url = props.isEditing
      ? `/api/products/${form.value.id}`
      : '/api/products'
    const payload = {
      ...form.value,
      variants: form.value.has_variants ? generatedVariants.value : []
    }
    await axios[method](url, payload)
    emit('submitted')
    hideModal()
    showAlert('Success', `Product ${props.isEditing ? 'updated' : 'created'} successfully.`, 'success')
  } catch (err) {
    console.error('Submit error:', err)
    showAlert('Error', err.response?.data?.message || 'Failed to save product.', 'danger')
  }
}

defineExpose({ show })
</script>