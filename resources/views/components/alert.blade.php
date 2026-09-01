<div
  x-data="{ show: false, message: '', type: 'success' }"
  x-on:alert.window="
    message = $event.detail.message; 
    show = true; 
    type = $event.detail.type ?? 'success'; 
    setTimeout(() => show = false, 3000);"
  x-show="show"
  x-cloak
  x-transition
  :class="'alert alert-' + type + ' mt-3'">
  <span x-text="message"></span>
</div>