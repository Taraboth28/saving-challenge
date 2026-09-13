import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// ── Goals ──────────────────────────────────────────────────
export const goalsApi = {
    index: ()          => api.get('/goals'),
    show: (id)         => api.get(`/goals/${id}`),
    store: (data)      => api.post('/goals', data),
    update: (id, data) => api.put(`/goals/${id}`, data),
    destroy: (id)      => api.delete(`/goals/${id}`),
};

// ── History ────────────────────────────────────────────────
export const historyApi = {
    index: (goalId)      => api.get(`/goals/${goalId}/history`),
    store: (goalId, data) => api.post(`/goals/${goalId}/history`, data),
    destroy: (id)        => api.delete(`/history/${id}`),
    report: ()           => api.get('/transactions'),
};

// ── Dashboard ──────────────────────────────────────────────
export const dashboardApi = {
    stats: () => api.get('/dashboard/stats'),
};

export default api;
