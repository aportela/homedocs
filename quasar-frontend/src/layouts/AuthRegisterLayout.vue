<template>
  <q-layout>
    <div class="row" style="height: 100vh">
      <div class="col-12 col-lg-8 col-md-12 col-sm-12 slogan-image-cover flex flex-center">
        <div class="q-pa-xl text-center text-white">
          <h2 class="text-h2 text-weight-bold q-mb-md">
            Welcome to HomeDocs
          </h2>
          <div class="text-h5">
            Where your documents find their place.
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-4 justify-center q-pa-xl">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
    </div>
  </q-layout>
</template>

<style scoped>
.slogan-image-cover {
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.8) 100%), url('https://images.pexels.com/photos/3184460/pexels-photo-3184460.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
  background-size: cover;
  background-position: center;
  filter: grayscale(100%);
}
</style>
