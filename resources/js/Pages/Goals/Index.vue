<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 animate-fade-in">
      <div>
        <h1 class="text-3xl font-bold gradient-text mb-1">My Goals</h1>
        <p class="text-slate-400 text-sm">Manage all your saving goals</p>
      </div>
      <button @click="openCreate" id="create-goal-btn" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        New Goal
      </button>
    </div>

    <!-- Filter / search bar -->
    <div class="glass rounded-2xl p-4 mb-6 animate-fade-in flex flex-wrap gap-3 items-center">
      <input v-model="search" type="text" placeholder="Search goals..."
             class="input-dark flex-1 min-w-40 !py-2 text-sm" />
      <SelectMenu v-model="filterStatus" class="w-36" :options="statusOptions" />
      <SelectMenu v-model="sortBy" class="w-40" :options="sortOptions" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Empty state -->
    <div v-else-if="!filteredGoals.length" class="glass rounded-2xl p-14 text-center animate-fade-in">
      <MagnifyingGlassIcon v-if="goals.length" class="w-12 h-12 mx-auto mb-4 text-indigo-400" />
      <GoalIcon v-else name="piggy-bank" class-name="w-12 h-12 mx-auto mb-4 text-indigo-400" />
      <h3 class="text-white font-semibold text-lg mb-2">
        {{ goals.length ? 'No matching goals' : 'No goals yet' }}
      </h3>
      <p class="text-slate-400 text-sm mb-5">
        {{ goals.length ? 'Try adjusting your filters.' : 'Create your first saving goal to get started!' }}
      </p>
      <button v-if="!goals.length" @click="openCreate" class="btn-primary">
        Create First Goal
      </button>
    </div>

    <!-- Goals grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 stagger-children">
      <GoalCard
        v-for="goal in filteredGoals"
        :key="goal.id"
        :goal="goal"
        @edit="openEdit"
        @delete="confirmDelete"
        @save="openSaving"
      />
    </div>

    <!-- Modals -->
    <GoalFormModal
      v-model="showForm"
      :edit-goal="editingGoal"
      @saved="refresh"
    />
    <HistoryModal
      v-model="showHistory"
      :goal-id="savingGoal?.id ?? ''"
      @saved="refresh"
    />
    <ConfirmModal
      v-model="showConfirm"
      title="Delete Goal?"
      :message="`Are you sure you want to delete '${deletingGoal?.name}'? All saving history will also be deleted.`"
      confirm-text="Delete Goal"
      @confirmed="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import GoalCard from '@/Components/GoalCard.vue';
import GoalFormModal from '@/Components/GoalFormModal.vue';
import HistoryModal from '@/Components/HistoryModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import GoalIcon from '@/Components/GoalIcon.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { goalsApi } from '@/composables/useApi.js';

const goals        = ref([]);
const loading      = ref(true);
const showForm     = ref(false);
const showHistory  = ref(false);
const editingGoal  = ref(null);
const savingGoal   = ref(null);
const showConfirm  = ref(false);
const deletingGoal = ref(null);
const search       = ref('');
const filterStatus = ref('all');
const sortBy       = ref('created_at');
const statusOptions = [
  { value: 'all', label: 'All Status' },
  { value: 'active', label: 'Active' },
  { value: 'completed', label: 'Completed' },
  { value: 'urgent', label: 'Urgent (≤30d)' },
];
const sortOptions = [
  { value: 'created_at', label: 'Newest First' },
  { value: 'deadline', label: 'Deadline' },
  { value: 'percentage', label: 'Progress' },
  { value: 'remaining_amount', label: 'Remaining' },
];

onMounted(refresh);

async function refresh() {
  loading.value = true;
  try {
    const { data } = await goalsApi.index();
    goals.value = data.data;
  } finally {
    loading.value = false;
  }
}

const filteredGoals = computed(() => {
  let list = [...goals.value];

  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(g => g.name.toLowerCase().includes(q) || (g.description ?? '').toLowerCase().includes(q));
  }
  if (filterStatus.value === 'active')    list = list.filter(g => !g.is_completed);
  if (filterStatus.value === 'completed') list = list.filter(g => g.is_completed);
  if (filterStatus.value === 'urgent')    list = list.filter(g => !g.is_completed && g.remaining_days <= 30);

  list.sort((a, b) => {
    if (sortBy.value === 'deadline')    return (a.deadline ?? '') > (b.deadline ?? '') ? 1 : -1;
    if (sortBy.value === 'percentage')  return b.percentage - a.percentage;
    if (sortBy.value === 'remaining_amount') return b.remaining_amount - a.remaining_amount;
    return (b.created_at ?? '') > (a.created_at ?? '') ? 1 : -1;
  });

  return list;
});

function openCreate() {
  editingGoal.value = null;
  showForm.value = true;
}
function openEdit(goal) {
  editingGoal.value = goal;
  showForm.value = true;
}
function openSaving(goal) {
  savingGoal.value = goal;
  showHistory.value = true;
}
function confirmDelete(goal) {
  deletingGoal.value = goal;
  showConfirm.value = true;
}
async function handleDelete() {
  if (!deletingGoal.value) return;
  await goalsApi.destroy(deletingGoal.value.id);
  showConfirm.value = false;
  refresh();
}
</script>
