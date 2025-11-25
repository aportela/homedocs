<template>
  <div class="row q-col-gutter-x-sm">
    <div
      :class="{ 'col-6': createdOnTimestamp != lastUpdateTimestamp, 'col-12': createdOnTimestamp == lastUpdateTimestamp }">
      <q-input dense class="q-mb-md" outlined v-model="creationDateTime" :label="t('Created on')" readonly>
        <template v-slot:append v-if="isScreenGreaterThanXS">
          <span style="font-size: 14px;">
            {{ creationTimeAgo }}
          </span>
        </template>
      </q-input>
    </div>
    <div class="col-6" v-if="createdOnTimestamp != lastUpdateTimestamp">
      <q-input dense class="q-mb-md" outlined v-model="lastUpdate" :label="t('Last update')" readonly>
        <template v-slot:append v-if="isScreenGreaterThanXS">
          <span style="font-size: 14px;">
            {{ lastUpdateTimeAgo }}
          </span>
        </template>
      </q-input>
    </div>
  </div>
</template>

<script setup lang="ts">

import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";

import { useFormatDates } from "src/composables/useFormatDates";
import { dateTimeFormat as localStorageDateTimeFormat } from "src/composables/useLocalStorage";

interface DocumentMetadataTopFormProps {
  createdOnTimestamp?: number;
  lastUpdateTimestamp?: number;
};
const props = withDefaults(defineProps<DocumentMetadataTopFormProps>(), {
  createdOnTimestamp: 0,
  lastUpdateTimestamp: 0,
});

const { t } = useI18n();
const { screen } = useQuasar();

const { fullDateTimeHuman, timeAgo } = useFormatDates();

const isScreenGreaterThanXS = computed(() => screen.gt.xs);

const creationDateTime = ref<string | null>(props.createdOnTimestamp > 0 ? fullDateTimeHuman(props.createdOnTimestamp, localStorageDateTimeFormat.get()) : null);
const lastUpdate = ref<string | null>(props.lastUpdateTimestamp > 0 ? fullDateTimeHuman(props.lastUpdateTimestamp, localStorageDateTimeFormat.get()) : null);

const creationTimeAgo = ref<string | null>(props.createdOnTimestamp > 0 ? timeAgo(props.createdOnTimestamp) : null);
const lastUpdateTimeAgo = ref<string | null>(props.lastUpdateTimestamp > 0 ? timeAgo(props.lastUpdateTimestamp) : null);

watch(() => props.createdOnTimestamp, (val: number) => {
  creationDateTime.value = val > 0 ? fullDateTimeHuman(val, localStorageDateTimeFormat.get()) : null;
  creationTimeAgo.value = val > 0 ? timeAgo(val) : null;
});

watch(() => props.lastUpdateTimestamp, (val: number) => {
  lastUpdate.value = val > 0 ? fullDateTimeHuman(val, localStorageDateTimeFormat.get()) : null;
  lastUpdateTimeAgo.value = val > 0 ? timeAgo(val) : null;
});

</script>