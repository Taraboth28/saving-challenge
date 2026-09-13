<template>
    <div class="app-shell min-h-screen">
    <Navbar />

    <main class="relative z-10">
      <router-view v-slot="{ Component, route }">
        <transition name="page" mode="out-in">
          <component :is="Component" :key="route.path" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from '@/Components/Navbar.vue';
import { useTheme } from '@/composables/useTheme.js';

const route = useRoute();
useTheme();
watchEffect(() => {
  if (route.meta?.title) document.title = route.meta.title;
});
</script>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
