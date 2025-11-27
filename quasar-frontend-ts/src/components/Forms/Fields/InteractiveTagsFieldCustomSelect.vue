<template>
  <div v-if="readOnly" class="cursor-pointer q-pa-sm relative-position white-space-pre-line read-only-input-container"
    @mouseenter="showUpdateHoverIcon = true" @mouseleave="showUpdateHoverIcon = false" @click="onToggleReadOnly">
    <div class="readonly-label">
      {{ t(label) }}</div>
    <q-icon v-if="!denyChangeEditableMode" name="edit" size="sm"
      class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm" v-show="showUpdateHoverIcon">
      <DesktopToolTip>{{ t("Click to toggle edit mode") }}</DesktopToolTip>
    </q-icon>
    <BrowseByTagButton v-for="tag in tags" :key="tag" :tag="tag" icon="tag" />
  </div>
  <q-select v-else ref="selectRef" :label="t(label)" v-model="tags" :dense="dense" :options-dense="dense" outlined
    use-input use-chips multiple hide-dropdown-icon :options="filteredTags" input-debounce="0"
    new-value-mode="add-unique" :disable="disabled || state.ajaxRunning || state.ajaxErrors"
    :loading="state.ajaxRunning" :error="state.ajaxErrors" :error-message="t('Error loading available tags')"
    @filter="onFilterTags" @add="onAddTag">
    <template v-slot:prepend>
      <slot name="prepend">
        <q-icon name="tag" />
      </slot>
    </template>
    <template v-slot:append v-if="!denyChangeEditableMode">
      <q-icon name="done" class="cursor-pointer" @click="onToggleReadOnly">
        <DesktopToolTip>{{ t("Click to toggle edit mode") }}</DesktopToolTip>
      </q-icon>
    </template>
    <template v-slot:selected>
      <q-chip square :removable="!readOnly" :dense="dense" v-for="tag, index in tags" :key="tag"
        @remove="removeTagAtIndex(index)" color="primary" text-color="white" icon="tag" :label="tag">
      </q-chip>
    </template>
  </q-select>
</template>

<script setup lang="ts">

import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { QSelect } from "quasar";

import { bus } from "src/composables/bus";
import { api } from "src/composables/api";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type GetTagsResponse as GetTagsResponseInterface } from "src/types/api-responses";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";

const { t } = useI18n();

interface InteractiveTagsFieldCustomSelectProps {
  startModeEditable?: boolean;
  denyChangeEditableMode?: boolean;
  modelValue: string[];
  disabled?: boolean;
  dense?: boolean;
  label?: string;
};

const props = withDefaults(defineProps<InteractiveTagsFieldCustomSelectProps>(), {
  startModeEditable: false,
  denyChangeEditableMode: false,
  disabled: false,
  delse: false,
  label: "Document tags",
});

const emit = defineEmits(['update:modelValue', 'error']);

const showUpdateHoverIcon = ref(false);

const readOnly = ref(!props.startModeEditable);
const selectRef = ref<QSelect | null>(null);

const tags = computed({
  get() {
    return props.modelValue || [];
  },
  set(value) {
    emit('update:modelValue', value || []);
  }
});

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const availableTags = reactive<Array<string>>([]);
const filteredTags = reactive<Array<string>>([]);

function onFilterTags(inputValue: string, doneFn: (callbackFn: () => void, afterFn?: (ref: QSelect) => void) => void, abortFn: () => void): void {
  const filtered = inputValue === ''
    ? availableTags
    : availableTags.filter(tag => tag.toLowerCase().includes(inputValue.toLowerCase()));

  doneFn(() => {
    filteredTags.length = 0;
    filteredTags.push(...filtered);
  });
}

function onRefresh() {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.tag.search()
      .then((successResponse: GetTagsResponseInterface) => {
        availableTags.length = 0;
        availableTags.push(...successResponse.data.tags);
      }).catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "InteractiveTagsFieldCustomSelect" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        emit('error', errorResponse.response);
      }).finally(() => {
        state.ajaxRunning = false;
      });
  }
}

function onAddTag(details: { index: number; value: any; }): void {
  selectRef.value?.hidePopup()
  selectRef.value?.reset();
  selectRef.value?.updateInputValue("");
}

function removeTagAtIndex(index: number) {
  tags.value?.splice(index, 1);
}

function focus() {
  nextTick()
    .then(() => {
      selectRef.value?.focus()
    }).catch((e) => {
      console.error(e);
    });
}

defineExpose({
  focus
});

function onToggleReadOnly() {
  readOnly.value = !readOnly.value;
  if (!readOnly.value) {
    focus();
  }
}

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("InteractiveTagsFieldCustomSelect")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
.readonly-label {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.6);
  margin-left: 0px;
  margin-bottom: 4px;
}

.read-only-input-container {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
}

.body--dark {
  .read-only-input-container {
    border: 1px solid rgba(255, 255, 255, 0.28);
  }

  .readonly-label {
    color: rgba(255, 255, 255, 0.7);
  }
}
</style>