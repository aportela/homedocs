<template>
  <q-page>
    <div>
      <q-markup-table v-if="hasShares">
        <thead>
          <tr>
            <th class="text-left">{{ t('Created on') }}</th>
            <th class="text-left">Last activity on</th>
            <th class="text-left">Expires on</th>
            <th class="text-right">Total clicks</th>
            <th class="text-center">{{ t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="share in shares">
            <td class="text-left">{{ share.createdAt.dateTime }} ({{ share.createdAt.timeAgo }})</td>
            <td class="text-left">{{ share.lastActivity.dateTime }} ({{ share.lastActivity.timeAgo }})</td>
            <td class="text-left">{{ t('never') }}</td>
            <td class="text-right">{{ share.totalClicks }}</td>
            <td class="text-center">
              <q-btn-group flat>
                <q-btn outline no-caps size="md" icon="settings" label="settings" />
                <q-btn outline no-caps size="md" icon="delete" label="delete" />
              </q-btn-group>
            </td>
          </tr>
        </tbody>
      </q-markup-table>

      <CustomBanner v-else warning text="You haven't created any share yet" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { default as CustomBanner } from 'src/components/Banners/CustomBanner.vue';
import { ref, computed } from 'vue';
import { DateTimeClass } from "src/types/dateTime";
import { currentTimestamp } from "src/composables/dateUtils";
const { t } = useI18n();

const shares = [
  {
    createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    totalClicks: Math.floor(Math.random() * 101),
  },
  {
    createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    totalClicks: Math.floor(Math.random() * 101),
  },
  {
    createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    totalClicks: Math.floor(Math.random() * 101),
  },
  {
    createdAt: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    lastActivity: new DateTimeClass(t, currentTimestamp() - Math.random() * 1000000000),
    totalClicks: Math.floor(Math.random() * 101),
  }
];

const hasShares = computed(() => shares.length > 0);

</script>
