import Swal from 'sweetalert2'

export function confirmAction({ 
  title = 'Tu es sûr ?',
  text = 'Cette action est irréversible',
  icon = 'warning',
  confirmText = 'Oui',
  cancelText = 'Annuler',
  onConfirm = () => {}
}) {
  Swal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText
  }).then((result) => {
    if (result.isConfirmed) {
      onConfirm()
    }
  })
}

export function showFlash(type, message) {
  Swal.fire({
    icon: type,
    title: type === 'success' ? 'Succès' : 'Erreur',
    text: message,
    timer: 5000,
    timerProgressBar: true,
    showConfirmButton: true,
    confirmButtonText: 'OK'
  })
}

document.addEventListener('DOMContentLoaded', () => {
  const flashEl = document.getElementById('flash-data')
  if (!flashEl) return

  const successMessages = JSON.parse(flashEl.dataset.success || '[]')
  const errorMessages = JSON.parse(flashEl.dataset.error || '[]')

  successMessages.forEach(msg => showFlash('success', msg))
  errorMessages.forEach(msg => showFlash('error', msg))

  document.querySelectorAll('.js-confirm-delete').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault()
      
      const title = form.dataset.title || 'Êtes-vous sûr ?'
      const text = form.dataset.text || 'Cette action est irréversible'
      const confirmButton = form.dataset.confirmButton || 'Oui, supprimer'
      
      Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: confirmButton,
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit()
        }
      })
    })
  })

  document.querySelectorAll('.js-confirm-submit').forEach(form => {
    console.log('Form trouvé avec js-confirm-submit:', form.action)
    
    form.addEventListener('submit', (e) => {
      console.log('Submit intercepté, confirmed:', form.dataset.confirmed)
      
      if (form.dataset.confirmed === 'true') {
        console.log('Formulaire déjà confirmé, on laisse passer')
        return true
      }

      e.preventDefault()
      e.stopPropagation()
      
      const isEdit = form.action.toLowerCase().includes('/edit')
      console.log('Est une modification?', isEdit, 'URL:', form.action)
      
      const title = isEdit ? 'Confirmer la modification ?' : 'Confirmer l\'ajout ?'
      const text = isEdit 
        ? 'Voulez-vous vraiment enregistrer ces modifications ?' 
        : 'Voulez-vous vraiment ajouter cet élément ?'
      const confirmButton = isEdit ? 'Oui, modifier' : 'Oui, ajouter'
      
      Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmButton,
        cancelButtonText: 'Annuler'
      }).then((result) => {
        console.log('Résultat SweetAlert:', result)
        if (result.isConfirmed) {
          console.log('Confirmé, soumission du formulaire')
          form.dataset.confirmed = 'true'
          const submitEvent = new Event('submit', { cancelable: true, bubbles: true })
          form.dispatchEvent(submitEvent)
          if (!submitEvent.defaultPrevented) {
            form.submit()
          }
        }
      })
      
      return false
    })
  })
})

