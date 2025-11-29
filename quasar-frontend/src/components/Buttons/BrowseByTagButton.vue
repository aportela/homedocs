<template>
  <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag } }" class="text-decoration-none"
    aria-label="Browse by tag">
    <q-chip square clickable class="theme-default-q-chip shadow-1" :class="qChipClass">
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="icon">
        <q-icon :name="icon"></q-icon>
      </q-avatar>
      <q-avatar class="theme-default-q-avatar text-white bg-blue-6" v-if="caption">{{ caption }}</q-avatar>
      <div class="full-width text-center ellipsis">
        {{ tag }}
      </div>
      <DesktopToolTip>{{ t("Browse by tag: ", { tag: tag }) }}</DesktopToolTip>
    </q-chip>
  </router-link>
</template>

<script setup lang="ts">

import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

interface BrowseByTagButtonProps {
  tag: string;
  icon?: string;
  caption?: string;
  toolTip?: boolean;
  qChipClass?: string;
};

withDefaults(defineProps<BrowseByTagButtonProps>(), {
  toolTip: true
});

</script>