import Navbar from './Navbar';
import type { LayoutProps } from '../types';

function Layout({ children }: LayoutProps) {
  return (
    <div className="min-h-screen">
      <Navbar />
      <main>{children}</main>
    </div>
  );
}

export default Layout;

