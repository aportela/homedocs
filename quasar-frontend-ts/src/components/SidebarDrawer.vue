<template>
  <q-drawer v-bind="attrs" show-if-above bordered :width="240" :mini="mini" @click.capture="onDrawerClick"
    class="fit theme-default-q-drawer">
    <q-list>
      <q-item class="cursor-pointer non-selectable no-pointer-events rounded-borders q-ma-sm theme-default-q-item">
        <q-item-section avatar>
          <q-avatar square size="24px">
            <img src="icons/favicon-128x128.png" />
          </q-avatar>
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-weight-bold text-uppercase">HomeDocs</q-item-label>
        </q-item-section>
      </q-item>
      <q-item v-for="link in menuItems" :key="link.text" v-ripple clickable :to="{ name: link.routeName }"
        class="rounded-borders q-ma-sm theme-default-q-item"
        :active="$route.name === link.routeName || (link.alternateRouteNames?.includes($route.name))"
        active-class="theme-default-q-item-active">
        <q-item-section avatar>
          <q-icon :name="link.icon" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t(link.text) }}</q-item-label>
        </q-item-section>
      </q-item>
      <q-item v-ripple clickable @click="logout" class="rounded-borders q-ma-sm theme-default-q-item">
        <q-item-section avatar>
          <q-icon name="logout" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ t("Sign out") }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-drawer>
</template>

<script setup lang="ts">

import { computed, useAttrs } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { api } from "src/composables/useAPI";
import { useSessionStore } from "src/stores/session";

interface SidebarDrawerProps {
  mini: boolean
};

const props = withDefaults(defineProps<SidebarDrawerProps>(), {
  mini: false
});

const attrs = useAttrs();

const { t } = useI18n();
const router = useRouter();

const session = useSessionStore();

const mini = computed(() => props.mini);

const menuItems = [
  { icon: 'home', text: "Dashboard", routeName: 'index' },
  { icon: 'account_circle', text: "My profile", routeName: 'profile' },
  { icon: 'note_add', text: "Add", routeName: 'newDocument' },
  { icon: 'find_in_page', text: "Advanced search", routeName: 'advancedSearch', alternateRouteNames: ['advancedSearchByTag', 'advancedSearchByFixedCreationDate', 'advancedSearchByFixedLastUpdate', 'advancedSearchByFixedUpdatedOn'] }
];

function onDrawerClick(e) {
  if (mini.value) {
    mini.value = false
    // notice we have registered an event with capture flag;
    // we need to stop further propagation as this click is
    // intended for switching drawer to "normal" mode only
    e.stopPropagation()
  }
}

function logout() {
  api.auth
    .logout()
    .then(() => {
      session.setJWT(null);
      router.push({
        name: "login",
      }).catch((e) => {
        console.error(e);
      });
    })
    .catch((errorResponse) => {
      console.error(errorResponse);
      session.setJWT(null);
      router.push({
        name: "login",
      }).catch((e) => {
        console.error(e);
      });
    });
}

</script>