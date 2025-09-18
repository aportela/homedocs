<template>
  <CustomWidget title="Personal information" caption="Update your data" icon="contact_mail">
    <template v-slot:content>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-input class="q-my-md" dense outlined v-model="email" type="email" name="email" :label="t('Email')" readonly
          :error="false">
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <CustomInputPassword class="q-my-md" dense outlined v-model="password" name="password"
          :label="t('New password')" :error="validator.hasErrors" ref="passwordRef"
          :errorMessage="validator.message ? t(validator.message) : ''" :disable="loading" :autofocus="true"
          :rules="formUtils.requiredFieldRules" lazy-rules>
        </CustomInputPassword>
        <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width q-my-xs"
          icon=" account_circle" :disable="loading || !password" :loading="loading" type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t('Update profile') }}
          </template>
        </q-btn>
        <CustomBanner v-if="profileUpdatedSuccessfully || error" :text="bannerText"
          :success="profileUpdatedSuccessfully" :error="error">
          <template v-slot:details v-if="error && initialState.isDevEnvironment && apiError">
            <APIErrorDetails class="q-mt-md" :apiError="apiError"></APIErrorDetails>
          </template>
        </CustomBanner>
      </form>
    </template>
  </CustomWidget>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useI18n } from 'vue-i18n'
import { useInitialStateStore } from "stores/initialState";

import { api } from 'boot/axios'
import { useFormUtils } from "src/composables/formUtils";

import { default as CustomWidget } from "src/components/CustomWidget.vue";
import { default as CustomInputPassword } from "src/components/CustomInputPassword.vue";
import { default as CustomBanner } from "src/components/CustomBanner.vue";
import { default as APIErrorDetails } from "components/APIErrorDetails.vue";

const { t } = useI18n();
const formUtils = useFormUtils();
const initialState = useInitialStateStore();

const loading = ref(false);
const error = ref(false);

const email = ref(null);
const password = ref(null);
const passwordRef = ref(null);

const profileUpdatedSuccessfully = ref(false);
const apiError = ref(null);

const validator = ref({
  hasErrors: false,
  message: null
});

const bannerText = computed(() => {
  if (profileUpdatedSuccessfully.value) {
    return ("Profile has been successfully updated");
  } else if (error.value) {
    return ("Error loading data")
  } else {
    return (null);
  }
});

const onResetForm = () => {
  validator.value.hasErrors = false;
  validator.value.message = null;
  passwordRef.value.resetValidation();
}

const onGetProfile = () => {
  loading.value = true;
  error.value = false;
  email.value = null;
  password.value = null;
  api.user.getProfile()
    .then((successResponse) => {
      loading.value = false;
      email.value = successResponse.data.data.email;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    })
    .catch((errorResponse) => {
      loading.value = false;
      error.value = true;
      apiError.value = errorResponse.customAPIErrorDetails;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    });
}

const onValidateForm = () => {
  onResetForm();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(passwordRef.value.hasError)) {
      onSubmitForm();
    } else {
      passwordRef.value?.focus();
    }
  });
}

const onSubmitForm = () => {
  loading.value = true;
  error.value = false;
  apiError.value = null;
  profileUpdatedSuccessfully.value = false;
  api.user
    .updateProfile(email.value, password.value)
    .then((successResponse) => {
      loading.value = false;
      profileUpdatedSuccessfully.value = true;
      email.value = successResponse.data.data.email;
      password.value = null;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    })
    .catch((errorResponse) => {
      loading.value = false;
      error.value = true;
      apiError.value = errorResponse.customAPIErrorDetails;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    });
}

onMounted(() => {
  onGetProfile();
});

</script>