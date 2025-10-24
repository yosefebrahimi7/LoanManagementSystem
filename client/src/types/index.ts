import type { ReactNode } from 'react';

export interface User {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface AuthResponse {
  token: string;
  refreshToken: string;
  user: User;
}

export interface LoginDto {
  email: string;
  password: string;
}

export interface RegisterDto {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
}

export interface ProtectedRouteProps {
  children: ReactNode;
}

export interface LayoutProps {
  children: ReactNode;
}

export interface AuthState {
  user: User | null;
  token: string;
  refreshToken: string;
  setUser: (user: User | null) => void;
  setToken: (token: string) => void;
  setRefreshToken: (refreshToken: string) => void;
  setAuth: (user: User, token: string, refreshToken: string) => void;
  clear: () => void;
  isAuthenticated: () => boolean;
}

