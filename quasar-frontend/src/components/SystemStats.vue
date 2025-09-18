<template>
  <div class="fit">
    <q-card>
      <q-item class="theme-default-q-card-section-header">
        <q-item-section avatar>
          <q-icon :name="loadingError ? 'error' : 'analytics'"></q-icon>
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t('Your system stats') }}</q-item-label>
          <q-item-label caption>{{ t(loadingError ? 'Error loading data' : "Small resume of your platform usage")
            }}</q-item-label>
        </q-item-section>
      </q-item>
      <q-separator />
      <q-card-section>
        <q-card-section class="text-center text-h6"><q-icon name="insights"></q-icon> {{ t("Activity Heatmap")
          }}</q-card-section>
        <q-card-section class="bg-white">
          <ActivityHeatMap class="q-mx-auto"></ActivityHeatMap>
        </q-card-section>
      </q-card-section>
      <q-card-section>
        <div class="row">
          <div class="col-4">
            <q-card class="bg-grey-3 q-ma-sm">
              <q-card-section class="text-center text-h6">
                <q-icon name="shelves"></q-icon>
                {{ t("Total documents") }}
              </q-card-section>
              <q-separator inset />
              <q-card-section class="text-center text-h4">{{ totalDocuments }}</q-card-section>
            </q-card>
          </div>
          <div class="col-4">
            <q-card class="bg-grey-3 q-ma-sm">
              <q-card-section class="text-center text-h6">
                <q-icon name="attachment"></q-icon>
                {{ t("Total attachments") }}
              </q-card-section>
              <q-separator inset />
              <q-card-section class="text-center text-h4">{{ totalAttachments }}</q-card-section>
            </q-card>
          </div>
          <div class="col-4">
            <q-card class="bg-grey-3 q-ma-sm ">
              <q-card-section class="text-center text-h6">
                <q-icon name="data_usage"></q-icon>
                {{ t("Disk usage") }}
              </q-card-section>
              <q-separator inset />
              <q-card-section class="text-center text-h4">{{ totalAttachmentsDiskUsage }}</q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>

import { ref, onMounted } from "vue";

import { useI18n } from "vue-i18n";
import { format, useQuasar } from "quasar"
import { api } from "boot/axios";

import ActivityHeatMap from 'components/ActivityHeatMap.vue';

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

let expanded = !$q.screen.lt.md;
const totalDocuments = ref(0);
const totalAttachments = ref(0);
const totalAttachmentsDiskUsage = ref(0);

function refreshTotalDocuments() {
  totalDocuments.value = 0;
  loading.value = true;
  loadingError.value = false;
  api.stats.documentCount()
    .then((success) => {
      totalDocuments.value = success.data.count;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

function refreshTotalAttachments() {
  totalAttachments.value = 0;
  loading.value = true;
  loadingError.value = false;
  api.stats.attachmentCount()
    .then((success) => {
      totalAttachments.value = success.data.count;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

function refreshTotalAttachmentsDiskUsage() {
  totalAttachments.value = 0;
  loading.value = true;
  loadingError.value = false;
  api.stats.attachmentDiskSize()
    .then((success) => {
      totalAttachmentsDiskUsage.value = format.humanStorageSize(success.data.size);
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

onMounted(() => {
  refreshTotalDocuments();
  refreshTotalAttachments();
  refreshTotalAttachmentsDiskUsage();
});

</script>