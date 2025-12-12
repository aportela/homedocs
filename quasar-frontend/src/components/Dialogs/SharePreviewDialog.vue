<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div>{{ t("Attachment share") }}</div>
    </template>
    <template v-slot:body>
      <div class="row q-py-md">
        <div class="col-6 q-col-gutter-sm">
          <div id="qr-container" ref="qrContainerRef" :class="{ 'visible_qr': enabled, 'hidden_qr': !enabled }">
          </div>
        </div>
        <div class="col-6 q-col-gutter-sm">
          <p>
            <q-input dense outlined v-model="url" :disable="!enabled || state.ajaxRunning" icon="delete"
              :hint="copiedToClipboardMessage">
              <template v-slot:append>
                <q-icon name="content_copy" class="cursor-pointer" @click="onCopyURLToClipboard" />
              </template>
            </q-input>
          </p>
          <p><q-toggle size="xl" v-model="enabled" :disable="state.ajaxRunning" color="green" icon="share"
              :label="t('Enabled')" /></p>
          <p><q-toggle size="xl" v-model="hasExpiration" :disable="!enabled || state.ajaxRunning" color="green"
              icon="lock_clock" :label="t('Has expiration')" @update:model-value="onToggleHasExpiration" /> <span
              class="text-weight-bold" v-if="hasExpiration">({{ t('Expires at') }} {{ expiresAtTimestampLabel }})</span>
          </p>
          <p v-if="hasExpiration">
            <q-btn-toggle no-caps v-model="expiresOn" :disable="!enabled || state.ajaxRunning" toggle-color="primary"
              :options="expiresOnOptions" />
          </p>
          <p class="row">
            <q-toggle class="col-6" size="xl" v-model="hasAccessCountLimit" :disable="!enabled || state.ajaxRunning"
              color="green" icon="lock_clock" :label="t('Has access count limit')" />
            <q-input class="col-6" type="number" v-model.number="accessLimit" v-if="hasAccessCountLimit" min="1"
              :label="t('Limit')" :disable="!enabled || state.ajaxRunning" />
          </p>
          <q-banner rounded :class="accessClass"><q-icon name="link" size="sm" />
            {{ t('This share has been accessed n times.', { count: accessCount }) }}
          </q-banner>
          <CustomErrorBanner class="q-my-md" v-if="state.ajaxErrors && state.ajaxErrorMessage"
            :text="state.ajaxErrorMessage" />
          <p>
            <q-btn-group flat spread>
              <q-btn outline no-caps icon="save" :label="t('Save')" class="full-width" @click="onSave" />
              <q-btn outline no-caps icon="delete" :label="t('Remove')" class="full-width" @click="onDelete" />
            </q-btn-group>
          </p>
        </div>
      </div>
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
  import { ref, reactive, watch, computed, onMounted, onBeforeUnmount } from "vue";
  import { useI18n } from "vue-i18n";
  import { bus } from "src/composables/bus";
  import { api } from "src/composables/api";
  import { timestamp, fullDateTimeHuman } from "src/composables/dateUtils";
  import { default as QRCodeStyling } from "qr-code-styling";
  import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
  import { type attachmentShareResponse as attachmentShareResponseInterface, } from "src/types/apiResponses";
  import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
  import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
  const { t } = useI18n();

  const emit = defineEmits(['close']);

  interface SharePreviewDialogProps {
    create: boolean;
    attachmentId: string;
  };

  const props = defineProps<SharePreviewDialogProps>();

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

  const visible = ref<boolean>(true);

  const copiedToClipboardMessage = ref<string | undefined>(undefined);

  const url = computed(() => id.value ? `${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}/api3/shared/attachment/${props.attachmentId}?id=${encodeURI(id.value)}` : null);

  const id = ref<string | null>(null);
  const enabled = ref<boolean>(true);

  const hasExpiration = ref<boolean>(false);


  const expiresAtTimestamp = ref<number>(0);
  const expiresOn = ref<ExpireOnOption>(null);
  const hasAccessCountLimit = computed<boolean>({
    get() {
      return (accessLimit.value > 0);
    },
    set(value: boolean) {
      if (value) {
        accessLimit.value = 1;
      } else {
        accessLimit.value = 0;
      }
    }
  });
  const accessLimit = ref<number>(0);
  const accessCount = ref<number>(0);

  const expiresAtTimestampLabel = computed(() => fullDateTimeHuman(expiresAtTimestamp.value));
  const accessClass = computed(() => {
    if (hasAccessCountLimit.value) {
      if (accessCount.value < accessLimit.value) {
        if (accessCount.value > 0) {
          return ('bg-green');
        } else {
          return ('bg-orange');
        }
      } else {
        return ('bg-red');
      }
    } else {
      if (accessCount.value > 0) {
        return ('bg-green');
      } else {
        return ('bg-orange');
      }
    }
  });


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
      expiresAtTimestamp.value = timestamp(date);
    } else {
      expiresAtTimestamp.value = 0;
    }
  });

  const onToggleHasExpiration = (hasExpiration: boolean) => {
    if (!hasExpiration) {
      expiresAtTimestamp.value = 0;
    } else {
      expiresOn.value = "oneDay";
    }
  };

  const qrContainerRef = ref<HTMLElement | null>(null);

  const onRefreshQRCode = () => {
    const qrCode = new QRCodeStyling({
      width: 500,
      height: 500,
      margin: 8,
      type: "svg",
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
    enabled.value = false;
    accessLimit.value = 0;
    accessCount.value = 0;
    api.sharedAttachment.create(props.attachmentId).then((successResponse: attachmentShareResponseInterface) => {
      id.value = successResponse.data.share.id;
      enabled.value = successResponse.data.share.enabled;
      expiresAtTimestamp.value = successResponse.data.share.expiresAtTimestamp;
      hasExpiration.value = successResponse.data.share.expiresAtTimestamp > 0;
      accessLimit.value = successResponse.data.share.accessLimit;
      accessCount.value = successResponse.data.share.accessCount;
      onRefreshQRCode();
      bus.emit("attachmentShareCreated", { attachmentId: props.attachmentId });
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "SharePreviewDialog.onCreate" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: Error creating attachment share";
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
    api.sharedAttachment.get(props.attachmentId).then((successResponse: attachmentShareResponseInterface) => {
      id.value = successResponse.data.share.id;
      enabled.value = successResponse.data.share.enabled;
      expiresAtTimestamp.value = successResponse.data.share.expiresAtTimestamp;
      hasExpiration.value = successResponse.data.share.expiresAtTimestamp > 0;
      accessLimit.value = successResponse.data.share.accessLimit;
      accessCount.value = successResponse.data.share.accessCount;
      onRefreshQRCode();
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "SharePreviewDialog.onGet" });
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
    api.sharedAttachment.update(props.attachmentId, enabled.value, expiresAtTimestamp.value, accessLimit.value).then((successResponse: attachmentShareResponseInterface) => {
      id.value = successResponse.data.share.id;
      enabled.value = successResponse.data.share.enabled;
      expiresAtTimestamp.value = successResponse.data.share.expiresAtTimestamp;
      hasExpiration.value = successResponse.data.share.expiresAtTimestamp > 0;
      accessLimit.value = successResponse.data.share.accessLimit;
      accessCount.value = successResponse.data.share.accessCount;
      onRefreshQRCode();
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "SharePreviewDialog.onSave" });
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
    api.sharedAttachment.remove(props.attachmentId).then(() => {
      bus.emit("attachmentShareDeleted", { attachmentId: props.attachmentId });
      emit('close');
    }).catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 401:
            state.ajaxErrors = false;
            bus.emit("reAuthRequired", { emitter: "SharePreviewDialog.onDelete" });
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
      if (msg.to?.includes("SharePreviewDialog.onCreate")) {
        onCreate();
      } else if (msg.to?.includes("SharePreviewDialog.onGet")) {
        onGet();
      } else if (msg.to?.includes("SharePreviewDialog.onSave")) {
        onSave();
      } else if (msg.to?.includes("SharePreviewDialog.onDelete")) {
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

  .blur {
    filter: blur(1.5rem);
  }
</style>