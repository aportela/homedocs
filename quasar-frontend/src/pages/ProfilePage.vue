<template>
  <q-page>
    <div class="slogan-image-cover flex flex-center q-mx-auto q-mt-lg" style="width: 90%; height: 300px;">
      <q-avatar icon="edit" size="48px" class="bg-dark text-white"
        style="position: absolute; top: 16px; right: 16px;" />
      <h2 class="text-h2 text-white text-weight-bolder q-my-none">My profile</h2>

    </div>
    <div class="text-center">
      <q-avatar icon="account_circle" size="200px" class="bg-grey-4 q-mx-auto"
        style="position: re1lative; top: -100px;" />
    </div>

    <q-card class="q-mx-auto" style="width: 90%;">
      <q-card-section class="text-center">
        <h4>{{ t("Personal information") }}</h4>
        <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')" disable>
          <template v-slot:prepend>
            <q-icon name="alternate_email" />
          </template>
        </q-input>
        <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password"
          :type="visiblePassword ? 'text' : 'password'" :label="t('New password')" :disable="loading"
          :error="remoteValidation.password.hasErrors"
          :errorMessage="remoteValidation.password.message ? t(remoteValidation.password.message) : ''">
          <template v-slot:prepend>
            <q-icon name="key" />
          </template>
          <template v-slot:append>
            <q-icon :name="visiblePassword ? 'visibility_off' : 'visibility'" class="cursor-pointer"
              @click="visiblePassword = !visiblePassword" />
          </template>
          <q-tooltip anchor="bottom right" self="top end">{{ t(visiblePassword ? "Hide password" : "Show password")
          }}</q-tooltip>
        </q-input>
      </q-card-section>
      <q-card-section>

        <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width" icon="account_circle"
          :disable="loading || !password" :loading="loading" type="submit">
          <template v-slot:loading>
            <q-spinner-hourglass class="on-left" />
            {{ t('Update profile') }}
          </template>
        </q-btn>
      </q-card-section>
    </q-card>

  </q-page>
</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from 'vue-i18n'
import { useInitialStateStore } from "stores/initialState";

const { t } = useI18n();

const initialState = useInitialStateStore();

const loading = ref(false);

const email = computed(() => initialState.session.email);

const password = ref(null);
const emailRef = ref(null);
const passwordRef = ref(null);
const visiblePassword = ref(false);

const remoteValidation = ref({
  password: {
    hasErrors: false,
    message: null
  }
});

const fieldIsRequiredLabel = computed(() => t('Field is required'));

</script>

<style scoped>
.slogan-image-cover {
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('https://images.pexels.com/photos/3184460/pexels-photo-3184460.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
  background-size: cover;
  background-position: bottom;
  filter: grayscale(100%);
  border-radius: 16px;

}
</style>