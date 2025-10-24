import { createBrowserRouter, RouterProvider } from "react-router";
import Home from "./pages/Home";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import Users from "./pages/Users";
import Profile from "./pages/Profile";
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
      <ProtectedRoute>
        <Dashboard />
      </ProtectedRoute>
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
]);

function App() {
  return <RouterProvider router={router} />;
}

export default App;
