<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page header -->
    <div class="mb-8 animate-fade-in">
      <h1 class="text-3xl font-bold gradient-text mb-1">Dashboard</h1>
      <p class="text-slate-400 text-sm">Your savings overview at a glance</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-10 h-10 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else>
      <!-- Stat cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8 stagger-children">
        <StatCard label="Total Goals"    :value="stats.total_goals"     icon="trophy" color="#6366f1" animate-fade-in />
        <StatCard label="Active Goals"   :value="stats.active_goals"    icon="bolt" color="#f59e0b" animate-fade-in />
        <StatCard label="Completed"      :value="stats.completed_goals" icon="check" color="#10b981" animate-fade-in />
        <StatCard label="Overall Progress" :value="`${stats.overall_percent}%`" icon="trend" color="#8b5cf6" animate-fade-in />
      </div>

      <!-- Savings summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="glass rounded-2xl p-5 animate-fade-in">
          <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">Total Target</p>
          <p class="text-2xl font-bold text-white">{{ fmt(stats.total_target) }}</p>
        </div>
        <div class="glass rounded-2xl p-5 animate-fade-in glow-emerald">
          <p class="text-xs uppercase tracking-wider text-emerald-400 mb-1">Total Saved</p>
          <p class="text-2xl font-bold text-emerald-400">{{ fmt(stats.total_saved) }}</p>
        </div>
        <div class="glass rounded-2xl p-5 animate-fade-in">
          <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">Remaining</p>
          <p class="text-2xl font-bold text-slate-300">{{ fmt(stats.total_remaining) }}</p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Doughnut -->
        <div class="glass rounded-2xl p-6 animate-fade-in">
          <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <ChartBarIcon class="w-5 h-5 text-indigo-400" /> Overall Progress
          </h2>
          <div class="flex items-center gap-6">
            <div class="relative w-36 h-36 flex-shrink-0">
              <Doughnut :data="doughnutData" :options="doughnutOptions" />
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ stats.overall_percent }}%</span>
                <span class="text-xs text-slate-400">saved</span>
              </div>
            </div>
            <div class="space-y-2 flex-1">
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-sm bg-indigo-400"></div>
                <span class="text-xs text-slate-300">Saved: {{ fmt(stats.total_saved) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-sm bg-slate-600"></div>
                <span class="text-xs text-slate-300">Remaining: {{ fmt(stats.total_remaining) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Monthly trend -->
        <div class="glass rounded-2xl p-6 animate-fade-in">
          <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <ArrowTrendingUpIcon class="w-5 h-5 text-emerald-400" /> Monthly Activity
          </h2>
          <Bar v-if="barData" :data="barData" :options="barOptions" class="max-h-40" />
          <p v-else class="text-slate-500 text-sm text-center py-10">No history yet</p>
        </div>
      </div>

      <!-- Per-goal breakdown -->
      <div v-if="stats.goal_breakdown?.length" class="glass rounded-2xl p-6 animate-fade-in mb-8">
        <h2 class="font-semibold text-white mb-5 flex items-center gap-2">
          <TrophyIcon class="w-5 h-5 text-amber-400" /> Goals Breakdown
        </h2>
        <div class="space-y-4">
          <div v-for="g in stats.goal_breakdown" :key="g.id" class="group">
            <div class="flex items-center justify-between mb-1">
              <router-link :to="`/goals/${g.id}`"
                           class="text-sm font-medium text-slate-300 hover:text-indigo-300 transition-colors truncate max-w-xs">
                {{ g.name }}
              </router-link>
              <span class="text-xs font-bold ml-2" :style="{ color: g.color }">{{ g.percentage }}%</span>
            </div>
            <div class="h-2.5 bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full rounded-full progress-bar-fill"
                   :style="{
                     width: `${Math.min(g.percentage, 100)}%`,
                     background: `linear-gradient(90deg, ${g.color}, ${g.color}bb)`,
                   }"></div>
            </div>
            <div class="flex justify-between text-xs text-slate-500 mt-1">
              <span>{{ fmt(g.saved_amount) }}</span>
              <span>{{ fmt(g.target_amount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="glass rounded-2xl p-12 text-center animate-fade-in">
        <GoalIcon name="piggy-bank" class-name="w-12 h-12 mx-auto mb-4 text-indigo-400" />
        <h3 class="text-white font-semibold text-lg mb-2">No goals yet</h3>
        <p class="text-slate-400 text-sm mb-5">Start your saving journey by creating your first goal.</p>
        <router-link to="/goals" class="btn-primary inline-flex">
          Create a Goal
        </router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Doughnut, Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale,
} from 'chart.js';
import { dashboardApi } from '@/composables/useApi.js';
import StatCard from '@/Components/StatCard.vue';
import GoalIcon from '@/Components/GoalIcon.vue';
import { ArrowTrendingUpIcon, ChartBarIcon, TrophyIcon } from '@heroicons/vue/24/outline';

ChartJS.register(ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const stats   = ref({ total_goals: 0, active_goals: 0, completed_goals: 0, total_target: 0, total_saved: 0, total_remaining: 0, overall_percent: 0, goal_breakdown: [], monthly_trend: [] });
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await dashboardApi.stats();
    stats.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

const fmt = (v) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(v ?? 0);

const doughnutData = computed(() => ({
  datasets: [{
    data: [stats.value.total_saved, stats.value.total_remaining],
    backgroundColor: ['#6366f1', '#1e293b'],
    borderWidth: 0,
    hoverOffset: 4,
  }],
}));
const doughnutOptions = {
  cutout: '75%',
  plugins: { legend: { display: false }, tooltip: { enabled: false } },
  responsive: true,
  maintainAspectRatio: false,
};

const barData = computed(() => {
  const trend = stats.value.monthly_trend;
  if (!trend?.length) return null;
  return {
    labels: trend.map(t => t.label),
    datasets: [
      {
        label: 'Deposits',
        data: trend.map(t => t.deposit),
        backgroundColor: 'rgba(99,102,241,0.7)',
        borderRadius: 6,
        borderSkipped: false,
      },
      {
        label: 'Withdrawals',
        data: trend.map(t => t.withdrawal),
        backgroundColor: 'rgba(239,68,68,0.5)',
        borderRadius: 6,
        borderSkipped: false,
      },
    ],
  };
});
const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: '#94a3b8', font: { size: 11 } } },
  },
  scales: {
    x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'transparent' } },
    y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
  },
};
</script>
