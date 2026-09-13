<template>
  <header class="sticky top-0 z-50 px-3 pt-3 sm:px-6">
    <div class="max-w-7xl mx-auto">
      <div class="island-bar w-full flex items-center justify-between gap-4 px-3 py-2 sm:px-4">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center glow-indigo"
               style="background: linear-gradient(135deg, #fb923c, #ea580c);">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="hidden sm:inline font-bold text-lg gradient-text group-hover:opacity-80 transition-opacity">
            Saving Challenge
          </span>
        </router-link>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-1 rounded-full bg-slate-950/40 p-1">
          <NavLink to="/" :exact="true" label="Dashboard">
            <template #icon>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </template>
              <span>Dashboard</span>
          </NavLink>
          <NavLink to="/goals" label="My Goals">
            <template #icon>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
            </template>
              <span>My Goals</span>
          </NavLink>
          <NavLink to="/transactions" label="Transactions">
            <template #icon>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5-3.5h.01M14.5 19.5h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </template>
              <span>Transactions</span>
          </NavLink>
          <NavLink to="/calculator" label="Calculator">
            <template #icon>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </template>
              <span>Calculator</span>
          </NavLink>
        </nav>

        <button @click="toggleTheme"
          class="theme-toggle p-2 rounded-full text-slate-400 hover:text-white transition-colors"
          :aria-label="isDark ? 'Switch to day mode' : 'Switch to night mode'"
          :title="isDark ? 'Switch to day mode' : 'Switch to night mode'">
          <SunIcon v-if="isDark" class="w-5 h-5" />
          <MoonIcon v-else class="w-5 h-5" />
        </button>

        <!-- Mobile menu button -->
        <button @click="mobileOpen = !mobileOpen"
                class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
          <svg v-if="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Nav -->
    <transition name="slide-down">
      <div v-if="mobileOpen" class="md:hidden glass border-t border-white/5 px-4 py-3 space-y-1">
        <MobileNavLink to="/" :exact="true" label="Dashboard" @click="mobileOpen = false">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span>Dashboard</span>
        </MobileNavLink>
        <MobileNavLink to="/goals" label="My Goals" @click="mobileOpen = false">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 01-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 01-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 01-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 01.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
            <span>My Goals</span>
        </MobileNavLink>
        <MobileNavLink to="/transactions" label="Transactions" @click="mobileOpen = false">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5-3.5h.01M14.5 19.5h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            <span>Transactions</span>
        </MobileNavLink>
        <MobileNavLink to="/calculator" label="Calculator" @click="mobileOpen = false">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z" /></svg>
            <span>Calculator</span>
        </MobileNavLink>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useLink } from 'vue-router';
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline';
import { useTheme } from '@/composables/useTheme.js';

const mobileOpen = ref(false);
const { isDark, toggleTheme } = useTheme();

// Desktop nav link component
const NavLink = {
  props: { to: String, exact: Boolean, label: String },
  slots: ['default', 'icon'],
  setup(props, { slots }) {
    const { isActive, isExactActive, href, navigate } = useLink({ to: props.to });
    return () => {
      const active = props.exact ? isExactActive.value : isActive.value;
      return h('a', {
        href: href.value,
        onClick: navigate,
          title: props.label,
          'aria-label': props.label,
          class: [
          'island-link flex items-center gap-2 px-3 py-2 rounded-full border text-sm font-medium transition-all duration-200',
          active
            ? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/25'
            : 'border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/5',
        ],
      }, [
        slots.icon?.(),
        slots.default?.(),
      ]);
    };
  },
};

const MobileNavLink = {
  props: { to: String, exact: Boolean, label: String },
  setup(props, { slots, emit }) {
    const { isActive, isExactActive, href, navigate } = useLink({ to: props.to });
    return () => {
      const active = props.exact ? isExactActive.value : isActive.value;
      return h('a', {
        href: href.value,
        onClick: (e) => { navigate(e); emit('click'); },
        title: props.label,
        'aria-label': props.label,
        class: [
          'flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-medium transition-all',
          active
            ? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/25'
            : 'border-transparent text-slate-400 hover:text-white hover:bg-white/5',
        ],
      }, slots.default?.());
    };
  },
};

import { h } from 'vue';
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.2s ease;
}
.slide-down-enter-from, .slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
