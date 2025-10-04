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

<script setup>

import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";

import { useFormatDates } from "src/composables/useFormatDates";

const props = defineProps({
  createdOnTimestamp: {
    type: Number,
    required: false,
    default: 0,
    validator(value) {
      return (value > 0);
    }
  },
  lastUpdateTimestamp: {
    type: Number,
    required: false,
    default: 0,
    validator(value) {
      return (value > 0);
    }
  }
});

const { t } = useI18n();
const { screen } = useQuasar();

const { fullDateTimeHuman, timeAgo } = useFormatDates();

const isScreenGreaterThanXS = computed(() => screen.gt.xs);

const creationDateTime = ref(props.createdOnTimestamp ? fullDateTimeHuman(props.createdOnTimestamp) : null);
const lastUpdate = ref(props.lastUpdateTimestamp ? fullDateTimeHuman(props.lastUpdateTimestamp) : null);

const creationTimeAgo = ref(props.createdOnTimestamp ? timeAgo(props.createdOnTimestamp) : null);
const lastUpdateTimeAgo = ref(props.lastUpdateTimestamp ? timeAgo(props.lastUpdateTimestamp) : null);

watch(() => props.createdOnTimestamp, val => {
  creationDateTime.value = val ? fullDateTimeHuman(val) : null;
  creationTimeAgo.value = val ? timeAgo(val) : null;
});

watch(() => props.lastUpdateTimestamp, val => {
  lastUpdate.value = val ? fullDateTimeHuman(val) : null;
  lastUpdateTimeAgo.value = val ? timeAgo(val) : null;
});

</script>
