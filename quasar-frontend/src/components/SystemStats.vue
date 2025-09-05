<template>
  <div class="fit">
    <q-card class="q-ma-xs" flat bordered>
      <q-card-section>
        <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
          :icon="loadingError ? 'error' : 'analytics'" :label="t('System stats')"
          :caption="t(loadingError ? 'Error loading data' : 'Your detailed data info')" :model-value="expanded">
          <p class="text-center" v-if="loading">
            <q-spinner-pie v-if="loading" color="grey-5" size="md" />
          </p>
          <div v-else>
            <ul>
              <li>Total documents: {{ totalDocuments }}</li>
              <li>Total attachments: {{ totalAttachments }}</li>
              <li>Total attachments disk usage: {{ totalAttachmentsDiskUsage }}</li>
            </ul>
          </div>
        </q-expansion-item>
        <div id="cal-heatmap"></div>
        <!--
        <q-btn-group flat dense class="q-mt-sm">
          <q-btn icon="arrow_left" @click.prevent="cal.previous()">Previous</q-btn>
          <q-btn icon-right="arrow_right" @click.prevent="cal.next()">Next</q-btn>
        </q-btn-group>
        -->
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { format, useQuasar } from "quasar"
import { api } from "boot/axios";

import CalHeatmap from "cal-heatmap";
import "cal-heatmap/cal-heatmap.css";
import Tooltip from 'cal-heatmap/plugins/Tooltip';
import CalendarLabel from 'cal-heatmap/plugins/CalendarLabel';

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

let expanded = !$q.screen.lt.md;
const totalDocuments = ref(0);
const totalAttachments = ref(0);
const totalAttachmentsDiskUsage = ref(0);
const activityHeatMapData = ref([]);

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

const cal = new CalHeatmap();

cal.on('click', (event, timestamp, value) => {
  console.log(
    'On <b>' +
    new Date(timestamp).toLocaleDateString() +
    '</b>, the max temperature was ' +
    value +
    '°C'
  );
});

function refreshActivityHeatmapData() {
  activityHeatMapData.value = [];
  loading.value = true;
  loadingError.value = false;
  api.stats.getActivityHeatMapData()
    .then((success) => {
      activityHeatMapData.value = success.data.heatmap;
      const maxValue = activityHeatMapData.value.reduce((max, obj) => (obj.count > max ? obj.count : max), -Infinity)
      cal.paint(
        {
          data: {
            source: activityHeatMapData.value,
            x: "date",
            y: "count",
            type: "json",
          },
          date: {
            start: new Date(new Date().setFullYear(new Date().getFullYear() - 1)),
            end: new Date(),
            max: new Date()
          },
          range: 12,
          scale: {
            color: {
              scheme: 'Greens',
              type: "linear",
              domain: [0, maxValue],
            },
          },
          domain: {
            type: 'month',
            gutter: 4,
            label: { text: 'MMM', textAlign: 'start', position: 'top' },
          },
          subDomain: { type: 'ghDay', radius: 2, width: 11, height: 11, gutter: 4 },
          itemSelector: '#cal-heatmap',
        },
        [
          [
            Tooltip,
            {
              text: function (date, value, dayjsDate) {
                return (
                  (value ? value : 'No') +
                  ' changes on ' +
                  dayjsDate.format('dddd, MMMM D, YYYY')
                );
              },
            },
          ],
          [
            CalendarLabel,
            {
              width: 30,
              textAlign: 'start',
              text: () => dayjs.weekdaysShort().map((d, i) => (i % 2 == 0 ? '' : d)),
              padding: [25, 0, 0, 0],
            },
          ],
        ]
      );

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

refreshTotalDocuments();
refreshTotalAttachments();
refreshTotalAttachmentsDiskUsage();
refreshActivityHeatmapData();

</script>