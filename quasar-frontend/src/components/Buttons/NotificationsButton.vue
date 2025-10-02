<template>
  <q-btn v-bind="attrs" :icon="hasNotifications ? 'notifications_active' : 'notifications'"
    :disabled="!hasNotifications" :class="{ 'shake-animation': hasNotifications }">
    <DesktopToolTip v-if="hasNotifications">{{ tooltip }}</DesktopToolTip>
    <q-menu>
      <div class="no-wrap q-pa-md">
        <div class="text-h6 q-mb-md">Recent notifications</div>
        <q-separator />
        <p v-for="notification in notificationItems" :key="notification.timestamp"
          class="text-subtitle2 q-mt-md q-mb-xs">
          <q-icon name="check" color="green" size="sm" v-if="notification.success"></q-icon>
          <q-icon name="error" color="red" size="sm" v-else></q-icon>
          {{ notification.date }} - {{ notification.body }}
        </p>
        <q-separator />
        <p class="text-center q-mt-sm">
          <q-btn color="primary" label="Close" size="sm" v-close-popup />
          <q-btn color="primary" label="Clear" size="sm" class="q-ml-sm" @click="notificationItems = []"
            v-close-popup />
        </p>
      </div>
    </q-menu>
  </q-btn>
</template>

<script setup>

import { useAttrs, ref, onMounted, nextTick, computed } from "vue";

import { useI18n } from "vue-i18n";

import { useNotifications } from "src/composables/notifications"

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const attrs = useAttrs();

const { t } = useI18n();

const notifications = useNotifications();

const tooltip = computed(() => t("Show recent notifications"));

const notificationItems = ref([]);
const hasNotifications = computed(() => notificationItems.value.length > 0);

notifications.on("add", message => {
  notificationItems.value.unshift(
    {
      id: message.id,
      timestamp: message.timestamp,
      date: message.date,
      body: message.body,
      caption: message.caption,
      success: message.type == "positive"
    }
  );
});

onMounted(() => {
  nextTick(() => {
    notifications.emit("add", { body: "Session logged", caption: null, type: "positive" });
    notifications.emit("add", { body: "Document created", caption: null, type: "positive" });
    notifications.emit("add", { body: "Document update failed", caption: null, type: "negative" });
  });

});

</script>

<style scoped>
@keyframes shakeKeyFrames {
  0% {
    transform: rotate(0deg);
  }

  25% {
    transform: rotate(-10deg);
  }

  50% {
    transform: rotate(10deg);
  }

  75% {
    transform: rotate(-10deg);
  }

  100% {
    transform: rotate(0deg);
  }
}

.shake-animation {
  animation: shakeKeyFrames 0.5s ease-in-out;
  animation-iteration-count: 1;
}
</style>
