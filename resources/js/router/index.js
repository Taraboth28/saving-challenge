import Dashboard from '@/Pages/Dashboard.vue';
import GoalsIndex from '@/Pages/Goals/Index.vue';
import GoalShow from '@/Pages/Goals/Show.vue';
import Calculator from '@/Pages/Calculator.vue';
import Transactions from '@/Pages/Transactions.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard — Saving Challenge' },
    },
    {
        path: '/goals',
        name: 'goals.index',
        component: GoalsIndex,
        meta: { title: 'My Goals — Saving Challenge' },
    },
    {
        path: '/goals/:id',
        name: 'goals.show',
        component: GoalShow,
        meta: { title: 'Goal Detail — Saving Challenge' },
    },
    {
        path: '/calculator',
        name: 'calculator',
        component: Calculator,
        meta: { title: 'Calculator — Saving Challenge' },
    },
    {
        path: '/transactions',
        name: 'transactions',
        component: Transactions,
        meta: { title: 'Transaction Report — Saving Challenge' },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

export default routes;
