<template>
  <BaseDialog v-model="visible" @close="onClose" width="1920px" max-width="80vw">
    <template v-slot:body>
      <div class="row">
        <div class="col-12 col-lg-6 col-xl-6">
          <div id="qr-container" ref="qrContainerRef"
            :class="{ 'visible_qr': attachmentShare.enabled, 'hidden_qr': !attachmentShare.enabled }">
          </div>
          <p class="q-mx-auto" style="width: 90%;">
            <q-input dense outlined v-model="url" :disable="!attachmentShare.enabled || state.ajaxRunning" icon="delete"
              :hint="copiedToClipboardMessage">
              <template v-slot:append>
                <q-icon name="content_copy" class="cursor-pointer" @click="onCopyURLToClipboard" />
              </template>
            </q-input>
          </p>
        </div>
        <div class="col-12 col-lg-6 col-xl-6">
          <div class="row">
            <div class="col-4">
              <q-toggle size="xl" v-model="attachmentShare.enabled" :disable="state.ajaxRunning" icon="share"
                :label="t('Enabled')" />
            </div>
            <div class="col-4" v-show="attachmentShare.enabled">
              <q-toggle size="xl" v-model="hasAccessCountLimit" :disable="!attachmentShare.enabled || state.ajaxRunning"
                icon="lock_clock" :label="t('Has access count limit')" />
            </div>
            <div class="col-4" v-show="attachmentShare.enabled">
              <q-toggle size="xl" v-model="hasExpiration" :disable="!attachmentShare.enabled || state.ajaxRunning"
                icon="lock_clock" :label="t('Has expiration')" @update:model-value="onToggleHasExpiration" />
            </div>
          </div>
          <div v-show="attachmentShare.enabled">
            <p class="text-weight-bold q-my-none">
              {{
                t('This share has been accessed n times.', {
                  count: attachmentShare.accessCount
                })
              }}
            </p>
            <p v-if="attachmentShare.lastAccessTimestamp" class="q-my-none">
              {{ t('Most recent access') }}: {{ fullDateTimeHuman(attachmentShare.lastAccessTimestamp) }} ({{
                t(
                  timeAgo(
                    attachmentShare.lastAccessTimestamp).label,
                  {
                    count:
                      timeAgo(attachmentShare.lastAccessTimestamp).count
                  }
                )
              }})
            </p>
            <div v-show="hasAccessCountLimit" class="q-my-none">
              <q-separator class="q-my-md" />
              <div style="display: flex">
                <span class="text-weight-bold q-mb-none q-mr-sm q-mt-sm">{{ t('Set access count limit') }}: </span>
                <q-input filled dense type="number" v-model.number="attachmentShare.accessLimit" min="1"
                  :disable="!attachmentShare.enabled || state.ajaxRunning" style="max-width: 8em" />
              </div>
            </div>
            <div v-show="hasExpiration">
              <q-separator class="q-my-md" />
              <p class="text-weight-bold q-my-none">{{
                t('Expires on') }}: {{ expiresAtTimestampLabel }}
                <span v-if="hasExpiration && currentTimestamp() <= attachmentShare.expiresAtTimestamp">({{
                  t(
                    timeUntil(attachmentShare.expiresAtTimestamp).label, {
                    count:
                      timeUntil(attachmentShare.expiresAtTimestamp).count
                  }
                  )
                }})</span>
                <span v-else>{{ t('(expired)') }}</span>
              </p>
              <p class="q-mt-sm">
                <span class="text-weight-bold q-mb-none q-mr-sm">{{ t('Set expiration') }}: </span>
                <q-btn-toggle no-caps v-model="expiresOn" :disable="!attachmentShare.enabled || state.ajaxRunning"
                  toggle-color="primary" :options="expiresOnOptions" size="md" />
              </p>
            </div>
            <div v-if="attachmentShare.id">
              <q-separator class="q-my-sm" />
              <p><q-icon name="work" size="sm" /> <router-link
                  :to="{ name: 'document', params: { id: attachmentShare.document.id } }" class="text-color-primary">{{
                    attachmentShare.document.title
                  }}</router-link>
              </p>
              <p><q-icon name="save" size="sm" /> <a class="text-color-primary"
                  :href="getAttachmentURL(attachmentShare.attachment.id, true)">{{
                    attachmentShare.attachment.name
                  }}</a> ({{
                    format.humanStorageSize(attachmentShare.attachment.size) }})
              </p>
            </div>
          </div>
          <CustomErrorBanner class="q-my-md" v-if="state.ajaxErrors && state.ajaxErrorMessage"
            :text="state.ajaxErrorMessage" />
        </div>
      </div>
    </template>
    <template v-slot:actions-prepend>
      <q-btn size="md" no-caps icon="save" :label="t('Save')" color="primary" @click="onSave" />
      <q-btn size="md" no-caps icon="delete" :label="t('Remove')" color="red" @click="onDelete" />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
  import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
  import { useI18n } from "vue-i18n";
  import { format } from "quasar";
  import { bus } from "src/composables/bus";
  import { api } from "src/composables/api";
  import { getURL as getAttachmentURL } from "src/composables/attachment";
  import { timestamp, fullDateTimeHuman, timeAgo, timeUntil, currentTimestamp } from "src/composables/dateUtils";
  import { default as QRCodeStyling } from "qr-code-styling";
  import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
  import { type CreateUpdateGetAttachmentShareResponse as CreateUpdateGetAttachmentShareResponseInterface, } from "src/types/apiResponses";
  import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
  import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
  import { type AttachmentShare as AttachmentShareInterface } from "src/types/attachmentShare";
  const { t } = useI18n();

  const emit = defineEmits(['close']);

  interface SharePreviewDialogProps {
    create: boolean;
    attachmentId: string;
  };

  const props = defineProps<SharePreviewDialogProps>();

  const visible = ref<boolean>(true);

  const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

  type ExpireOnOption = null | 'oneMinute' | 'oneHour' | 'oneDay' | 'oneWeek' | 'oneMonth' | 'oneYear';

  interface ExpiresOnOptionInterface {
    label: string;
    value: ExpireOnOption;
  };

  const expiresOnOptions: ExpiresOnOptionInterface[] = [
    { label: t('1 minute'), value: 'oneMinute' },
    { label: t('1 hour'), value: 'oneHour' },
    { label: t('1 day'), value: 'oneDay' },
    { label: t('1 week'), value: 'oneWeek' },
    { label: t('1 month'), value: 'oneMonth' },
    { label: t('1 year'), value: 'oneYear' }
  ];

  const attachmentShare = reactive<AttachmentShareInterface>({
    id: "",
    createdAtTimestamp: 0,
    expiresAtTimestamp: 0,
    lastAccessTimestamp: null,
    accessLimit: 0,
    accessCount: 0,
    enabled: false,
    document: {
      id: "",
      title: "",
    },
    attachment: {
      id: "",
      name: "",
      size: 0,
    }
  });

  const copiedToClipboardMessage = ref<string | undefined>(undefined);

  const url = computed(() => attachmentShare.id ? `${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}/api3/shared/attachment/${props.attachmentId}?id=${encodeURI(attachmentShare.id)}` : null);

  const hasExpiration = ref<boolean>(false);
  const expiresAtTimestampLabel = computed(() => hasExpiration.value && attachmentShare.expiresAtTimestamp > 0 ? fullDateTimeHuman(attachmentShare.expiresAtTimestamp) : null);

  const hasAccessCountLimit = ref<boolean>(false);

  const expiresOn = ref<ExpireOnOption | null>(null);

  watch(() => expiresOn.value, (newValue: ExpireOnOption) => {
    if (newValue) {
      const date = new Date();
      switch (newValue) {
        case "oneMinute":
          date.setMinutes(date.getMinutes() + 1);
          break;
        case "oneHour":
          date.setHours(date.getHours() + 1);
          break;
        case "oneDay":
          date.setDate(date.getDate() + 1);
          break;
        case "oneWeek":
          date.setDate(date.getDate() + 7);
          break;
        case "oneMonth":
          date.setMonth(date.getMonth() + 1);
          break;
        case "oneYear":
          date.setFullYear(date.getFullYear() + 1);
          break;
      }
      attachmentShare.expiresAtTimestamp = timestamp(date);
    }
  });

  watch(() => hasAccessCountLimit.value, (newValue) => {
    if (newValue && attachmentShare.accessLimit == 0) {
      attachmentShare.accessLimit = 1;
    }
  });

  const onToggleHasExpiration = (hasExpiration: boolean) => {
    expiresOn.value = null;
    if (hasExpiration && attachmentShare.expiresAtTimestamp < 1) {
      const date = new Date();
      date.setHours(date.getHours() + 1);
      attachmentShare.expiresAtTimestamp = timestamp(date);
    }
  };

  const qrContainerRef = ref<HTMLElement | null>(null);

  const onRefreshQRCode = () => {
    const qrCode = new QRCodeStyling({
      type: "svg",
      shape: "square",
      width: 600,
      height: 600,
      margin: 0,
      data: url.value!,
      dotsOptions: {
        color: "#000000",
        type: "square",
      },
      backgroundOptions: {
        color: "#ffffff",
      },
    });

    // Renderizar el código QR en un contenedor
    if (qrContainerRef.value) {

      QRCodeStyling._clearContainer(qrContainerRef.value);
      qrCode.append(qrContainerRef.value); // Esto colocará el QR en un contenedor
    } else {
      console.error("container not found");
    }
  };

  const onClose = () => {
    emit('close');
  };

  const onCopyURLToClipboard = () => {
    copiedToClipboardMessage.value = undefined;
    if (url.value) {
      navigator.clipboard.writeText(url.value)
        .then(() => {
          copiedToClipboardMessage.value = t("Copied to clipboard!");
        })
        .catch((error) => {
          console.error("Error copying url to clipboard", error);
        });
    }
  };

  const onCreate = () => {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.attachmentShare.create(props.attachmentId).then((successResponse: CreateUpdateGetAttachmentShareResponseInterface) => {
      attachmentShare.id = successResponse.data.attachmentShare.id;
      attachmentShare.createdAtTimestamp = successResponse.data.attachmentShare.createdAtTimestamp;
      attachmentShare.expiresAtTimestamp = successResponse.data.attachmentShare.expiresAtTimestamp;
      attachmentShare.lastAccessTimestamp = successResponse.data.attachmentShare.lastAccessTimestamp;
      attachmentShare.accessLimit = successResponse.data.attachmentShare.accessLimit;
      attachmentShare.accessCount = successResponse.data.attachmentShare.accessCount;
      attachmentShare.enabled = successResponse.data.attachmentShare.enabled;
      attachmentShare.attachment = successResponse.data.attachmentShare.attachment;
      attachmentShare.document = successResponse.data.attachmentShare.document;
      hasExpiration.value = attachmentShare.expiresAtTimestamp > 0;
      hasAccessCountLimit.value = attachmentShare.accessLimit > 0;
      onRefreshQRCode();
      bus.emit("attachmentShareAdded", { attachmentShare: successResponse.data.attachmentShare });
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "AttachmentShareDialog.onCreate" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: Error adding attachment share";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
    }).finally(() => {
      state.ajaxRunning = false;
    });
  };

  const onGet = () => {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.attachmentShare.get(props.attachmentId).then((successResponse: CreateUpdateGetAttachmentShareResponseInterface) => {
      attachmentShare.id = successResponse.data.attachmentShare.id;
      attachmentShare.createdAtTimestamp = successResponse.data.attachmentShare.createdAtTimestamp;
      attachmentShare.expiresAtTimestamp = successResponse.data.attachmentShare.expiresAtTimestamp;
      attachmentShare.lastAccessTimestamp = successResponse.data.attachmentShare.lastAccessTimestamp;
      attachmentShare.accessLimit = successResponse.data.attachmentShare.accessLimit;
      attachmentShare.accessCount = successResponse.data.attachmentShare.accessCount;
      attachmentShare.enabled = successResponse.data.attachmentShare.enabled;
      attachmentShare.attachment = successResponse.data.attachmentShare.attachment;
      attachmentShare.document = successResponse.data.attachmentShare.document;
      hasExpiration.value = attachmentShare.expiresAtTimestamp > 0;
      hasAccessCountLimit.value = attachmentShare.accessLimit > 0;
      onRefreshQRCode();
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "AttachmentShareDialog.onGet" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: Error getting attachment share";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
    }).finally(() => {
      state.ajaxRunning = false;
    });
  };

  const onSave = () => {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.attachmentShare.update(props.attachmentId, attachmentShare.enabled, hasExpiration.value ? attachmentShare.expiresAtTimestamp : 0, hasAccessCountLimit.value ? attachmentShare.accessLimit : 0).then((successResponse: CreateUpdateGetAttachmentShareResponseInterface) => {
      attachmentShare.id = successResponse.data.attachmentShare.id;
      attachmentShare.createdAtTimestamp = successResponse.data.attachmentShare.createdAtTimestamp;
      attachmentShare.expiresAtTimestamp = successResponse.data.attachmentShare.expiresAtTimestamp;
      attachmentShare.lastAccessTimestamp = successResponse.data.attachmentShare.lastAccessTimestamp;
      attachmentShare.accessLimit = successResponse.data.attachmentShare.accessLimit;
      attachmentShare.accessCount = successResponse.data.attachmentShare.accessCount;
      attachmentShare.enabled = successResponse.data.attachmentShare.enabled;
      attachmentShare.attachment = successResponse.data.attachmentShare.attachment;
      attachmentShare.document = successResponse.data.attachmentShare.document;
      hasExpiration.value = attachmentShare.expiresAtTimestamp > 0;
      hasAccessCountLimit.value = attachmentShare.accessLimit > 0;
      bus.emit("attachmentShareChanged", { attachmentShare: successResponse.data.attachmentShare });
      onRefreshQRCode();
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "AttachmentShareDialog.onSave" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: Error saving attachment share";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
    }).finally(() => {
      state.ajaxRunning = false;
    });
  };

  const onDelete = () => {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.attachmentShare.remove(props.attachmentId).then(() => {
      bus.emit("attachmentShareDeleted", { attachmentId: props.attachmentId });
      emit('close');
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "AttachmentShareDialog.onDelete" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: Error removing attachment share";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
    }).finally(() => {
      state.ajaxRunning = false;
    });
  };

  onMounted(() => {
    bus.on("reAuthSucess", (msg) => {
      if (msg.to?.includes("AttachmentShareDialog.onCreate")) {
        onCreate();
      } else if (msg.to?.includes("AttachmentShareDialog.onGet")) {
        onGet();
      } else if (msg.to?.includes("AttachmentShareDialog.onSave")) {
        onSave();
      } else if (msg.to?.includes("AttachmentShareDialog.onDelete")) {
        onDelete();
      }
    });

    if (props.create) {
      onCreate();
    } else {
      onGet();
    }
  });

  onBeforeUnmount(() => {
    bus.off("reAuthSucess");
  });
</script>

<style>

  #qr-container {
    width: 100%;
    text-align: center;
  }

  #qr-container svg {
    margin: 1em auto;
    display: block;
  }

  .visible_qr svg {
    filter: blur(0);

  }

  .hidden_qr svg {
    filter: blur(1.5rem);
  }

</style>
