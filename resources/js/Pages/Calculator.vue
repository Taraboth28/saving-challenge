<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 animate-fade-in">
      <h1 class="text-3xl font-bold gradient-text mb-1">Saving Calculator</h1>
      <p class="text-slate-400 text-sm">Find out how much you need to save to reach your goal on time.</p>
    </div>

    <!-- Calculator card -->
    <div class="glass rounded-2xl p-6 mb-6 animate-fade-in">
      <h2 class="font-semibold text-white mb-5 flex items-center gap-2">
        <CalculatorIcon class="w-5 h-5 text-indigo-400" /> Enter Your Details
      </h2>

      <div class="space-y-4">
        <!-- Target amount -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
            Target Amount *
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
              {{ currencySymbol }}
            </span>
            <input v-model.number="form.target" type="number" min="1" step="0.01"
                   placeholder="10,000" class="input-dark !pl-8" @input="calculate" />
          </div>
        </div>

        <!-- Already saved -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
            Already Saved
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
              {{ currencySymbol }}
            </span>
            <input v-model.number="form.saved" type="number" min="0" step="0.01"
                   placeholder="0" class="input-dark !pl-8" @input="calculate" />
          </div>
        </div>

        <!-- Deadline -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
            Target Deadline *
          </label>
          <input v-model="form.deadline" type="date" :min="minDate" class="input-dark" @change="calculate" />
        </div>

        <!-- Currency -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
            Currency
          </label>
          <select v-model="form.currency" class="input-dark" @change="calculate">
            <option v-for="c in currencies" :key="c.code" :value="c.code">
              {{ c.symbol }} {{ c.code }} — {{ c.name }}
            </option>
          </select>
        </div>

        <button @click="calculate" class="btn-primary w-full justify-center mt-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          Calculate
        </button>
      </div>
    </div>

    <!-- Results -->
    <transition name="results">
      <div v-if="result" class="animate-fade-in">
        <!-- Summary card -->
        <div class="glass rounded-2xl p-6 mb-4"
             :style="{ borderTop: '3px solid #6366f1' }">
          <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <ChartBarIcon class="w-5 h-5 text-indigo-400" /> Result
          </h2>

          <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="bg-slate-800/50 rounded-xl p-4">
              <p class="text-xs text-slate-400 mb-1">Target</p>
              <p class="font-bold text-white">{{ fmt(form.target) }}</p>
            </div>
            <div class="bg-slate-800/50 rounded-xl p-4">
              <p class="text-xs text-slate-400 mb-1">Already Saved</p>
              <p class="font-bold text-emerald-400">{{ fmt(form.saved ?? 0) }}</p>
            </div>
            <div class="bg-slate-800/50 rounded-xl p-4">
              <p class="text-xs text-slate-400 mb-1">Still Needed</p>
              <p class="font-bold text-indigo-300">{{ fmt(result.remaining) }}</p>
            </div>
            <div class="bg-slate-800/50 rounded-xl p-4">
              <p class="text-xs text-slate-400 mb-1">Days Remaining</p>
              <p class="font-bold" :class="result.days <= 30 ? 'text-rose-400' : 'text-white'">
                {{ result.days }} days
              </p>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mb-1 flex justify-between">
            <span class="text-xs text-slate-400">Progress</span>
            <span class="text-xs font-bold text-indigo-400">{{ result.percentage }}%</span>
          </div>
          <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full rounded-full progress-bar-fill"
                 style="background: linear-gradient(90deg, #6366f1, #8b5cf6);"
                 :style="{ width: `${Math.min(result.percentage, 100)}%` }"></div>
          </div>
        </div>

        <!-- Required amounts -->
        <div class="glass rounded-2xl p-6 mb-4">
          <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <LightBulbIcon class="w-5 h-5 text-amber-400" /> How Much Should I Save?
          </h2>
          <div class="space-y-3">
            <div v-for="row in result.breakdown" :key="row.label"
                 class="flex items-center justify-between p-3 rounded-xl hover:bg-white/3 transition-colors">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                     :style="{ background: `${row.color}22` }">
                  <component :is="row.icon" class="w-4 h-4" :style="{ color: row.color }" />
                </div>
                <div>
                  <p class="text-sm font-medium text-white">{{ row.label }}</p>
                  <p class="text-xs text-slate-500">{{ row.subtitle }}</p>
                </div>
              </div>
              <p class="font-bold text-white text-base">{{ fmt(row.amount) }}</p>
            </div>
          </div>
        </div>

        <!-- Motivational tip -->
        <div class="glass rounded-2xl p-5 border border-indigo-500/20 animate-fade-in">
          <div class="flex gap-3">
            <BoltIcon class="w-7 h-7 text-indigo-400 shrink-0" />
            <div>
              <p class="text-sm font-semibold text-indigo-300 mb-1">Saving Tip</p>
              <p class="text-slate-400 text-sm">{{ motivationalTip }}</p>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { AcademicCapIcon, BoltIcon, CalculatorIcon, CalendarDaysIcon, ChartBarIcon, LightBulbIcon } from '@heroicons/vue/24/outline';

const form = ref({
  target: null, saved: null, deadline: '', currency: 'USD',
});
const result = ref(null);

const minDate = computed(() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  const pad = (v) => String(v).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
});

const currencies = [
  { code: 'USD', symbol: '$',  name: 'US Dollar'     },
  { code: 'KHR', symbol: '៛',  name: 'Cambodian Riel' },
];

const currencySymbol = computed(
  () => currencies.find(c => c.code === form.value.currency)?.symbol ?? '$'
);

const fmt = (v) => new Intl.NumberFormat('en-US', {
  style: 'currency', currency: form.value.currency ?? 'USD', minimumFractionDigits: 0,
}).format(v ?? 0);

const tips = [
  'Automate your savings by setting up automatic transfers on payday.',
  'Cut one unnecessary subscription per month and redirect it to savings.',
  'The 50/30/20 rule: 20% of your income goes to savings.',
  'Track small expenses — coffee, snacks — they add up faster than you think.',
  'Every time you skip an impulse purchase, transfer that amount to savings.',
  'Build an emergency fund first before investing — aim for 3–6 months of expenses.',
];
const motivationalTip = computed(() => tips[Math.floor(Math.random() * tips.length)]);

function calculate() {
  const target = form.value.target;
  const saved  = form.value.saved ?? 0;
  const deadline = form.value.deadline;

  if (!target || target <= 0 || !deadline) {
    result.value = null;
    return;
  }

  const remaining = Math.max(0, target - saved);
  const percentage = target > 0 ? Math.round((saved / target) * 100 * 100) / 100 : 0;

  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const end = new Date(`${deadline}T00:00:00`);
  const days = Math.max(0, Math.round((end - today) / (1000 * 60 * 60 * 24)));

  const daily   = days > 0 ? Math.ceil(remaining / days * 100) / 100 : (remaining > 0 ? remaining : 0);
  const weekly  = days > 0 ? Math.ceil(Math.min(remaining, daily * 7) * 100) / 100 : (remaining > 0 ? remaining : 0);
  const monthly = days > 0 ? Math.ceil(Math.min(remaining, daily * 30) * 100) / 100 : (remaining > 0 ? remaining : 0);

  result.value = {
    remaining, percentage, days,
    breakdown: [
      { label: 'Daily',   amount: daily,   icon: CalendarDaysIcon, color: '#6366f1', subtitle: 'Every day'       },
      { label: 'Weekly',  amount: weekly,  icon: ChartBarIcon, color: '#8b5cf6', subtitle: 'Every week'      },
      { label: 'Monthly', amount: monthly, icon: AcademicCapIcon, color: '#10b981', subtitle: 'Every month'     },
    ],
  };
}
</script>

<style scoped>
.results-enter-active { transition: all 0.4s ease; }
.results-enter-from { opacity: 0; transform: translateY(12px); }
</style>
