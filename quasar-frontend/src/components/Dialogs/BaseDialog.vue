<template>
  <q-dialog v-model="visible" @hide="onHide">
    <q-card class="q-card-base-dialog">
      <q-card-section class="q-py-sm row self-center q-dialog-header">
        <slot name="header">
          <div class="col flex items-center">
            <slot name="title">Title</slot>
          </div>
          <q-space />
          <div>
            <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
          </div>
        </slot>
      </q-card-section>
      <q-card-section class="q-py-sm q-dialog-body">
        <slot name="body"></slot>
      </q-card-section>
      <q-card-section class="q-py-sm q-dialog-footer">
        <slot name="footer">
          <q-card-actions align="right">
            <slot name="actions">
              <q-btn color="primary" size="md" no-caps @click.stop="onHide" icon="close" :label="t('Close')" />
            </slot>
          </q-card-actions>
        </slot>
      </q-card-section>
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
  background: var(--color-zinc-100);
}

.q-dialog-header {
  background: var(--color-zinc-200);
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}


.q-dialog-footer {
  background: var(--color-zinc-200);
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
</style>