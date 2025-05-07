<template>
    <div class="col-sm-12 col-md-6 col-lg-5 col-xl-4 ml-auto">
      <h1 class="text-white fw-300 mb-3 d-sm-block d-md-none">Secure login</h1>
      <div class="card p-4 rounded-plus bg-faded">
        <form @submit.prevent="submitLogin">
          <!-- Email -->
          <div class="form-group">
            <label for="email" class="form-label text-white">Email</label>
            <input v-model="email" type="email" id="email" class="form-control form-control-lg" required placeholder="Your email address" />
          </div>
  
          <!-- Password -->
          <div class="form-group">
            <label for="password" class="form-label text-white">Password</label>
            <input v-model="password" type="password" id="password" class="form-control form-control-lg" required placeholder="Your password" />
          </div>
  
          <!-- Remember Me -->
          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input v-model="remember" type="checkbox" class="custom-control-input" id="remember_me" />
              <label class="custom-control-label text-white" for="remember_me">
                Remember me for the next 30 days
              </label>
            </div>
          </div>
  
          <!-- Buttons -->
          <div class="row no-gutters">
            <div class="col-lg-6 pr-lg-1 my-2">
              <button type="button" class="btn btn-info btn-block btn-lg">
                Sign in with <i class="fab fa-google"></i>
              </button>
            </div>
            <div class="col-lg-6 pl-lg-1 my-2">
              <button type="submit" class="btn btn-danger btn-block btn-lg" :disabled="loading">
                {{ loading ? 'Logging in...' : 'Secure login' }}
              </button>
            </div>
          </div>
  
          <div v-if="error" class="alert alert-danger mt-3">
            {{ error }}
          </div>
        </form>
      </div>
  
      <div class="position-absolute pos-bottom pos-left pos-right p-3 text-center text-white">
        2020 © SmartAdmin by
        <a href="https://www.gotbootstrap.com" class="text-white opacity-40 fw-500" target="_blank">gotbootstrap.com</a>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import axios from 'axios'
  import router from '@/router' // or use Inertia if that's what you're using
  
  const email = ref('')
  const password = ref('')
  const remember = ref(false)
  const loading = ref(false)
  const error = ref('')
  
  const submitLogin = async () => {
    loading.value = true
    error.value = ''
    try {
      const response = await axios.post('/api/login', {
        email: email.value,
        password: password.value,
      })
  
      const token = response.data.token
      localStorage.setItem('token', token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  
      // redirect to dashboard/products/etc
      router.push('/products') // or Inertia.visit('/products')
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed.'
    } finally {
      loading.value = false
    }
  }
  </script>
  