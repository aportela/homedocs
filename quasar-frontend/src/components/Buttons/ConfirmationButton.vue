<template>

  <q-btn @click.prevent.stop square :disable="disable" :icon="icon" :label="label">
    <q-popup-proxy v-model="showProxy">
      <q-banner>
        <template v-slot:avatar>
          <q-icon name="warning" color="primary" />
        </template>
        Are you sure ?.
        <template v-slot:action>
          <q-btn-group spread class="full-width no-shadow">
            <q-btn label="yes" icon="check" class="bg-blue text-white full-width full-width" no-caps
              @click.prevent.stop="onConfirm"></q-btn>
            <q-btn label="no" icon="cancel" class="bg-blue-6 text-white full-width full-width" no-caps
              @click.prevent.stop="onCancel"></q-btn>
          </q-btn-group>
        </template>
      </q-banner>
    </q-popup-proxy>
  </q-btn>
</template>

<script setup>

import { ref } from "vue";

const emit = defineEmits(['confirm']);

const props = defineProps({
  disable: {
    type: Boolean,
    required: false,
    default: false,
  },
  icon: {
    type: String,
    required: false,
  },
  label: {
    type: String,
    required: false,
  },
});

const showProxy = ref(false);

const onConfirm = () => {
  showProxy.value = false;
  emit("confirm");
};

const onCancel = () => {
  showProxy.value = false;
};
</script>