<template>
  <q-expansion-item class="full-width theme-default-q-expansion-item" style="width: 100%"
    header-class="theme-default-q-expansion-item-header" expand-separator v-model="isExpanded">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon v-if="loading" name="settings" class="animation-spin"></q-icon>
        <q-icon v-else-if="error" name="error" color="red" @click.stop="handleClick"
          :class="{ 'cursor-pointer': handleClick }"></q-icon>
        <q-icon v-else :name="icon" @click.stop="handleClick" :class="{ 'cursor-pointer': handleClick }">
          <q-tooltip v-if="iconToolTip">{{ t(iconToolTip) }}</q-tooltip>
        </q-icon>
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ t(title) }}
          <slot name="header-extra">
          </slot>
        </q-item-label>
        <q-item-label caption>{{ t(caption) }}</q-item-label>
      </q-item-section>
    </template>
    <q-card class="q-ma-xs q-mt-sm" flat>
      <q-card-section class="q-pa-none">
        <slot name="content"></slot>
      </q-card-section>
    </q-card>
  </q-expansion-item>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  expanded: {
    type: Boolean,
    default: true
  },
  title: String,
  caption: String,
  icon: String,
  iconToolTip: String,
  onIconClick: {
    type: Function,
    default: null
  },
  loading: Boolean,
  error: Boolean
});


const isExpanded = ref(props.expanded === true);

function handleClick() {
  if (props.onIconClick && typeof props.onIconClick === 'function') {
    props.onIconClick();
  }
}

</script>