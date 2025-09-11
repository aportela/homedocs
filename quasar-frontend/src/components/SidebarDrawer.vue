<template>
  <q-drawer v-bind="attrs" show-if-above bordered :width="240" :mini="mini" @click.capture="drawerClick">
    <q-scroll-area class="fit">
      <q-list padding>
        <q-item class="cursor-pointer non-selectable rounded-borders q-ma-sm sidebar_item">
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
          class="rounded-borders q-ma-sm sidebar_item" :active="$route.name === link.routeName"
          active-class="bg-grey-4 text-grey-8">
          <q-item-section avatar>
            <q-icon color="grey" :name="link.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ t(link.text) }}</q-item-label>
          </q-item-section>
        </q-item>
        <q-item v-ripple clickable @click="signOut" class="rounded-borders q-ma-sm sidebar_item">
          <q-item-section avatar>
            <q-icon color="grey" name="logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ t("Sign out") }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-scroll-area>
    <div class="q-mini-drawer-hide absolute" style="top: 22px; right: -17px">
      <q-btn dense round unelevated color="accent" icon="chevron_left" @click="mini = true"
        style="background-color: rgb(105, 108, 255); color: white; border: 6px solid rgb(242, 242, 247);" />
    </div>
  </q-drawer>
</template>

<script setup>
import { ref, useAttrs } from "vue";
import { api } from "boot/axios";
import { useSessionStore } from "stores/session";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";

const attrs = useAttrs();

const { t } = useI18n();
const $q = useQuasar();
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
const router = useRouter();

const mini = ref(false);

const menuItems = [
  { icon: 'storage', text: "Dashboard", routeName: 'index' },
  { icon: 'note_add', text: "Add", routeName: 'newDocument' },
  { icon: 'find_in_page', text: "Advanced search", routeName: 'advancedSearch' }
];

function drawerClick(e) {
  if (mini.value) {
    mini.value = false
    // notice we have registered an event with capture flag;
    // we need to stop further propagation as this click is
    // intended for switching drawer to "normal" mode only
    e.stopPropagation()
  }
}

function signOut() {
  api.user
    .signOut()
    .then((success) => {
      session.signOut();
      router.push({
        name: "signIn",
      });
    })
    .catch((error) => {
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

</script>

<style scoped>
.sidebar_item {
  border: 1px solid #eee;
}
</style>