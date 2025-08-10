import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/Auth/LoginView.vue'
import RegisterView from '../views/Auth/RegisterView.vue'
import PaginaInicial from '../views/Logado/PaginaInicial.vue'

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
  ],
})

// Guard global para checar autenticação
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Rotas que não precisam de autenticação
  const publicPages = ['/login', '/signup']
  const authNotRequired = publicPages.includes(to.path)

  if (!token && !authNotRequired) {
    // Se não tem token e vai para página protegida, manda pro login
    return next('/login')
  }

  if (token && authNotRequired) {
    // Se tem token e vai para login ou signup, manda pra página inicial
    return next('/')
  }

  next() // segue normalmente
})

export default router
