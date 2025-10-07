<template>
  <BaseWidget title="Profile settings" caption="Customize how you interact with the platform." icon="settings">
    <template v-slot:content>
      <p class="q-my-none"><q-toggle v-model="visibilityCheck" checked-icon="check" color="green"
          :label="t('Always show uploading dialog after adding files')" unchecked-icon="clear" class="q-mr-md"
          @update:modelValue="saveVisibilityCheck" /></p>
      <p class="q-my-none"><q-toggle v-model="toolTipsCheck" checked-icon="check" color="green"
          :label="t('Show tooltips')" unchecked-icon="clear" class="q-mr-md" @update:modelValue="saveToolTipsCheck" />
      </p>
    </template>
  </BaseWidget>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";

import { useLocalStorage } from "src/composables/useLocalStorage";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";

const { t } = useI18n();

const { alwaysOpenUploadDialog, showToolTips } = useLocalStorage();

const visibilityCheck = ref(alwaysOpenUploadDialog.get());

const saveVisibilityCheck = (val) => {
  alwaysOpenUploadDialog.set(val);
};

const toolTipsCheck = ref(showToolTips.get());

const saveToolTipsCheck = (val) => {
  showToolTips.set(val);
};

</script>