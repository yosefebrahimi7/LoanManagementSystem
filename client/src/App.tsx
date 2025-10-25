import { createBrowserRouter, RouterProvider } from "react-router";
import Home from "./pages/Home";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import Users from "./pages/Users";
import Profile from "./pages/Profile";
import LoanRequest from "./pages/LoanRequest";
import LoanApproval from "./pages/LoanApproval";
import LoanDetails from "./pages/LoanDetails";
import ProtectedRoute from "./components/ProtectedRoute";
import Layout from "./components/Layout";

const router = createBrowserRouter([
  {
    path: "/",
    element: (
      <Layout>
        <Home />
      </Layout>
    ),
  },
  {
    path: "/login",
    element: <Login />,
  },
  {
    path: "/register",
    element: <Register />,
  },
  {
    path: "/dashboard",
    element: (
      <Layout>
        <ProtectedRoute>
          <Dashboard />
        </ProtectedRoute>
      </Layout>
    ),
  },
  {
    path: "/users",
    element: (
      <Layout>
        <ProtectedRoute>
          <Users />
        </ProtectedRoute>
      </Layout>
    ),
  },
  {
    path: "/profile",
    element: (
      <Layout>
        <ProtectedRoute>
          <Profile />
        </ProtectedRoute>
      </Layout>
    ),
  },
  {
    path: "/loan-request",
    element: (
      <Layout>
        <ProtectedRoute>
          <LoanRequest />
        </ProtectedRoute>
      </Layout>
    ),
  },
        {
          path: "/loan-approval",
          element: (
            <Layout>
              <ProtectedRoute>
                <LoanApproval />
              </ProtectedRoute>
            </Layout>
          ),
        },
        {
          path: "/loan-details/:id",
          element: (
            <Layout>
              <ProtectedRoute>
                <LoanDetails />
              </ProtectedRoute>
            </Layout>
          ),
        },
]);

function App() {
  return <RouterProvider router={router} />;
}

export default App;
