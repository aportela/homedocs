<template>
  <div id="cal-heatmap-container" class="calContainerRef">
    <div id="cal-heatmap"></div>
    <div class="q-mb-md q-gutter-sm q-py-sm" v-if="showNavigationButtons">
      <q-btn icon="arrow_left" :disabled="state.loading || leftButtonDisabled" size="md" color="primary"
        @click.prevent="onLeftButtonClicked">{{ t("Previous") }}</q-btn>
      <q-btn icon-right="arrow_right" :disabled="state.loading || rightButtonDisabled" size="md" color="primary"
        @click.prevent="onRightButtonClicked">{{ t("Next") }}</q-btn>
    </div>
    <CustomErrorBanner v-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
      :api-error="state.apiError">
    </CustomErrorBanner>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { date } from "quasar";

import { useAPI } from "src/composables/useAPI";
import { useBus } from "src/composables/useBus";
import { useDarkModeStore } from "src/stores/darkMode";
import { useI18nStore } from "src/stores/i18n";
import type { APIErrorDetails as APIErrorDetailsInterface } from "src/types/api-error-details";

import CalHeatmap from "cal-heatmap";
import "cal-heatmap/cal-heatmap.css";
import Tooltip from "cal-heatmap/plugins/Tooltip";
import CalendarLabel from "cal-heatmap/plugins/CalendarLabel";

import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const router = useRouter();
const { t } = useI18n();
const darkModeStore = useDarkModeStore();
const { api } = useAPI();
const { bus } = useBus();
const i18NStore = useI18nStore();

const emit = defineEmits(['loading', 'loaded', 'error']);

interface ActivityHeatMapProps {
  showNavigationButtons?: boolean
};

withDefaults(defineProps<ActivityHeatMapProps>(), {
  showNavigationButtons: true
});

interface State {
  loading: boolean,
  loadingError: boolean,
  errorMessage: string | null,
  apiError: APIErrorDetailsInterface | null
};

const state: State = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const leftButtonDisabled = ref(false);
const rightButtonDisabled = ref(false);

const currentLocale = computed(() => i18NStore.currentLocale.substring(0, 2));

const calDefaultOptions = {
  itemSelector: '#cal-heatmap',
};

// last 2 years
const fromDate = (new Date(new Date().setFullYear(new Date().getFullYear() - 2)));
fromDate.setDate(1);
fromDate.setHours(0, 0, 0, 0);

const calOptions = ref({
  date: {
    locale: currentLocale.value,
    start: new Date(new Date().setFullYear(new Date().getFullYear() - 1)), // last 12 months
    end: new Date(),
    min: fromDate,
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
  theme: darkModeStore.isActive ? "dark" : "light"
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
      text: () => Array.from({ length: 7 }, (_, i) => new Date(2023, 0, i + 1).toLocaleDateString(currentLocale.value, { weekday: 'short' })).map((d, i) => (i % 2 == 0 ? '' : d)),
      padding: [25, 0, 0, 0],
    },
  ],
];

let cal = new CalHeatmap(calDefaultOptions);

// UGLY-HACK
// official dynamic cal-heatmap theme toggle is not supported (https://cal-heatmap.com/docs/options/theme)
watch(() => darkModeStore.isActive, () => {
  cal.destroy();
  cal = new CalHeatmap(calDefaultOptions);
  onCalRefresh();
});

// UGLY-HACK
// day/month labels not updated on i18n changes
watch(() => currentLocale.value, () => {
  cal.destroy();
  cal = new CalHeatmap(calDefaultOptions);
  onCalRefresh();
});

/*
// TODO: change cal-heatmap-container max-width style for avoiding scrollbar appearing on button navigation
cal.on('resize', (newW, newH, oldW, oldH) => {
   onsole.log(
     Calendar has been resized from ${oldW}x${oldH} to ${newW}x${newH}`
   ;
});
*/

cal.on('click', (event: Event, timestamp: number, value: number) => {
  if (value > 0) {
    router.push({
      name: "advancedSearchByFixedUpdatedOn",
      params: {
        fixedUpdatedOn: new Date(timestamp).toISOString().split('T')[0]
      }
    }).catch((e) => {
      console.error(e);
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

const onCalRefresh = (data?, scaleDomain?) => {
  if (data) {
    calOptions.value.data = data;
  }
  if (scaleDomain) {
    calOptions.value.scale.color.domain = scaleDomain;
  }
  calOptions.value.date.locale = currentLocale.value;
  calOptions.value.theme = darkModeStore.isActive ? "dark" : "light";
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

const onRefresh = () => {
  emit("loading");
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  api.stats.getActivityHeatMapData(date.formatDate(fromDate, 'x'))
    .then((successResponse) => {
      const counts = successResponse.data.heatmap.map(d => d.count);
      //const min = Math.min(...counts);
      //const max = Math.max(...counts);
      const scaleDomain = [...new Set(counts)];
      scaleDomain.unshift(0);
      scaleDomain.sort(function (a, b) { return a - b; });
      onCalRefresh({
        source: successResponse.data.heatmap,
        x: "date",
        y: "count",
        type: "json",
      }, scaleDomain);
      state.loading = false;
      emit("loaded");
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      if (errorResponse.isAPIError) {
        switch (errorResponse.response.status) {
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "ActivityHeatMap" });
            break;
          default:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.errorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      state.loading = false;
      emit("error");
    });
}

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("ActivityHeatMap")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
#cal-heatmap-container {
  max-width: 882px;
  overflow-x: auto;
}

#cal-heatmap {
  width: 870px;
  height: 136px;
}
</style>