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

import { computed, reactive, useAttrs } from "vue";
import { useI18n } from "vue-i18n";
import { type OrderType } from "src/types/order-type";

const attrs = useAttrs();

const { t } = useI18n();

const emit = defineEmits(['change'])

interface Option {
  field: string;
  label: string;
  order?: OrderType | undefined;
}

class OptionClass implements Option {
  field: string;
  label: string;
  order?: OrderType | undefined;

  constructor(field: string, label: string, order?: OrderType) {
    this.field = field;
    this.label = label;
    if (order) {
      this.order = order;
    }
  }
}

interface SortByFieldCustomButtonDropdownProps {
  options: Option[];
  current: Option;
  dense?: boolean;
};

const props = withDefaults(defineProps<SortByFieldCustomButtonDropdownProps>(), {
  dense: false,
});

const completeOptions = reactive<Array<Option>>([]);

props.options?.forEach((option) => {
  completeOptions.push(new OptionClass(option.field, option.label, "ASC"));
  completeOptions.push(new OptionClass(option.field, option.label, "DESC"));
});

const currentSort = computed({
  get() {
    return props.current;
  },
  set() {
    /* */
  }
});

const currentLabel = computed(() =>
  t("Current sort by",
    {
      label: currentSort.value.label ? t(currentSort.value.label) : null,
      order: currentSort.value.order ? t(currentSort.value.order == "DESC" ? "descending" : "ascending") : null
    }
  )
);

const onClick = (option: Option) => {
  emit("change", option);
};

</script>
