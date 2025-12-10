<template>
  <div class="row q-col-gutter-x-sm">
    <div v-if="createdAt" :class="{ 'col-6': showBothDates, 'col-12': !showBothDates }">
      <q-input dense class="q-mb-md" outlined v-model="createdAtModel" :label="t('Created on')" readonly>
        <template v-slot:append v-if="isScreenGreaterThanXS">
          <span style="font-size: 14px;">
            {{ createdAt?.timeAgo }}
          </span>
        </template>
      </q-input>
    </div>
    <div class="col-6" v-if="updatedAtModel">
      <q-input dense class="q-mb-md" outlined v-model="updatedAtModel" :label="t('Last update')" readonly>
        <template v-slot:append v-if="isScreenGreaterThanXS">
          <span style="font-size: 14px;">
            {{ updatedAt?.timeAgo }}
          </span>
        </template>
      </q-input>
    </div>
  </div>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";

import { type DateTime as DateTimeInterface } from "src/types/dateTime";

interface DocumentMetadataTopFormProps {
  createdAt: DateTimeInterface | null;
  updatedAt: DateTimeInterface | null;
};

const props = defineProps<DocumentMetadataTopFormProps>();

const { t } = useI18n();
const { screen } = useQuasar();

const isScreenGreaterThanXS = computed(() => screen.gt.xs);

const showBothDates = computed(() => props.createdAt?.timestamp != props.updatedAt?.timestamp);

const createdAtModel = computed({
  get() {
    return props.createdAt?.dateTime;
  },
  set() {
    /* */
  }
});

const updatedAtModel = computed({
  get() {
    return props.createdAt?.dateTime;
  },
  set() {
    /* */
  }
});

</script>
