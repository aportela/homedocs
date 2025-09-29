<template>
  <q-layout view="lHh lpR lFf" class="theme-default-q-layout">
    <q-header height-hint="61.59" class="theme-default-q-header" bordered>
      <q-toolbar class="theme-default-q-toolbar">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar;" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="onToggleminiSidebarCurrentMode" aria-label="Toggle drawer"
          :icon="miniSidebarCurrentMode ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md"
          v-show="visibleSidebar" />
        <q-btn type="button" no-caps no-wrap align="left" outline :label="searchButtonLabel" icon-right="search"
          class="full-width no-caps theme-default-q-btn" @click.prevent="dialogs.fastSearch.visible = true">
          <q-tooltip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
            }}</q-tooltip>
        </q-btn>
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
          <!--
          <NotificationsButton dense no-caps></NotificationsButton>
          -->
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar" :mini="miniSidebarCurrentMode"></SidebarDrawer>
    <SearchDialog :visible="dialogs.fastSearch.visible" @close="dialogs.fastSearch.visible = false"></SearchDialog>
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
    <!-- main common dialogs block, this dialogs will be launched from ANY page so we declare here and manage with bus events -->
    <ReAuthDialog v-if="dialogs.reauth.visible" @success="onSuccessReauth" @close="dialogs.reauth.visible = false" />
    <FilePreviewDialog v-if="dialogs.filePreview.visible" :document="dialogs.filePreview.document"
      :current-index="dialogs.filePreview.currentIndex" @close="dialogs.filePreview.visible = false" />
    <DocumentFilesPreviewDialog v-if="dialogs.documentFilesPreview.visible"
      :document-id="dialogs.documentFilesPreview.document.id"
      :document-title="dialogs.documentFilesPreview.document.title"
      @close="dialogs.documentFilesPreview.visible = false" />
    <DocumentNotesPreviewDialog v-if="dialogs.documentNotesPreview.visible"
      :document-id="dialogs.documentNotesPreview.document.id"
      :document-title="dialogs.documentNotesPreview.document.title"
      @close="dialogs.documentNotesPreview.visible = false" />
    <UploadingDialog v-if="dialogs.uploading.visible" :files="dialogs.uploading.files"
      :transfering="dialogs.uploading.transfering" @close="dialogs.uploading.visible = false" />
  </q-layout>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuasar, LocalStorage } from "quasar";
import { useI18n } from "vue-i18n";
import { useSessionStore } from "src/stores/session";
import { bus } from "src/boot/bus";

import { default as SidebarDrawer } from "src/components/SidebarDrawer.vue"
import { default as SearchDialog } from "src/components/Dialogs/SearchDialog.vue"
import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as ReAuthDialog } from "src/components/Dialogs/ReAuthDialog.vue"

import { default as FilePreviewDialog } from "src/components/Dialogs/FilePreviewDialog.vue"
import { default as DocumentFilesPreviewDialog } from "src/components/Dialogs/DocumentFilesPreviewDialog.vue"
import { default as DocumentNotesPreviewDialog } from "src/components/Dialogs/DocumentNotesPreviewDialog.vue";
import { default as UploadingDialog } from "src/components/Dialogs/UploadingDialog.vue";

const $q = useQuasar();

const { t } = useI18n();

const session = useSessionStore();

if (!session.isLoaded) {
  session.load();
}

const dialogs = reactive({
  reauth: {
    visible: false
  },
  filePreview: {
    visible: false,
    document: {
      id: null,
      title: null,
      attachments: [],
    },
    currentIndex: 0
  },
  documentFilesPreview: {
    visible: false,
    document: {
      id: null,
      title: null,
    }
  },
  documentNotesPreview: {
    visible: false,
    document: {
      id: null,
      title: null,
    }
  },
  fastSearch: {
    visible: false
  },
  uploading: {
    visible: false,
    files: []
  }
});

const reAuthEmitters = reactive([]);

const onSuccessReauth = () => {
  dialogs.reauth.visible = false;
  bus.emit("reAuthSucess", ({ to: reAuthEmitters }))
  reAuthEmitters.length = 0;
};

const lockminiSidebarCurrentModeMode = ref(false);

const visibleSidebar = ref($q.screen.gt.sm);

// toggle this for using current mini sidebar saved mode
const saveMiniSidebarMode = true;

const miniSidebarCurrentModeSavedMode = saveMiniSidebarMode ? LocalStorage.getItem("miniSidebarCurrentMode") : null;

if (saveMiniSidebarMode && miniSidebarCurrentModeSavedMode != null) {
  lockminiSidebarCurrentModeMode.value = true;
}

const miniSidebarCurrentMode = ref(miniSidebarCurrentModeSavedMode != null ? miniSidebarCurrentModeSavedMode == true : $q.screen.md);

const currentScreenSize = computed(() => $q.screen.name);

watch(currentScreenSize, (newValue) => {
  if (!lockminiSidebarCurrentModeMode.value) {
    miniSidebarCurrentMode.value = $q.screen.lt.lg;
  }
});

const searchButtonLabel = computed(() => $q.screen.gt.xs ? t('Search on HomeDocs...') : '');

const onToggleminiSidebarCurrentMode = (value) => {
  miniSidebarCurrentMode.value = !miniSidebarCurrentMode.value;
  lockminiSidebarCurrentModeMode.value = true;
  if (saveMiniSidebarMode) {
    LocalStorage.set("miniSidebarCurrentMode", miniSidebarCurrentMode.value);
  }
}

onMounted(() => {
  bus.on("reAuthRequired", (msg) => {
    if (msg.emitter) {
      reAuthEmitters.push(msg.emitter);
    }
    dialogs.reauth.visible = true;
  });

  bus.on("showDocumentFilePreviewDialog", (msg) => {
    dialogs.filePreview.document.id = msg?.document?.id;
    dialogs.filePreview.document.title = msg?.document?.title;
    dialogs.filePreview.document.attachments = msg?.document?.attachments || [];
    dialogs.filePreview.currentIndex = msg?.currentIndex;
    dialogs.filePreview.visible = true;
  });

  bus.on("showDocumentFilesPreviewDialog", (msg) => {
    dialogs.documentFilesPreview.document.id = msg?.document?.id;
    dialogs.documentFilesPreview.document.title = msg?.document?.title;
    dialogs.documentFilesPreview.visible = true;
  });

  bus.on("showDocumentNotesPreviewDialog", (msg) => {
    dialogs.documentNotesPreview.document.id = msg?.document?.id;
    dialogs.documentNotesPreview.document.title = msg?.document?.title;
    dialogs.documentNotesPreview.visible = true;
  });

  bus.on("showUploadingDialog", (msg) => {
    dialogs.uploading.files = msg.files.map((file) => {
      return ({
        name: file.name,
        size: file.size,
        start: file.start,
        end: null,
        done: false,
        error: false
      })
    }) || [];
    dialogs.uploading.transfering = true;
    dialogs.uploading.visible = true;
  });

  bus.on("refreshUploadingDialog", (msg) => {
    if (dialogs.uploading.files.length > 0) {
      msg.files?.forEach((uploadedFile) => {
        const existentFile = dialogs.uploading.files?.find((file) => uploadedFile.name == file.name && uploadedFile.size == file.size);
        if (existentFile) {
          existentFile.done = true;
          existentFile.end = uploadedFile.end;
        } else {
          dialogs.uploading.files.push({
            name: uploadedFile.name,
            size: uploadedFile.size,
            start: uploadedFile.start, // TODO: exists ?
            end: uploadedFile.end,
            done: true,
            error: false
          });
        }
      });
    } else {
      dialogs.uploading.files = msg.files?.map((file) => {
        return ({
          name: file.name,
          size: file.size,
          start: file.start, // TODO: exists ?
          end: file.end,
          done: true,
          error: false
        });
      })
    }
    const totalDone = dialogs.uploading.files?.filter((file) => file.done == true).length;
    const totalError = dialogs.uploading.files?.filter((file) => file.error == true).length;
    dialogs.uploading.transfering = totalDone + totalError != dialogs.uploading.files?.length;
    dialogs.uploading.visible = true;
  });

  bus.on("refreshUploadingDialogErrors", (msg) => {
    if (dialogs.uploading.files.length > 0) {
      msg.files?.forEach((errorFile) => {
        const existentFile = dialogs.uploading.files?.find((file) => errorFile.name == file.name && errorFile.size == file.size);
        if (existentFile) {
          existentFile.done = true;
          existentFile.end = errorFile.end;
        } else {
          dialogs.uploading.files.push({
            name: errorFile.name,
            size: errorFile.size,
            start: errorFile.start,
            end: errorFile.end,
            done: false,
            error: true
          });
        }
      });
    } else {
      dialogs.uploading.files = msg.files?.map((file) => {
        return ({
          name: file.name,
          size: file.size,
          start: file.start,
          end: file.end,
          done: false,
          error: true
        });
      })
    }
    const totalDone = dialogs.uploading.files?.filter((file) => file.done == true).length;
    const totalError = dialogs.uploading.files?.filter((file) => file.error == true).length;
    dialogs.uploading.transfering = totalDone + totalError != dialogs.uploading.files?.length;
    dialogs.uploading.visible = true;
  });

  bus.on("hideUploadingDialog", (msg) => {
    dialogs.uploading.transfering = false;
    dialogs.uploading.visible = false;
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthRequired");
  bus.off("showDocumentFilePreviewDialog");
  bus.off("showDocumentFilesPreviewDialog");
  bus.off("showDocumentNotesPreviewDialog");
  bus.off("showUploadingDialog");
  bus.off("hideUploadingDialog");
});

</script>