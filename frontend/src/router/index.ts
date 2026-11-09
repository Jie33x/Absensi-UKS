import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Views
import PortalAbsensiView from '../views/PortalAbsensiView.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import AdminLoginView from '../views/AdminLoginView.vue';
import RekapAbsensiView from '../views/RekapAbsensiView.vue';
import DataAnggotaView from '../views/DataAnggotaView.vue';
import PengaturanAdminView from '../views/PengaturanAdminView.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/absensi',
  },
  {
    path: '/absensi',
    name: 'portal-absensi',
    component: PortalAbsensiView,
    meta: {
      title: 'Portal Absensi Digital - PMR Wira',
    },
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: AdminLoginView,
    meta: {
      title: 'Masuk Portal Admin - PMR Wira',
      guestOnly: true,
    },
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: {
      requiresAuth: true,
    },
    children: [
      {
        path: '',
        name: 'admin-root',
        redirect: { name: 'admin-rekap' },
      },
      {
        path: 'rekap',
        name: 'admin-rekap',
        component: RekapAbsensiView,
        meta: {
          title: 'Rekap Absensi - Portal Admin PMR',
        },
      },
      {
        path: 'anggota',
        name: 'admin-anggota',
        component: DataAnggotaView,
        meta: {
          title: 'Data Anggota - Portal Admin PMR',
        },
      },
      {
        path: 'pengaturan',
        name: 'admin-pengaturan',
        component: PengaturanAdminView,
        meta: {
          title: 'Pengaturan Admin - Portal Admin PMR',
        },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/absensi',
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0 };
  },
});

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  const token = localStorage.getItem('pmr_admin_token') || authStore.token;
  const isAuthenticated = !!token;

  if (to.meta.title) {
    document.title = `${to.meta.title}`;
  }

  // Handle protected admin routes
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      return next({ name: 'admin-login', query: { redirect: to.fullPath } });
    }
  }

  // Handle guest-only routes (e.g. login)
  if (to.meta.guestOnly && isAuthenticated) {
    return next({ name: 'admin-rekap' });
  }

  next();
});

export default router;
