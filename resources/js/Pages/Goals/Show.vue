<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back -->
    <router-link to="/goals"
                 class="inline-flex items-center gap-1.5 text-slate-400 hover:text-indigo-400 transition-colors text-sm mb-6 animate-fade-in">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      Back to Goals
    </router-link>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else-if="goal">
      <!-- Goal header card -->
      <div class="glass rounded-2xl p-6 mb-6 animate-fade-in"
           :style="{ borderTop: `3px solid ${goal.color}` }">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl"
                 :style="{ background: `${goal.color}22`, border: `1px solid ${goal.color}44` }">
              <GoalIcon :name="goal.icon" class-name="w-7 h-7" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">{{ goal.name }}</h1>
              <p v-if="goal.description" class="text-slate-400 text-sm mt-0.5">{{ goal.description }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="openEdit" class="btn-secondary btn-sm">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </button>
            <button @click="showConfirm = true" class="btn-danger btn-sm">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Delete
            </button>
          </div>
        </div>

        <!-- Progress bar -->
        <div class="mb-1 flex justify-between items-center">
          <span class="text-xs text-slate-400">Saving Progress</span>
          <span class="font-bold text-sm" :style="{ color: goal.color }">{{ goal.percentage }}%</span>
        </div>
        <div class="h-3 bg-slate-800 rounded-full overflow-hidden mb-5">
          <div class="h-full rounded-full progress-bar-fill"
               :style="{
                 width: `${Math.min(goal.percentage, 100)}%`,
                 background: `linear-gradient(90deg, ${goal.color}, ${goal.color}cc)`,
                 boxShadow: `0 0 12px ${goal.color}55`,
               }"></div>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="bg-slate-800/50 rounded-xl p-4">
            <p class="text-xs text-slate-400 mb-1">Saved</p>
            <p class="font-bold text-emerald-400">{{ fmt(goal.saved_amount) }}</p>
          </div>
          <div class="bg-slate-800/50 rounded-xl p-4">
            <p class="text-xs text-slate-400 mb-1">Target</p>
            <p class="font-bold text-white">{{ fmt(goal.target_amount) }}</p>
          </div>
          <div class="bg-slate-800/50 rounded-xl p-4">
            <p class="text-xs text-slate-400 mb-1">Remaining</p>
            <p class="font-bold text-slate-300">{{ fmt(goal.remaining_amount) }}</p>
          </div>
          <div class="bg-slate-800/50 rounded-xl p-4">
            <p class="text-xs text-slate-400 mb-1">Days Left</p>
            <p class="font-bold" :class="goal.remaining_days <= 30 ? 'text-rose-400' : 'text-white'">
              {{ goal.remaining_days ?? '—' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Calculator + deposit row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Required saving calculator -->
        <div class="glass rounded-2xl p-5 animate-fade-in">
          <h2 class="font-semibold text-white mb-4 flex items-center gap-2">
            <ChartBarIcon class="w-5 h-5 text-indigo-400" /> Required Savings
          </h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b border-white/5">
              <span class="text-slate-400 text-sm">Daily</span>
              <span class="font-bold text-white">{{ goal.required_daily !== null ? fmt(goal.required_daily) : '—' }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-white/5">
              <span class="text-slate-400 text-sm">Weekly</span>
              <span class="font-bold text-white">{{ goal.required_weekly !== null ? fmt(goal.required_weekly) : '—' }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
              <span class="text-slate-400 text-sm">Monthly</span>
              <span class="font-bold text-white">{{ goal.required_monthly !== null ? fmt(goal.required_monthly) : '—' }}</span>
            </div>
          </div>
          <p class="text-xs text-slate-500 mt-3">
            Deadline: {{ goal.deadline ? new Date(goal.deadline).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Not set' }}
          </p>
        </div>

        <!-- Quick deposit -->
        <div class="glass rounded-2xl p-5 animate-fade-in flex flex-col justify-between">
          <div>
            <h2 class="font-semibold text-white mb-2 flex items-center gap-2">
              <BanknotesIcon class="w-5 h-5 text-emerald-400" /> Record Saving
            </h2>
            <p class="text-slate-400 text-sm mb-4">Log a deposit or withdrawal for this goal.</p>
          </div>
          <button @click="showHistory = true" class="btn-success w-full justify-center">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Entry
          </button>
        </div>
      </div>

      <!-- History table -->
      <div class="glass rounded-2xl p-5 animate-fade-in">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-white flex items-center gap-2">
            <ClipboardDocumentListIcon class="w-5 h-5 text-indigo-400" /> Saving History
            <span class="badge badge-indigo ml-1">{{ history.length }}</span>
          </h2>
          <button @click="showHistory = true" class="btn-secondary btn-sm">
            + Add Entry
          </button>
        </div>

        <!-- History loading -->
        <div v-if="histLoading" class="flex justify-center py-8">
          <div class="w-7 h-7 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else-if="!history.length" class="text-center py-8">
          <p class="text-slate-500 text-sm">No entries yet. Start logging your savings!</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-xs uppercase tracking-wider text-slate-500 border-b border-white/5">
                <th class="pb-3 text-left">Date</th>
                <th class="pb-3 text-left">Type</th>
                <th class="pb-3 text-right">Amount</th>
                <th class="pb-3 text-left pl-4">Note</th>
                <th class="pb-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="entry in history" :key="entry.id"
                  class="hover:bg-white/2 transition-colors group">
                <td class="py-3 text-slate-300">
                  {{ new Date(entry.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                </td>
                <td class="py-3">
                  <span :class="entry.type === 'deposit' ? 'badge-emerald' : 'badge-rose'" class="badge">
                    {{ entry.type === 'deposit' ? '↑ Deposit' : '↓ Withdrawal' }}
                  </span>
                </td>
                <td class="py-3 text-right font-semibold"
                    :class="entry.type === 'deposit' ? 'text-emerald-400' : 'text-rose-400'">
                  {{ entry.type === 'deposit' ? '+' : '-' }}{{ fmt(entry.amount) }}
                </td>
                <td class="py-3 text-slate-400 pl-4 max-w-xs truncate">{{ entry.note || '—' }}</td>
                <td class="py-3 text-right">
                  <button @click="confirmDeleteEntry(entry)"
                          class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg hover:bg-red-500/15 hover:text-red-400 text-slate-500 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <div v-else class="glass rounded-2xl p-12 text-center animate-fade-in">
      <p class="text-white font-semibold text-lg">Goal not found.</p>
      <router-link to="/goals" class="btn-primary mt-4 inline-flex">Back to Goals</router-link>
    </div>

    <!-- Modals -->
    <GoalFormModal v-model="showForm" :edit-goal="goal" @saved="refreshGoal" />
    <HistoryModal v-model="showHistory" :goal-id="route.params.id" @saved="refreshAll" />
    <ConfirmModal
      v-model="showConfirm"
      title="Delete Goal?"
      :message="`Delete '${goal?.name}' and all its history?`"
      confirm-text="Delete"
      @confirmed="handleDeleteGoal"
    />
    <ConfirmModal
      v-model="showEntryConfirm"
      title="Delete Entry?"
      message="This will revert the saved amount change."
      confirm-text="Delete Entry"
      @confirmed="handleDeleteEntry"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import GoalFormModal from '@/Components/GoalFormModal.vue';
import HistoryModal from '@/Components/HistoryModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import GoalIcon from '@/Components/GoalIcon.vue';
import { BanknotesIcon, ChartBarIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import { goalsApi, historyApi } from '@/composables/useApi.js';

const route  = useRoute();
const router = useRouter();

const goal     = ref(null);
const history  = ref([]);
const loading  = ref(true);
const histLoading = ref(false);
const showForm     = ref(false);
const showHistory  = ref(false);
const showConfirm  = ref(false);
const showEntryConfirm = ref(false);
const deletingEntry = ref(null);

const fmt = (v, cur) => new Intl.NumberFormat('en-US', {
  style: 'currency', currency: cur ?? goal.value?.currency ?? 'USD', minimumFractionDigits: 0,
}).format(v ?? 0);

onMounted(refreshAll);

async function refreshGoal() {
  const { data } = await goalsApi.show(route.params.id);
  goal.value = data.data;
}
async function refreshHistory() {
  histLoading.value = true;
  try {
    const { data } = await historyApi.index(route.params.id);
    history.value = data.data;
  } finally {
    histLoading.value = false;
  }
}
async function refreshAll() {
  loading.value = true;
  try {
    await Promise.all([refreshGoal(), refreshHistory()]);
  } finally {
    loading.value = false;
  }
}

function openEdit() { showForm.value = true; }

async function handleDeleteGoal() {
  await goalsApi.destroy(route.params.id);
  router.push('/goals');
}

function confirmDeleteEntry(entry) {
  deletingEntry.value = entry;
  showEntryConfirm.value = true;
}
async function handleDeleteEntry() {
  if (!deletingEntry.value) return;
  await historyApi.destroy(deletingEntry.value.id);
  showEntryConfirm.value = false;
  refreshAll();
}
</script>
