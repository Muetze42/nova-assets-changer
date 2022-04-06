<!-- Remove timezone -->
<template>
  <div :class="`text-${field.textAlign}`">
    <span v-if="field.value" class="whitespace-nowrap">
      {{ formattedDate }}
    </span>
    <span v-else>&mdash;</span>
  </div>
</template>

<script>
import { DateTime } from 'luxon'

export default {
  props: ['resourceName', 'field'],

  computed: {
    timezone() {
      return Nova.config('userTimezone') || Nova.config('timezone')
    },

    formattedDate() {
      return DateTime.fromISO(this.field.value)
        .setZone(this.timezone)
        .toLocaleString({
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
        })
    },
  },
}
</script>
