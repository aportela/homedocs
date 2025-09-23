<template>
  <BaseWidget title="Personal information" caption="Update your data" icon="contact_mail">
    <template v-slot:content>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-input class="q-my-md" dense outlined v-model="profile.email" type="email" name="email" :label="t('Email')"
          readonly :error="false">
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <PasswordFieldCustomInput class="q-my-md" dense outlined v-model="profile.password" name="password"
          :label="t('New password')" :error="validator.hasErrors" ref="passwordRef"
          :errorMessage="validator.message ? t(validator.message) : ''" :disable="state.loading" :autofocus="true"
          :rules="formUtils.requiredFieldRules" lazy-rules>
        </PasswordFieldCustomInput>
        <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width q-my-xs"
          icon=" account_circle" :disable="state.loading || !profile.password" :loading="state.loading" type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t('Update profile') }}
          </template>
        </q-btn>
        <CustomBanner v-if="profileUpdatedSuccessfully" text="Profile has been successfully updated" success
          class="q-mt-lg">
        </CustomBanner>
        <CustomErrorBanner v-else-if="state.loadingError" text="Error loading data" :apiError="state.apiError"
          class="q-mt-lg">
        </CustomErrorBanner>
      </form>
    </template>
  </BaseWidget>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from "vue";
import { useI18n } from 'vue-i18n'

import { api } from 'boot/axios'
import { useFormUtils } from "src/composables/formUtils";

import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";
import { default as PasswordFieldCustomInput } from "src/components/Forms/Fields/PasswordFieldCustomInput.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const formUtils = useFormUtils();

const state = reactive({
  loading: false,
  loadingError: false,
  apiError: null
});

const profile = reactive({
  email: null,
  password: null
});

const passwordRef = ref(null);

const profileUpdatedSuccessfully = ref(false);

const validator = reactive({
  hasErrors: false,
  message: null
});

const onResetForm = () => {
  validator.hasErrors = false;
  validator.message = null;
  passwordRef.value?.resetValidation();
}

const onGetProfile = () => {
  state.loading = true;
  state.loadingError = false;
  state.apiError = null;
  profile.email = null;
  profile.password = null;
  api.user.getProfile()
    .then((successResponse) => {
      profile.email = successResponse.data.data.email;
      state.loading = false;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      state.apiError = errorResponse.customAPIErrorDetails;
      state.loading = false;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    });
}

const onValidateForm = () => {
  onResetForm();
  passwordRef.value?.validate();
  if (!passwordRef.value?.hasError) {
    onSubmitForm();
  } else {
    nextTick(() => {
      passwordRef.value?.focus();
    });
  }
}

const onSubmitForm = () => {
  state.loading = true;
  state.loadingError = false;
  state.apiError = null;
  profileUpdatedSuccessfully.value = false;
  api.user
    .updateProfile(profile.email, profile.password)
    .then((successResponse) => {
      profileUpdatedSuccessfully.value = true;
      profile.email = successResponse.data.data.email;
      profile.password = null;
      state.loading = false;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      state.apiError = errorResponse.customAPIErrorDetails;
      state.loading = false;
      nextTick(() => {
        passwordRef.value?.focus();
      });
    });
}

onMounted(() => {
  onGetProfile();
});

</script>