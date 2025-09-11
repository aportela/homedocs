<template>
  <div id="cal-heatmap"></div>
  <!--
    <q-btn-group flat dense class="q-mt-sm">
      <q-btn icon="arrow_left" @click.prevent="cal.previous()">Previous</q-btn>
      <q-btn icon-right="arrow_right" @click.prevent="cal.next()">Next</q-btn>
    </q-btn-group>
    -->
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

import { api } from "boot/axios";

import CalHeatmap from "cal-heatmap";
import "cal-heatmap/cal-heatmap.css";
import Tooltip from 'cal-heatmap/plugins/Tooltip';
import CalendarLabel from 'cal-heatmap/plugins/CalendarLabel';

const router = useRouter();

const { t } = useI18n();

const activityHeatMapData = ref([]);

const cal = new CalHeatmap();

const loadingError = ref(false);
const loading = ref(false);

cal.on('click', (event, timestamp, value) => {
  if (value > 0) {
    router.push({
      name: "advancedSearchByFixedDate",
      params: {
        fixedDate: new Date(timestamp).toISOString().split('T')[0]
      }
    });
  }
});

function refresh() {
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
                return (value
                  ? `${value} ${t(" change/s on date ")} ${dayjsDate.format('dddd, MMMM D, YYYY')}`
                  : `${t("No activity on date ")} ${dayjsDate.format('dddd, MMMM D, YYYY')}`);
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

onMounted(() => {
  refresh();
});

</script>

<style scoped>
#cal-heatmap {
  width: 810px;
  height: 136px;
}
</style>