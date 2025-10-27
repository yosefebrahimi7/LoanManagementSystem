import type { ReactNode } from 'react';

export interface User {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  isActive: boolean;
  role: number;
  roleName: string;
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

export interface Loan {
  id: number;
  user_id: number;
  amount: number;
  term_months: number;
  interest_rate: number;
  monthly_payment: number;
  remaining_balance: number;
  status: string;
  start_date: string;
  approved_at?: string;
  approved_by?: number;
  rejection_reason?: string;
  created_at: string;
  updated_at: string;
  user?: User;
  approved_by_user?: User;
  schedules?: LoanSchedule[];
  payments?: LoanPayment[];
}

export interface LoanSchedule {
  id: number;
  loan_id: number;
  installment_number: number;
  amount_due: number;
  principal_amount: number;
  interest_amount: number;
  penalty_amount: number;
  paid_amount: number;
  due_date: string;
  paid_at?: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface LoanPayment {
  id: number;
  loan_id: number;
  user_id: number;
  amount: number;
  method: string;
  status: string;
  gateway_reference?: string;
  created_at: string;
  updated_at: string;
}

export interface LoanRequestDto {
  amount: number;
  term_months: number;
  interest_rate?: number;
  start_date: string;
}

export interface LoanApprovalDto {
  action: 'approve' | 'reject';
  rejection_reason?: string;
}

export interface Notification {
  id: string;
  type: string;
  data: {
    message?: string;
    type?: string;
    loan_id?: number;
    user_id?: number;
    [key: string]: any;
  };
  read_at: string | null;
  created_at: string;
}

