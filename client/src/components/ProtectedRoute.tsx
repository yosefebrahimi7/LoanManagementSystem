import { Navigate } from 'react-router';
import useAuth from '../stores/auth';
import type { ProtectedRouteProps } from '../types';

function ProtectedRoute({ children }: ProtectedRouteProps) {
  const isAuthenticated = useAuth((state) => state.isAuthenticated());

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
}

export default ProtectedRoute;

