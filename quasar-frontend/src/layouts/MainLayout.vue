<template>
  <q-layout view="lHh lpR lFf" class="theme-default-q-layout">
    <q-header height-hint="61.59" class="theme-default-q-header" bordered>
      <q-toolbar class="theme-default-q-toolbar">
        <q-btn flat dense round @click="visibleSidebar = !visibleSidebar;" aria-label="Toggle drawer" icon="menu"
          v-show="!visibleSidebar" class="q-mr-md" />
        <q-btn flat dense round @click="onToggleminiSidebarCurrentMode" aria-label="Toggle drawer"
          :icon="miniSidebarCurrentMode ? 'arrow_forward_ios' : 'arrow_back_ios_new'" class="q-mr-md"
          v-show="visibleSidebar">
          <DesktopToolTip>{{ t(miniSidebarCurrentMode ? "Expand sidebar" : "Collapse sidebar") }}
          </DesktopToolTip>
        </q-btn>
        <q-btn type="button" no-caps no-wrap align="left" outline :label="searchButtonLabel" icon="search"
          class="full-width no-caps theme-default-q-btn" @click.prevent="dialogs.fastSearch.visible = true">
          <DesktopToolTip anchor="bottom middle" self="top middle">{{ t("Click to open fast search")
            }}</DesktopToolTip>
        </q-btn>
        <!--
        <FastSearchSelector dense class="full-width"></FastSearchSelector>
        -->
        <q-btn-group flat class="q-ml-md">
          <DarkModeButton dense />
          <SwitchLanguageButton :short-labels="true" style="min-width: 9em" />
          <GitHubButton dense :href="GITHUB_PROJECT_URL" />
        </q-btn-group>
      </q-toolbar>
    </q-header>
    <SidebarDrawer v-model="visibleSidebar" :mini="miniSidebarCurrentMode" />
    <SearchDialog v-model="dialogs.fastSearch.visible" />
    <q-page-container>
      <router-view class="q-pa-sm" />
    </q-page-container>
    <!-- main common dialogs block, this dialogs will be launched from ANY page so we declare here and manage with bus events -->
    <ReAuthDialog v-if="dialogs.reauth.visible" @success="onSuccessReauth" @close="dialogs.reauth.visible = false" />
    <FilePreviewDialog v-if="dialogs.filePreview.visible" :document="dialogs.filePreview.document"
      :current-index="dialogs.filePreview.currentIndex" @close="dialogs.filePreview.visible = false" />
    <DocumentFilesPreviewDialog
      v-if="dialogs.documentFilesPreview.visible && dialogs.documentFilesPreview.document.id && dialogs.documentFilesPreview.document.title"
      :document-id="dialogs.documentFilesPreview.document.id"
      :document-title="dialogs.documentFilesPreview.document.title"
      @close="dialogs.documentFilesPreview.visible = false" />
    <DocumentNotesPreviewDialog
      v-if="dialogs.documentNotesPreview.visible && dialogs.documentNotesPreview.document.id && dialogs.documentNotesPreview.document.title"
      :document-id="dialogs.documentNotesPreview.document.id"
      :document-title="dialogs.documentNotesPreview.document.title"
      @close="dialogs.documentNotesPreview.visible = false" />
    <UploadingDialog v-model="dialogs.uploading.visible" :transfers="dialogs.uploading.transfers"
      @clear-processed-transfers="onClearProcessedTransfers" />
    <AttachmentShareDialog v-if="dialogs.attachmentShare.visible" :attachment-id="dialogs.attachmentShare.attachmentId"
      :create="dialogs.attachmentShare.create" @close="dialogs.attachmentShare.visible = false" />
  </q-layout>

</template>

<script setup lang="ts">
  import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
  import { useQuasar, LocalStorage } from "quasar";
  import { useI18n } from "vue-i18n";

  import { useSessionStore } from "src/stores/session";
  import { currentTimestamp } from "src/composables/dateUtils";
  import { bus } from "src/composables/bus";
  import type { Document } from "src/types/document";

  import { default as SidebarDrawer } from "src/components/SidebarDrawer.vue"
  import { default as SearchDialog } from "src/components/Dialogs/SearchDialog.vue"
  import { default as DarkModeButton } from "src/components/Buttons/DarkModeButton.vue"
  import { default as SwitchLanguageButton } from "src/components/Buttons/SwitchLanguageButton.vue"
  import { default as GitHubButton } from "src/components/Buttons/GitHubButton.vue"
  import { GITHUB_PROJECT_URL } from "src/constants"
  import { default as ReAuthDialog } from "src/components/Dialogs/ReAuthDialog.vue"

  import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
  import { default as FilePreviewDialog } from "src/components/Dialogs/FilePreviewDialog.vue"
  import { default as DocumentFilesPreviewDialog } from "src/components/Dialogs/DocumentFilesPreviewDialog.vue"
  import { default as DocumentNotesPreviewDialog } from "src/components/Dialogs/DocumentNotesPreviewDialog.vue";
  import { default as UploadingDialog } from "src/components/Dialogs/UploadingDialog.vue";

  import { type UploadTransfer as UploadTransferInterface } from "src/types/uploadTransfer";
  import { type Document as DocumentInterface, DocumentClass } from "src/types/document";
  import AttachmentShareDialog from "src/components/Dialogs/AttachmentShareDialog.vue";

  const $q = useQuasar();

  const { t } = useI18n();

  interface DialogsInterface {
    reauth: {
      visible: boolean;
    },
    filePreview: {
      visible: boolean;
      document: DocumentInterface;
      currentIndex: number;
    },
    documentFilesPreview: {
      visible: boolean;
      document: {
        id: string | null;
        title: string | null;
      }
    },
    documentNotesPreview: {
      visible: boolean;
      document: {
        id: string | null;
        title: string | null;
      }
    },
    fastSearch: {
      visible: boolean;
    },
    uploading: {
      visible: boolean;
      transfers: UploadTransferInterface[]
    },
    attachmentShare: {
      visible: boolean;
      attachmentId: string;
      create: false,
    }
  };

  const dialogs = reactive<DialogsInterface>({
    reauth: {
      visible: false
    },
    filePreview: {
      visible: false,
      document: new DocumentClass(),
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
    },
    attachmentShare: {
      visible: false,
      attachmentId: "",
      create: false,
    }
  });

  const reAuthEmitters = reactive<Array<string>>([]);

  const sessionStore = useSessionStore();

  const onSuccessReauth = () => {
    dialogs.reauth.visible = false;
    bus.emit("reAuthSucess", ({ to: reAuthEmitters }))
    reAuthEmitters.length = 0;
  };

  const lockminiSidebarCurrentModeMode = ref<boolean>(false);

  const visibleSidebar = ref($q.screen.gt.sm);

  // toggle this for using current mini sidebar saved mode
  const saveMiniSidebarMode = true;

  const miniSidebarCurrentModeSavedMode = saveMiniSidebarMode ? LocalStorage.getItem("miniSidebarCurrentMode") : null;

  if (saveMiniSidebarMode && miniSidebarCurrentModeSavedMode != null) {
    lockminiSidebarCurrentModeMode.value = true;
  }

  const miniSidebarCurrentMode = ref(miniSidebarCurrentModeSavedMode != null ? miniSidebarCurrentModeSavedMode == true : $q.screen.md);

  const currentScreenSize = computed(() => $q.screen.name);

  watch(currentScreenSize, () => {
    if (!lockminiSidebarCurrentModeMode.value) {
      miniSidebarCurrentMode.value = $q.screen.lt.lg;
    }
  });

  const searchButtonLabel = computed(() => $q.screen.gt.xs ? t('Search on HomeDocs...') : '');

  const onToggleminiSidebarCurrentMode = () => {
    miniSidebarCurrentMode.value = !miniSidebarCurrentMode.value;
    lockminiSidebarCurrentModeMode.value = true;
    if (saveMiniSidebarMode) {
      LocalStorage.set("miniSidebarCurrentMode", miniSidebarCurrentMode.value);
    }
  }

  const onClearProcessedTransfers = () => {
    dialogs.uploading.transfers = dialogs.uploading.transfers.filter((transfer) => !transfer.processed);
  };

  interface BusMsg {
    emitter: string;
    document?: Document | null;
    currentIndex?: number | null;
  };

  onMounted(() => {
    bus.on("reAuthRequired", (msg: BusMsg) => {
      if (msg.emitter) {
        reAuthEmitters.push(msg.emitter);
      }
      sessionStore.refreshAccessToken().then((success: boolean) => {
        if (success) {
          bus.emit("reAuthSucess", ({ to: reAuthEmitters }))
          reAuthEmitters.length = 0;
        } else {
          dialogs.reauth.visible = true;
        }
      }).catch((e: Error) => {
        console.error("An unhandled exception occurred during access token refresh", e);
        dialogs.reauth.visible = true;
      }).finally(() => {
      });
    });

    bus.on("showDocumentFilePreviewDialog", (msg: BusMsg) => {
      dialogs.filePreview.document.id = msg?.document?.id || null;
      dialogs.filePreview.document.title = msg?.document?.title || "";
      dialogs.filePreview.document.attachments = msg?.document?.attachments || [];
      dialogs.filePreview.currentIndex = msg?.currentIndex || 0;
      dialogs.filePreview.visible = true;
    });

    bus.on("showDocumentFilesPreviewDialog", (msg: BusMsg) => {
      dialogs.documentFilesPreview.document.id = msg?.document?.id || null;
      dialogs.documentFilesPreview.document.title = msg?.document?.title || null;
      dialogs.documentFilesPreview.visible = true;
    });

    bus.on("showDocumentNotesPreviewDialog", (msg: BusMsg) => {
      dialogs.documentNotesPreview.document.id = msg?.document?.id || null;
      dialogs.documentNotesPreview.document.title = msg?.document?.title || null;
      dialogs.documentNotesPreview.visible = true;
    });

    bus.on("showUploadingDialog", (msg) => {
      dialogs.uploading.transfers.unshift(...msg.transfers);
      dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0 && sessionStore.openUploadDialog;
    });

    bus.on("refreshUploadingDialog.fileUploaded", (msg) => {
      if (dialogs.uploading.transfers.length > 0) {
        msg.transfers?.forEach((completedTransfer: UploadTransferInterface) => {
          const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && completedTransfer.filename == transfer.filename && completedTransfer.filesize == transfer.filesize);
          if (foundTransfer) {
            foundTransfer.end = currentTimestamp();
            foundTransfer.uploading = false;
            foundTransfer.done = true;
            foundTransfer.processed = true;
          } else {
            console.error("Transfer not found", completedTransfer);
          }
        });
      } else {
        console.error("Missing previous transfers");
      }
      dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0 && sessionStore.openUploadDialog;
    });

    bus.on("refreshUploadingDialog.fileUploadRejected", (msg) => {
      msg.transfers?.forEach((transferUploadedWithError: UploadTransferInterface) => {
        const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && transferUploadedWithError.filename == transfer.filename && transferUploadedWithError.filesize == transfer.filesize);
        if (foundTransfer) {
          foundTransfer.end = currentTimestamp();
          foundTransfer.uploading = false;
          foundTransfer.error = true;
          foundTransfer.errorHTTPCode = transferUploadedWithError.errorHTTPCode;
          foundTransfer.errorMessage = "Transfer rejected";
          foundTransfer.processed = true;
        } else {
          dialogs.uploading.transfers.unshift(transferUploadedWithError);
        }
      });
      dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0;
    });

    bus.on("refreshUploadingDialog.fileUploadFailed", (msg) => {
      msg.transfers?.forEach((transferUploadedWithError: UploadTransferInterface) => {
        const foundTransfer = dialogs.uploading.transfers?.find((transfer) => !transfer.processed && transferUploadedWithError.filename == transfer.filename && transferUploadedWithError.filesize == transfer.filesize);
        if (foundTransfer) {
          foundTransfer.end = currentTimestamp();
          foundTransfer.uploading = false;
          foundTransfer.error = true;
          foundTransfer.errorHTTPCode = transferUploadedWithError.errorHTTPCode;
          foundTransfer.errorMessage = "Transfer failed";
          foundTransfer.processed = true;
        } else {
          dialogs.uploading.transfers.unshift(transferUploadedWithError);
        }
      });
      dialogs.uploading.visible = dialogs.uploading.transfers?.length > 0;
    });

    bus.on("showAttachmentShareDialog", (msg) => {
      dialogs.attachmentShare.visible = true;
      dialogs.attachmentShare.attachmentId = msg.attachmentId;
      dialogs.attachmentShare.create = msg.create;
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
    bus.off("showAttachmentShareDialog");
  });

</script>