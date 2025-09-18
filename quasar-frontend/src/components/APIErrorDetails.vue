<template>
  <div>
    <dl>
      <dt v-if="apiError.url">URL: {{ apiError.url }}</dt>
      <dt v-if="apiError.method">Method: {{ apiError.method }}</dt>
      <dt v-if="apiError.httpCode">HTTP code: {{ apiError.httpCode }}</dt>
      <dt v-if="apiError.httpStatus">Text: {{ apiError.httpStatus }}</dt>
      <dt v-if="apiError.params.query">Query params: {{ apiError.params.query }}</dt>
      <dt v-if="apiError.params.data">Body params: {{ apiError.params.data }}</dt>
    </dl>
  </div>
</template>

<script setup>

const props = defineProps({
  apiError: {
    type: Object,
    required: true
  }
});

</script>