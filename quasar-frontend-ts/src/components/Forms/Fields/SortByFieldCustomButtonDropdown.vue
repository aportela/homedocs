<template>
  <q-btn-dropdown v-bind="attrs" :label="currentLabel" :dense="dense">
    <q-list>
      <q-item :dense="dense" v-close-popup v-for="(option, index) in completeOptions" :key="index"
        @click="onClick(option)" :clickable="!(option.field == currentSort.field && option.order == currentSort.order)"
        :disable="option.field == currentSort.field && option.order == currentSort.order">
        <q-item-section avatar>
          <q-icon :name="option.order == 'ASC' ? 'keyboard_double_arrow_up' : 'keyboard_double_arrow_down'" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t("Current sort by", {
            label: t(option.label), order: option.order == "DESC" ?
              t("descending") : t("ascending")
          }) }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup lang="ts">

import { computed, reactive, watch, useAttrs } from "vue";
import { useI18n } from "vue-i18n";

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['change'])

const props = defineProps({
  options: {
    type: Array,
    required: true,
    validator(value) {
      return Array.isArray(value);
    }
  },
  current: {
    type: Object,
    required: true
  },
  dense: {
    type: Boolean,
    required: false,
    default: false
  }
});

const currentSort = reactive({
  field: props.current?.field || null,
  label: props.current?.label || null,
  order: props.current?.order || null
});

if (!currentSort.label) {
  currentSort.label = (props.options.find((option) => option.field == currentSort.field))?.label;
}

const currentLabel = computed(() =>
  t("Current sort by",
    {
      label: currentSort.label ? t(currentSort.label) : null,
      order: currentSort.order ? t(currentSort.order == "DESC" ? "descending" : "ascending") : null
    }
  )
);
const onClick = (option) => {
  currentSort.field = option.field;
  currentSort.label = option.label;
  currentSort.order = option.order;
  emit("change", currentSort);
};

watch(() => props.current, val => { currentSort.field = val.field; currentSort.label = val.label; currentSort.order = val.order });

const completeOptions = reactive([]);

props.options?.forEach((option) => {
  completeOptions.push({
    field: option.field,
    label: option.label,
    order: "ASC"
  });
  completeOptions.push({
    field: option.field,
    label: option.label,
    order: "DESC"
  })
});


</script>
