<template>
  <div>
    <dl>
      <dt v-if="apiError.method">Method: <strong>{{ apiError.method }}</strong></dt>
      <dt v-if="apiError.url">URL: <strong>{{ apiError.url }}</strong></dt>
      <dt v-if="apiError.httpCode">HTTP code: <strong>{{ apiError.httpCode }} <span v-if="apiError.httpStatus">({{
        apiError.httpStatus }})</span></strong></dt>
      <q-separator class="q-my-md" v-if="apiError.params.query || apiError.params.data"></q-separator>
      <dt v-if="apiError.params.query">Query params: <strong>{{ apiError.params.query }}</strong></dt>
      <dt v-if="apiError.params.data">Body params: <strong>{{ apiError.params.data }}</strong></dt>
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