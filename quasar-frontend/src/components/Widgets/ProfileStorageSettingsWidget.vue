<template>
  <BaseWidget title="Profile settings" caption="Customize how you interact with the platform." icon="settings">
    <template v-slot:content>
      <q-toggle v-model="visibilityCheck" checked-icon="check" color="green"
        :label="t('Always show uploading dialog after adding files')" unchecked-icon="clear" class="q-mr-md"
        @update:modelValue="saveVisibilityCheck" />
    </template>
  </BaseWidget>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";

import { useLocalStorage } from "src/composables/useLocalStorage";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";

const { t } = useI18n();

const { alwaysOpenUploadDialog } = useLocalStorage();

const visibilityCheck = ref(alwaysOpenUploadDialog.get());

const saveVisibilityCheck = (val) => {
  alwaysOpenUploadDialog.set(val);
};

</script>
