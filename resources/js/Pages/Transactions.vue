<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 animate-fade-in">
      <h1 class="text-3xl font-bold gradient-text mb-1">Transaction Report</h1>
      <p class="text-slate-400 text-sm">Review every deposit and withdrawal across your goals.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="glass rounded-2xl p-5">
        <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">Total Deposits</p>
        <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(totalDeposits) }}</p>
      </div>
      <div class="glass rounded-2xl p-5">
        <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">Total Withdrawals</p>
        <p class="text-2xl font-bold text-rose-400">{{ formatCurrency(totalWithdrawals) }}</p>
      </div>
      <div class="glass rounded-2xl p-5">
        <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">Net Savings</p>
        <p class="text-2xl font-bold text-white">{{ formatCurrency(totalDeposits - totalWithdrawals) }}</p>
      </div>
    </div>

    <div class="glass rounded-2xl p-4 mb-6 flex flex-wrap gap-3 items-center">
      <SelectMenu v-model="typeFilter" class="w-40" :options="typeOptions" />
      <SelectMenu v-model="goalFilter" class="flex-1 min-w-48" :options="goalOptions" />
    </div>

    <div class="glass rounded-2xl p-5 animate-fade-in">
      <div v-if="loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
      </div>
      <div v-else-if="!filteredTransactions.length" class="text-center py-12 text-slate-500 text-sm">
        No transactions match the selected filters.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs uppercase tracking-wider text-slate-500 border-b border-white/5">
              <th class="pb-3 text-left">Date</th>
              <th class="pb-3 text-left">Goal</th>
              <th class="pb-3 text-left">Type</th>
              <th class="pb-3 text-right">Amount</th>
              <th class="pb-3 text-left pl-4">Note</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="transaction in filteredTransactions" :key="transaction.id">
              <td class="py-3 text-slate-300">{{ formatDate(transaction.date) }}</td>
              <td class="py-3 text-white font-medium">{{ transaction.goal_name }}</td>
              <td class="py-3">
                <span class="badge" :class="transaction.type === 'deposit' ? 'badge-emerald' : 'badge-rose'">
                  {{ transaction.type === 'deposit' ? '↑ Deposit' : '↓ Withdrawal' }}
                </span>
              </td>
              <td class="py-3 text-right font-semibold" :class="transaction.type === 'deposit' ? 'text-emerald-400' : 'text-rose-400'">
                {{ transaction.type === 'deposit' ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency) }}
              </td>
              <td class="py-3 pl-4 text-slate-400">{{ transaction.note || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { historyApi } from '@/composables/useApi.js';
import SelectMenu from '@/Components/SelectMenu.vue';

const transactions = ref([]);
const loading = ref(true);
const typeFilter = ref('all');
const goalFilter = ref('all');
const typeOptions = [
  { value: 'all', label: 'All Types' },
  { value: 'deposit', label: 'Deposits' },
  { value: 'withdrawal', label: 'Withdrawals' },
];

onMounted(async () => {
  try {
    const { data } = await historyApi.report();
    transactions.value = data.data;
  } finally {
    loading.value = false;
  }
});

const goals = computed(() => [...new Set(transactions.value.map(transaction => transaction.goal_name))].sort());
const goalOptions = computed(() => [
  { value: 'all', label: 'All Goals' },
  ...goals.value.map(goal => ({ value: goal, label: goal })),
]);
const filteredTransactions = computed(() => transactions.value
  .filter((transaction) => {
  const matchesType = typeFilter.value === 'all' || transaction.type === typeFilter.value;
  const matchesGoal = goalFilter.value === 'all' || transaction.goal_name === goalFilter.value;
  return matchesType && matchesGoal;
  })
  .sort((a, b) => {
    const dateOrder = String(b.date || '').localeCompare(String(a.date || ''));
    return dateOrder || String(b.created_at || '').localeCompare(String(a.created_at || ''));
  }));
const totalDeposits = computed(() => sumByType('deposit'));
const totalWithdrawals = computed(() => sumByType('withdrawal'));

function sumByType(type) {
  return transactions.value
    .filter(transaction => transaction.type === type)
    .reduce((total, transaction) => total + Number(transaction.amount || 0), 0);
}
function formatCurrency(amount, currency = 'USD') {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency, minimumFractionDigits: 2 }).format(amount || 0);
}
function formatDate(date) {
  return new Date(date).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
  });
}
</script>
