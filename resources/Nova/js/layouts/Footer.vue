<!-- Add target="_blank" to footer link. Don't remove the Copyright!!! -->
<template>
  <div class="mt-8 leading-normal text-xs text-gray-500 space-y-1">
    <p class="text-center">
      Powered by
      <a
        href="https://nova.laravel.com" target="_blank"
        class="inline-block py-1 text-primary-500 hover:text-primary-400 font-bold no-underline"
        >Laravel Nova</a
      >
      &middot; v{{ currentVersion }}.
    </p>

    <p class="text-center">
      &copy; {{ new Date().getFullYear() }} Laravel LLC &middot; by Taylor
      Otwell and David Hemphill
    </p>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: mapGetters(['currentVersion']),
}
</script>
