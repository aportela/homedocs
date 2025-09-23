<template>
  <router-view />
</template>

<script>
import { defineComponent } from 'vue'


/*
window.onpopstate = function (event) {
  if(event?.state?.current?.endsWith("advanced-search")) {
  // TODO: UGLY HACK for checking if return to search page from history => relaunch previous search ?
  }
}
*/

export default defineComponent({
  name: 'App'
})
</script>