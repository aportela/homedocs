<template>
  <q-btn-dropdown v-bind="attrs" :label="currentLabel" :dense="dense">
    <q-list>
      <q-item :dense="dense" v-close-popup v-for="(option, index) in options" :key="index" @click="onClick(option)"
        :clickable="!(option.field == currentSort.field && option.order == currentSort.order)"
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

  import { computed, useAttrs } from "vue";
  import { useI18n } from "vue-i18n";
  import { type Sort as SortInterface } from "src/types/sort";

  const attrs = useAttrs();

  const { t } = useI18n();

  const emit = defineEmits(['change'])

  // TODO: v-model ?
  interface SortByFieldCustomButtonDropdownProps {
    options: SortInterface[];
    current: SortInterface;
    dense?: boolean;
  };

  const props = withDefaults(defineProps<SortByFieldCustomButtonDropdownProps>(), {
    dense: false,
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

  const onClick = (option: SortInterface) => {
    emit("change", option);
  };

</script>