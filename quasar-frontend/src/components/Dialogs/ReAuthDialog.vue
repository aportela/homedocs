<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card style="width: 512px; max-width: 80vw;">
      <q-card-section class="q-p-none">
        <slot name="header"></slot>
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none">
        <slot name="body"></slot>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";

const emit = defineEmits(['close', 'hide', 'success', 'error']);

const visible = ref(true);

const onClose = () => {
  visible.value = false;
  emit('close');
}

</script>