<template>
  <q-dialog v-model="visible" @hide="emit('close')">
    <q-card>
      <q-card-section class="bg-grey-8 text-white q-p-none">
        <slot name="header"></slot>
      </q-card-section>
      <q-card-section class="q-p-none">
        <slot name="body"></slot>
      </q-card-section>
      <q-card-actions align="right">
        <q-btn outline v-close-popup class="bg-grey-6 text-white"><q-icon left name="close" />{{ t("Cancel")
        }}</q-btn>
        <q-btn outline @click.stop="emit('ok')" class="bg-dark text-white"><q-icon left name="done" />{{ t("Ok")
        }}</q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const emit = defineEmits(['close', 'ok']);

const visible = ref(true);

</script>
