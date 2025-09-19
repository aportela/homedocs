<template>
  <div id="cal-heatmap-container" class="calContainerRef">
    <div id="cal-heatmap"></div>
    <div class="q-mb-md q-gutter-sm q-py-sm" v-if="showNavigationButtons">
      <q-btn icon="arrow_left" :disabled="leftButtonDisabled" size="md" color="primary"
        @click.prevent="onLeftButtonClicked">Previous</q-btn>
      <q-btn icon-right="arrow_right" :disabled="rightButtonDisabled" size="md" color="primary"
        @click.prevent="onRightButtonClicked">Next</q-btn>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { Dark } from "quasar";

import { i18n } from "boot/i18n";
import { api } from "boot/axios";

import CalHeatmap from "cal-heatmap";
import "cal-heatmap/cal-heatmap.css";
import Tooltip from 'cal-heatmap/plugins/Tooltip';
import CalendarLabel from 'cal-heatmap/plugins/CalendarLabel';

const router = useRouter();

const { t } = useI18n();

const props = defineProps({
  showNavigationButtons: {
    type: Boolean,
    required: false,
    default: true
  }
});

const loading = ref(false);
const error = ref(false);

const leftButtonDisabled = ref(false);
const rightButtonDisabled = ref(false);

const currentLocale = computed(() => i18n.global.locale.value.substring(0, 2));

const calDefaultOptions = {
  itemSelector: '#cal-heatmap',
};

const calOptions = ref({
  date: {
    locale: currentLocale.value,
    start: new Date(new Date().setFullYear(new Date().getFullYear() - 1)),
    end: new Date(),
    max: new Date(),
  },
  range: 13, // visibility: 12 months
  scale: {
    color: {
      scheme: 'greens',
      type: 'threshold',
      domain: null,
    },
  },
  domain: {
    type: 'month',
    gutter: 4,
    label: { text: 'MMM', textAlign: 'start', position: 'top' },
  },
  subDomain: { type: 'ghDay', radius: 2, width: 11, height: 11, gutter: 4 },
  theme: Dark.isActive ? "dark" : "light"
});

const calDefaultPlugins = [
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
];

let cal = new CalHeatmap(calDefaultOptions);

// UGLY-HACK
// official dynamic cal-heatmap theme toggle is not supported (https://cal-heatmap.com/docs/options/theme)
watch(() => Dark.isActive, val => {
  cal.destroy();
  cal = new CalHeatmap(calDefaultOptions);
  onCalRefresh();
});

// UGLY-HACK
// day/month labels not updated on i18n changes
watch(() => currentLocale.value, val => {
  cal.destroy();
  cal = new CalHeatmap(calDefaultOptions);
  onCalRefresh();
});

/*
// TODO: change cal-heatmap-container max-width style for avoiding scrollbar appearing on button navigation
cal.on('resize', (newW, newH, oldW, oldH) => {
  console.log(
    `Calendar has been resized from ${oldW}x${oldH} to ${newW}x${newH}`
  );

});
*/

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

cal.on('minDateReached', () => {
  leftButtonDisabled.value = true;
});

cal.on('minDateNotReached', () => {
  leftButtonDisabled.value = false;
});

cal.on('maxDateReached', () => {
  rightButtonDisabled.value = true;
});

cal.on('maxDateNotReached', () => {
  rightButtonDisabled.value = false;
});

const onCalRefresh = (data, scaleDomain) => {
  if (data) {
    calOptions.value.data = data;
  }
  if (scaleDomain) {
    calOptions.value.scale.color.domain = scaleDomain;
  }
  calOptions.value.date.locale = currentLocale.value;
  calOptions.value.theme = Dark.isActive ? "dark" : "light";
  cal.paint(
    calOptions.value,
    calDefaultPlugins
  );
};

const onLeftButtonClicked = () => {
  cal.previous();
};

const onRightButtonClicked = () => {
  cal.next();
};


const refresh = () => {
  loading.value = true;
  error.value = false;
  api.stats.getActivityHeatMapData()
    .then((success) => {
      const counts = success.data.heatmap.map(d => d.count);
      //const min = Math.min(...counts);
      //const max = Math.max(...counts);
      let scaleDomain = [...new Set(counts)];
      scaleDomain.unshift(0);
      scaleDomain.sort(function (a, b) { return a - b; });
      onCalRefresh({
        source: success.data.heatmap,
        x: "date",
        y: "count",
        type: "json",
      }, scaleDomain);
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      error.value = true;
    });
}

onMounted(() => {
  refresh();
});

</script>

<style scoped>
#cal-heatmap-container {
  max-width: 882px;
  overflow-x: auto;
}

#cal-heatmap {
  width: 870px;
  height: 136px;
}
</style>