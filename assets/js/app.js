// assets/js/app.js
// Simple front-end helpers for the static prototype and small UX touches

document.addEventListener('click', function(e){
  // confirm delete forms with class confirm-delete
  if (e.target.matches('.confirm-delete')) {
    if (!confirm('Are you sure?')) e.preventDefault();
  }
});

// Example: show a small alert for elements with data-alert
document.querySelectorAll('[data-alert]').forEach(function(el){
  el.addEventListener('click', function(){
    alert(el.dataset.alert);
  });
});