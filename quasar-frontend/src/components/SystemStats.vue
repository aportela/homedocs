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
        <a className="button button--sm button--secondary margin-top--sm" href="#" @click.prevent="cal.previous()">←
          Previous</a>
        <a className="button button--sm button--secondary margin-top--sm margin-left--xs" href="#"
          @click.prevent="cal.next()">Next →
        </a>
        <div style="{ float: 'right' , fontSize: 12 }">
          <span style="{ color: '#768390' }">Less</span>
          <div id="ex-ghDay-legend" style="display: 'inline-block'; margin: '0 4px'"></div>
          <span style=" { color: '#768390' , fontSize: 12 }">More</span>
        </div>
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
import LegendLite from 'cal-heatmap/plugins/LegendLite';
import CalendarLabel from 'cal-heatmap/plugins/CalendarLabel';

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

refreshTotalDocuments();
refreshTotalAttachments();
refreshTotalAttachmentsDiskUsage();

function generateRandomData() {
  const data = [];
  const now = new Date();

  for (let i = 0; i < 365; i++) {
    const date = new Date(now);
    date.setDate(now.getDate() - i);

    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');

    const value = Math.floor(Math.random() * 4); // entre 0 y 3

    data.push({
      date: `${yyyy}-${mm}-${dd}`,
      value: value,
    });
  }
  return data.sort((a, b) => new Date(a.date) - new Date(b.date));
}

const cal = new CalHeatmap();
cal.paint(
  {
    data: {
      source: generateRandomData(),
      x: "date",
      y: "value",
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
        scheme: "Cool",
        type: 'threshold',
        range: ['#14432a', '#166b34', '#37a446', '#4dd05a'],
        domain: [0, 1, 2, 3],
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
            ' activity on ' +
            dayjsDate.format('dddd, MMMM D, YYYY')
          );
        },
      },
    ],
    [
      LegendLite,
      {
        includeBlank: true,
        itemSelector: '#ex-ghDay-legend',
        radius: 2,
        width: 11,
        height: 11,
        gutter: 4,
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

</script>