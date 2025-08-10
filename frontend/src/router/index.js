import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/Auth/LoginView.vue'
import RegisterView from '../views/Auth/RegisterView.vue'
import PaginaInicial from '../views/Logado/PaginaInicial.vue'
import PerfilUsuario from '../views/Logado/PerfilUsuario.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/signup',
      name: 'signup',
      component: RegisterView,
    },
    {
      path: '/',
      name: 'paginaInicial',
      component: PaginaInicial,
    },
    {
      path: '/:username',
      name: 'perfil',
      component: PerfilUsuario,
    }
  ],
})

// Guard global
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  // Rotas públicas
  const publicRouteNames = ['login', 'signup', 'perfil'];
  const authNotRequired = publicRouteNames.includes(to.name);

  if (!token && !authNotRequired) {
    return next({ name: 'login' });
  }

  if (token && (to.name === 'login' || to.name === 'signup')) {
    return next({ name: 'paginaInicial' });
  }

  next();
});
export default router
