<template>
  <q-dialog v-model="visible" @hide="onHide">
    <q-card class="q-card-base-dialog">
      <q-card-section class="q-p-none row">
        <slot name="header">
          <div class="col">
            <slot name="title">Title</slot>
          </div>
          <q-space />
          <div>
            <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
          </div>
        </slot>
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none">
        <slot name="body"></slot>
      </q-card-section>
      <slot name="footer">
        <q-separator class="q-my-sm"></q-separator>
        <q-card-actions align="right">
          <slot name="actions">
            <q-btn color="primary" size="md" no-caps @click.stop="onHide" icon="close" :label="t('Close')" />
          </slot>
        </q-card-actions>
      </slot>
    </q-card>
  </q-dialog>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits(['close']);

const visible = ref(true);

const onHide = () => {
  visible.value = false;
  emit('close');
}

</script>

<style lang="css" scoped>
.q-card-base-dialog {
  width: 60vw;
}
</style>