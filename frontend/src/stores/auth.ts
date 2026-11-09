import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '../services/api';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('pmr_admin_token') || null);
  const username = ref<string | null>(localStorage.getItem('pmr_admin_username') || null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed(() => !!token.value);

  async function login(creds: { username: string; password: string }) {
    loading.value = true;
    error.value = null;
    try {
      const data = await api.login(creds);
      token.value = data.token;
      const uname = data.user?.username || data.admin?.username || creds.username;
      username.value = uname;

      localStorage.setItem('pmr_admin_token', data.token);
      localStorage.setItem('pmr_admin_username', uname);
      return data;
    } catch (err: any) {
      error.value = err.message || 'Login gagal';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await api.logout();
    } catch {
      // Ignored
    }
    token.value = null;
    username.value = null;
    localStorage.removeItem('pmr_admin_token');
    localStorage.removeItem('pmr_admin_username');
  }

  function updateCredentials(newUsername: string, newToken?: string) {
    username.value = newUsername;
    localStorage.setItem('pmr_admin_username', newUsername);
    if (newToken) {
      token.value = newToken;
      localStorage.setItem('pmr_admin_token', newToken);
    }
  }

  async function checkAuth() {
    if (!token.value) return false;
    try {
      const user = await api.getUser();
      if (user?.username) {
        username.value = user.username;
        localStorage.setItem('pmr_admin_username', user.username);
        return true;
      }
    } catch {
      logout();
      return false;
    }
    return true;
  }

  return {
    token,
    username,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    updateCredentials,
    checkAuth,
  };
});
