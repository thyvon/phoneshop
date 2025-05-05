import Swal from 'sweetalert2'

// Success Alert
export const successAlert = (title, message) => {
  Swal.fire({
    icon: 'success',
    title: title,
    text: message
  })
}

// Error Alert
export const errorAlert = (title, message) => {
  Swal.fire({
    icon: 'error',
    title: title,
    text: message
  })
}

// Confirmation Alert for delete
export const confirmDelete = () => {
  return Swal.fire({
    title: 'Are you sure?',
    text: 'You won\'t be able to revert this!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  })
}
