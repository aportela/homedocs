<template>
  <q-card>
    <q-item class="theme-default-q-card-section-header">
      <q-item-section avatar>
        <q-icon :name="icon"></q-icon>
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ t(title) }}</q-item-label>
        <q-item-label caption>{{ t(caption) }}</q-item-label>
      </q-item-section>
    </q-item>
    <q-separator />
    <q-card-section>
      <slot name="content"></slot>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  title: String,
  caption: String,
  icon: String
});

</script>