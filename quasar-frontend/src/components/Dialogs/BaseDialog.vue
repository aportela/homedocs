<template>
  <q-dialog v-model="visible" @hide="onHide">
    <q-card class="q-card-base-dialog" :style="{ width: width, minWidth: minWidth, maxWidth: maxWidth }">
      <q-card-section class="q-py-xs row self-center q-dialog-header">
        <slot name="header">
          <div class="col flex items-center">
            <slot name="header-left"></slot>
          </div>
          <q-space />
          <div>
            <slot name="header-right"></slot>
            <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" v-if="showHeaderCloseButton" />
          </div>
        </slot>
      </q-card-section>
      <q-card-section class="q-pa-none q-dialog-body">
        <slot name="body"></slot>
      </q-card-section>
      <q-card-section class="q-pa-none q-dialog-footer">
        <slot name="footer">
          <q-card-actions align="right">
            <slot name="actions">
              <slot name="actions-prepend"></slot>
              <q-btn class="action-secondary" v-if="confirmationDialog" @click.stop="onCancel"><q-icon left
                  name="close" />{{ t("Cancel")
                  }}</q-btn>
              <q-btn color="primary" v-if="confirmationDialog" @click.stop="onConfirm"><q-icon left name="done" />{{
                t("Ok")
              }}</q-btn>
              <q-btn color="primary" v-if="!confirmationDialog" size="md" no-caps @click.stop="onHide" icon="close"
                :label="t('Close')" />
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

const emit = defineEmits(['close', 'confirm', 'cancel']);

const props = defineProps({
  showHeaderCloseButton: {
    type: Boolean,
    required: false,
    default: true
  },
  width: {
    type: String,
    required: false,
    default: ""
  },
  minWidth: {
    type: String,
    required: false,
    default: ""
  },
  maxWidth: {
    type: String,
    required: false,
    default: ""
  },
  confirmationDialog: {
    type: Boolean,
    required: false,
    default: false
  }
});

const visible = ref(true);

const onHide = () => {
  visible.value = false;
  emit('close');
}

const onConfirm = () => {
  visible.value = false;
  emit('confirm');
}

const onCancel = () => {
  visible.value = false;
  emit('cancel');
}

</script>
