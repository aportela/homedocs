<template>
  <q-btn-dropdown v-bind="attrs" :label="currentLabel" :dense="dense">
    <q-list>
      <q-item :dense="dense"
        :clickable="completeOptions.field != current.field && completeOptions.order != current.order" v-close-popup
        v-for="(option, index) in completeOptions" :key="index" @click="onClick(option)">
        <q-item-section avatar>
          <q-icon :name="option.order == 'ASC' ? 'keyboard_double_arrow_up' : 'keyboard_double_arrow_down'" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t(option.label) }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>

import { computed, reactive, useAttrs } from "vue";
import { useI18n } from "vue-i18n";

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['change'])

const props = defineProps({
  options: {
    type: Array,
    required: true
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

const currentLabel = computed(() => {
  return (t("Order by: none"))
});

const onClick = (option) => {
  console.log(option);
  emit("change", { field: option.field, order: option.order })
};

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