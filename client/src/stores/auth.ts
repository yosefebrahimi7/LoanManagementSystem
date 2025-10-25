import { create } from 'zustand';
import { persist, devtools } from 'zustand/middleware';
import type { AuthState } from '../types';

const useAuth = create<AuthState>()(
  devtools(
    persist(
      (set, get) => ({
        user: null,
        token: '',
        refreshToken: '',
        setUser: (user) => set({ user }),
        setToken: (token) => set({ token }),
        setRefreshToken: (refreshToken) => set({ refreshToken }),
        setAuth: (user, token, refreshToken) => set({ user, token, refreshToken }),
        clear: () => set({ user: null, token: '', refreshToken: '' }),
        isAuthenticated: () => !!get().token,
      }),
      {
        name: 'auth-storage',
      }
    )
  )
);

export { useAuth };
export default useAuth;