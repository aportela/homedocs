<template>
  <div v-if="apiError">
    <p class="q-my-none" v-if="apiError.url">URL: {{ apiError.url }}</p>
    <p class="q-my-none" v-if="apiError.method">Method: {{ apiError.method }}</p>
    <p class="q-my-none" v-if="apiError.httpCode">HTTP code: {{ apiError.httpCode }}</p>
    <p class="q-my-none" v-if="apiError.httpStatus">Text: {{ apiError.httpStatus }}</p>
    <p class="q-my-none" v-if="apiError.params.query">Query params: {{ apiError.params.query }}</p>
    <p class="q-my-none" v-if="apiError.params.data">Body params: {{ apiError.params.data }}</p>
  </div>
</template>

<script setup>

defineProps({
  apiError: {
    type: Object,
    required: true
  }
});

</script>