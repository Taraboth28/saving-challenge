<template>
  <div class="w-full p-4 rounded-2xl glass card-hover animate-fade-in"
       :style="{ borderLeft: `3px solid ${color}` }">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ label }}</p>
        <p class="text-2xl font-bold text-white">{{ value }}</p>
      </div>
      <div class="w-11 h-11 rounded-xl flex items-center justify-center"
           :style="{ background: `${color}20` }">
        <component :is="iconComponent" class="w-5 h-5" :style="{ color }" aria-hidden="true" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowTrendingUpIcon, BoltIcon, CheckCircleIcon, TrophyIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  label: String,
  value: [String, Number],
  icon:  String,
  color: { type: String, default: '#6366f1' },
});

const iconComponent = computed(() => ({
  trophy: TrophyIcon,
  bolt: BoltIcon,
  check: CheckCircleIcon,
  trend: ArrowTrendingUpIcon,
}[props.icon] ?? TrophyIcon));
</script>
