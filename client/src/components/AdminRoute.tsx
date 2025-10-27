import { Navigate } from 'react-router';
import useAuth from '../stores/auth';
import type { ProtectedRouteProps } from '../types';

function AdminRoute({ children }: ProtectedRouteProps) {
  const { user, isAuthenticated } = useAuth();

  if (!isAuthenticated()) {
    return <Navigate to="/login" replace />;
  }

  if (user?.roleName !== 'admin') {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
}

export default AdminRoute;

