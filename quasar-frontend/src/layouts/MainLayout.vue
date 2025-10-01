<template>
  <q-layout view="lHh lpR lFf" class="theme-default-q-layout">
    <q-header height-hint="61.59" class="theme-default-q-header" bordered>
      <q-toolbar class="theme-default-q-toolbar">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar;" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="onToggleminiSidebarCurrentMode" aria-label="Toggle drawer"
          :icon="miniSidebarCurrentMode ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md"
          v-show="visibleSidebar" />
        <q-btn type="button" no-caps no-wrap align="left" outline :label="searchButtonLabel" icon="search"
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
    <UploadingDialog v-model="dialogs.uploading.visible" :transfers="dialogs.uploading.transfers" />
  </q-layout>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
import { useQuasar, LocalStorage, uid } from "quasar";
import { useI18n } from "vue-i18n";

import { useFormatDates } from "src/composables/formatDate"
import { useLocalStorage } from "src/composables/localStorage"
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

const { currentTimestamp } = useFormatDates();

const { alwaysOpenUploadDialog } = useLocalStorage();

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
    transfers: []
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
    dialogs.uploading.transfers.unshift(...msg.transfers.map((transfer) => {
      return ({
        id: uid(),
        filename: transfer.name,
        filesize: transfer.size,
        start: currentTimestamp(),
        end: null,
        uploading: true,
        done: false,
        error: false,
        errorHTTPCode: null,
        errorMessage: null,
        processed: false,
      })
    }) || []);
    dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0 && alwaysOpenUploadDialog.get();
  });

  bus.on("refreshUploadingDialog.fileUploaded", (msg) => {
    if (dialogs.uploading.transfers.length > 0) {
      msg.transfers?.forEach((completedTransfer) => {
        const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && completedTransfer.name == transfer.filename && completedTransfer.size == transfer.filesize);
        if (foundTransfer) {
          foundTransfer.isNew = false;
          foundTransfer.end = currentTimestamp();
          foundTransfer.uploading = false;
          foundTransfer.done = true;
          foundTransfer.processed = true;
        } else {
          // TODO:
          console.error("Transfer not found");
        }
      });
    } else {
      console.error("Missing previous transfers");
    }
    dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0 && alwaysOpenUploadDialog.get();
  });

  bus.on("refreshUploadingDialog.fileUploadRejected", (msg) => {
    msg.transfers?.forEach((transferUploadedWithError) => {
      const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && transferUploadedWithError.name == transfer.filename && transferUploadedWithError.size == transfer.filesize);
      if (foundTransfer) {
        foundTransfer.end = currentTimestamp();
        foundTransfer.uploading = false;
        foundTransfer.error = true;
        foundTransfer.errorHTTPCode = transferUploadedWithError.error?.status;
        foundTransfer.errorMessage = "Transfer rejected";
        foundTransfer.processed = true;
      } else {
        dialogs.uploading.transfers.unshift({
          id: uid(),
          filename: transferUploadedWithError.name,
          filesize: transferUploadedWithError.size,
          start: currentTimestamp(),
          end: currentTimestamp(),
          uploading: false,
          done: false,
          error: true,
          errorHTTPCode: transferUploadedWithError.error?.status,
          errorMessage: "Transfer rejected",
          processed: true,
        });
      }
    });
    dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0;
  });

  bus.on("refreshUploadingDialog.fileUploadFailed", (msg) => {
    msg.transfers?.forEach((transferUploadedWithError) => {
      const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && transferUploadedWithError.name == transfer.filename && transferUploadedWithError.size == transfer.filesize);
      if (foundTransfer) {
        foundTransfer.end = currentTimestamp();
        foundTransfer.uploading = false;
        foundTransfer.error = true;
        foundTransfer.errorHTTPCode = transferUploadedWithError.error?.status;
        foundTransfer.errorMessage = "Transfer failed";
        foundTransfer.processed = true;
      } else {
        dialogs.uploading.transfers.unshift({
          id: uid(),
          filename: transferUploadedWithError.name,
          filesize: transferUploadedWithError.size,
          start: currentTimestamp(),
          end: currentTimestamp(),
          uploading: false,
          done: false,
          error: true,
          errorHTTPCode: transferUploadedWithError.error?.status,
          errorMessage: "Transfer failed",
          processed: true,
        });
      }
    });
    dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0;
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthRequired");
  bus.off("showDocumentFilePreviewDialog");
  bus.off("showDocumentFilesPreviewDialog");
  bus.off("showDocumentNotesPreviewDialog");
  bus.off("showUploadingDialog");
  bus.off("refreshUploadingDialog.fileUploaded");
  bus.off("refreshUploadingDialog.fileUploadRejected");
  bus.off("refreshUploadingDialog.fileUploadFailed");
});

</script>