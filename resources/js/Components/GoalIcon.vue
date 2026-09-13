<template>
  <component :is="icon" :class="className" aria-hidden="true" />
</template>

<script setup>
import { computed } from 'vue';
import {
  AcademicCapIcon,
  BanknotesIcon,
  BeakerIcon,
  BoltIcon,
  ChartBarIcon,
  ComputerDesktopIcon,
  HeartIcon,
  HomeIcon,
  PaperAirplaneIcon,
  ShieldCheckIcon,
  TrophyIcon,
  TruckIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  name: { type: String, default: 'piggy-bank' },
  className: { type: String, default: 'w-6 h-6' },
});

const icons = {
  'piggy-bank': BanknotesIcon,
  home: HomeIcon,
  car: TruckIcon,
  vacation: PaperAirplaneIcon,
  education: AcademicCapIcon,
  emergency: ShieldCheckIcon,
  wedding: TrophyIcon,
  gadget: ComputerDesktopIcon,
  health: HeartIcon,
  investment: ChartBarIcon,
};

const icon = computed(() => icons[props.name] ?? BeakerIcon);
</script>
