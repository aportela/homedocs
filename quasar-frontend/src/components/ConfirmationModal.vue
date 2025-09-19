<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card>
      <q-card-section class="q-p-none">
        <slot name="header"></slot>
      </q-card-section>
      <q-card-section class="q-p-none">
        <slot name="body"></slot>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn outline @click.stop="onCancel"><q-icon left name="close" />{{ t("Cancel")
        }}</q-btn>
        <q-btn outline @click.stop="onConfirm"><q-icon left name="done" />{{ t("Ok")
        }}</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits(['close', 'ok']);

const visible = ref(true);

const onClose = () => {
  visible.value = false;
  emit('close');
}

const onCancel = () => {
  visible.value = false;
  emit('cancel');
}

const onConfirm = () => {
  visible.value = false;
  emit('ok');
}

</script>