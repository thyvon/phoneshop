// api/products.js

import axios from 'axios'

export const createProduct = (data) => {
  return axios.post('/products/create', data)
}

export const editProduct = (id, data) => {
  return axios.post(`/products/edit/${id}`, data)
}
