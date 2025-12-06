<template>
  <BaseWidget title="Profile settings" caption="Customize how you interact with the platform." icon="settings">
    <template v-slot:content>
      <p class="q-my-none"><q-input dense outlined clearable v-model.trim="dateFormatModel" :label="t('Date format')"
          @update:modelValue="saveDateFormat" /></p>
      <p class="q-my-md"><q-input dense outlined clearable v-model.trim="dateTimeFormatModel"
          :label="t('Datetime format')" @update:modelValue="saveDateTimeFormat" /></p>
      <p class="q-my-none"><q-toggle v-model="visibilityCheckModel" checked-icon="check" color="green"
          :label="t('Always show uploading dialog after adding files')" unchecked-icon="clear" class="q-mr-md" /></p>
      <p class="q-my-none"><q-toggle v-model="toolTipsCheckModel" checked-icon="check" color="green"
          :label="t('Show tooltips')" unchecked-icon="clear" class="q-mr-md" />
      </p>
    </template>
  </BaseWidget>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

import { useSessionStore } from "src/stores/session";
import { dateFormat as localStorageDateFormat, dateTimeFormat as localStorageDateTimeFormat } from "src/composables/localStorage";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";

const { t } = useI18n();

const sessionStore = useSessionStore();

const dateFormatModel = ref<string>(localStorageDateFormat.get());

const saveDateFormat = (val: string | number | null) => {
  if (typeof val === 'string' && val.length > 0) {
    localStorageDateFormat.set(val);
  } else {
    localStorageDateFormat.remove();
  }
};

const dateTimeFormatModel = ref<string>(localStorageDateTimeFormat.get());

const saveDateTimeFormat = (val: string | number | null) => {
  if (typeof val === 'string' && val.length > 0) {
    localStorageDateTimeFormat.set(val);
  } else {
    localStorageDateTimeFormat.remove();
  }
};

const visibilityCheckModel = computed({
  get() {
    return (sessionStore.openUploadDialog);
  },
  set(value: boolean) {
    sessionStore.setOpenUploadDialog(value);
  }
});


const toolTipsCheckModel = computed({
  get() {
    return (sessionStore.toolTipsEnabled);
  },
  set(value: boolean) {
    sessionStore.toggleToolTips(value);
  }
});


</script>