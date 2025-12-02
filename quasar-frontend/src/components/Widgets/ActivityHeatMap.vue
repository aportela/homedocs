<template>
  <div id="cal-heatmap-container" class="calContainerRef">
    <div id="cal-heatmap"></div>
    <div class="q-mb-md q-gutter-sm q-py-sm" v-if="showNavigationButtons">
      <q-btn icon="arrow_left" :disabled="state.ajaxRunning || leftButtonDisabled" size="md" color="primary"
        @click.prevent="onLeftButtonClicked">{{ t("Previous") }}</q-btn>
      <q-btn icon-right="arrow_right" :disabled="state.ajaxRunning || rightButtonDisabled" size="md" color="primary"
        @click.prevent="onRightButtonClicked">{{ t("Next") }}</q-btn>
    </div>
    <CustomErrorBanner v-if="state.ajaxErrors" :text="state.ajaxErrorMessage || 'Error loading data'"
      :api-error="state.ajaxAPIErrorDetails">
    </CustomErrorBanner>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { date } from "quasar";
import { type Dayjs } from 'dayjs';

import { api } from "src/composables/api";
import { bus } from "src/composables/bus";
import { useDarkModeStore } from "src/stores/darkMode";
import { useI18nStore } from "src/stores/i18n";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type GetActivityHeatMapDataResponseItem as GetActivityHeatMapDataResponseItemInterface, type GetActivityHeatMapDataResponse as GetActivityHeatMapDataResponseInterface } from "src/types/api-responses";
// @ts-expect-error: `cCalHeatmap` is missing TypeScript type definitions
import { default as CalHeatmap } from "cal-heatmap";
import "cal-heatmap/cal-heatmap.css";
// @ts-expect-error: `Tooltip` is missing TypeScript type definitions
import { default as Tooltip } from "cal-heatmap/plugins/Tooltip";
// @ts-expect-error: `CalendarLabel` is missing TypeScript type definitions
import { default as CalendarLabel } from "cal-heatmap/plugins/CalendarLabel";

import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const router = useRouter();
const { t } = useI18n();
const darkModeStore = useDarkModeStore();
const i18NStore = useI18nStore();

const emit = defineEmits(['loading', 'loaded', 'error']);

interface ActivityHeatMapProps {
  showNavigationButtons?: boolean
};

withDefaults(defineProps<ActivityHeatMapProps>(), {
  showNavigationButtons: true
});

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const leftButtonDisabled = ref<boolean>(false);
const rightButtonDisabled = ref<boolean>(false);

const currentLocale = computed(() => i18NStore.currentLocale.substring(0, 2));

const calDefaultOptions = {
  itemSelector: '#cal-heatmap',
};

// last 2 years
const fromDate = (new Date(new Date().setFullYear(new Date().getFullYear() - 2)));
fromDate.setDate(1);
fromDate.setHours(0, 0, 0, 0);

interface DataInterface {
  source: GetActivityHeatMapDataResponseItemInterface[];
  x: string;
  y: string;
  type: string;
};

interface CalOptions {
  data: DataInterface;
  date: {
    locale: string;
    start: Date;
    end: Date;
    min: Date;
    max: Date;
  };
  range: number;
  scale: {
    color: {
      scheme: string;
      type: string;
      domain: number[] | null;
    };
  };
  domain: {
    type: string;
    gutter: number;
    label: {
      text: string;
      textAlign: string;
      position: string;
    };
  };
  subDomain: {
    type: string;
    radius: number;
    width: number;
    height: number;
    gutter: number;
  };
  theme: string;
};

const calOptions: CalOptions = reactive<CalOptions>({
  data: {
    source: [],
    x: "date",
    y: "count",
    type: "json",
  },
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
      domain: null as number[] | null,
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
      text: function (timestamp: number, value: number, dayjsDate: Dayjs) {
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

let cal: CalHeatmap = new CalHeatmap(calDefaultOptions);

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


const onCalRefresh = (dataSource?: GetActivityHeatMapDataResponseItemInterface[], scaleColorDomain?: number[]) => {
  if (dataSource) {
    calOptions.data.source = dataSource;
  }
  if (scaleColorDomain) {
    calOptions.scale.color.domain = scaleColorDomain;
  }
  calOptions.date.locale = currentLocale.value;
  calOptions.theme = darkModeStore.isActive ? "dark" : "light";
  cal.paint(
    calOptions,
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
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  api.stats.getActivityHeatMapData(Number(date.formatDate(fromDate, 'x')))
    .then((successResponse: GetActivityHeatMapDataResponseInterface) => {
      const counts = successResponse.data.heatmap.map(d => d.count);
      //const min = Math.min(...counts);
      //const max = Math.max(...counts);
      const scaleDomain = [...new Set(counts)];
      scaleDomain.unshift(0);
      scaleDomain.sort(function (a, b) { return a - b; });
      onCalRefresh(successResponse.data.heatmap, scaleDomain);
      emit("loaded");
    })
    .catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "ActivityHeatMapWidget" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      emit("error");
    }).finally(() => {
      state.ajaxRunning = false;
    });
}

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("ActivityHeatMapWidget")) {
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