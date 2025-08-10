<template>
  <aside class="sidebar sketch-style">
    <div class="sidebar-top">
      <div class="logo">
        <div class="logo-icon"></div>
        <span class="logo-text">Mober</span>
      </div>

      <nav class="nav-menu">
        <ul>
          <li class="nav-item">
            <a href="#">
              <div class="icon-container home-icon"></div>
              <span>Página inicial</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#">
              <div class="icon-container search-icon"></div>
              <span>Pesquisar</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#">
              <div class="icon-container bell-icon"></div>
              <span>Notificações</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#">
              <div class="icon-container plus-icon"></div>
              <span>Criar</span>
            </a>
          </li>

          <li v-if="usuario && usuario.username" class="nav-item">
            <a :href="`/${usuario.username}`">
              <div class="icon-container profile-icon">
                <img
                  :src="usuario.avatar_url"
                  :alt="`Avatar de ${usuario.username}`"
                  class="avatar"
                />
              </div>
              <span>Perfil</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="more-options-wrapper" ref="moreOptionsWrapper">
      <div
        class="more-options"
        @click="toggleMoreOptions"
        tabindex="0"
        @keydown.enter.prevent="toggleMoreOptions"
      >
        <div class="icon-container more-icon"></div>
        <span>Mais</span>
      </div>

      <div v-if="showMoreOptions" class="pop-up sketch-style">
        <ul>
          <li class="pop-up-item">
            <a href="#">
              <div class="icon-container settings-icon"></div>
              <span>Configurações</span>
            </a>
          </li>
          <li class="pop-up-item">
            <a href="#" @click.prevent="logout">
              <div class="icon-container logout-icon"></div>
              <span>Sair</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { getMyAvatar, logout } from '@/services/apiService';

const usuario = ref(null);
const loading = ref(false);
const error = ref(null);

const showMoreOptions = ref(false);

const toggleMoreOptions = () => {
  showMoreOptions.value = !showMoreOptions.value;
};

const moreOptionsWrapper = ref(null);
const handleClickOutside = (event) => {
  if (
    showMoreOptions.value &&
    moreOptionsWrapper.value &&
    !moreOptionsWrapper.value.contains(event.target)
  ) {
    showMoreOptions.value = false;
  }
};

onMounted(async () => {
  loading.value = true;
  error.value = null;

  try {
    const apiData = await getMyAvatar();
    if (apiData && apiData.usuario) {
      usuario.value = apiData.usuario;
    }
  } catch (err) {
    error.value = err.message || 'Erro desconhecido';
  } finally {
    loading.value = false;
  }

  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&family=Indie+Flower&display=swap');

:root {
  --color-lilac: #d0bfff;
  --color-border: #2c2c2c;
  --color-dark-text: #2c2c2c;
  --color-bg-light: #ffffff;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 32px;
}

.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 15vw;
  padding: var(--spacing-lg) var(--spacing-md);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background-color: #d0bfff;
  border-right: 2px solid var(--color-border);
  font-family: 'Kalam', cursive;
  color: var(--color-dark-text);
}
.sidebar-top {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: var(--spacing-md);
}
.logo-icon {
  width: 40px;
  height: 40px;
  border: 2px solid var(--color-border);
  position: relative;
  transform: rotate(45deg);
}
.logo-icon::before,
.logo-icon::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 2px;
  background: var(--color-border);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.logo-icon::before {
  transform: translate(-50%, -50%) rotate(90deg);
}
.logo-text {
  font-family: 'Kalam', cursive;
  font-size: 2rem;
  font-weight: 700;
}
.nav-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.nav-item {
  margin-bottom: var(--spacing-sm);
}
.nav-item.active a {
  background-color: var(--color-bg-light);
  border: 2px solid var(--color-border);
  border-radius: 5px;
}
.nav-item a {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  text-decoration: none;
  color: var(--color-dark-text);
  font-size: 1.2rem;
  padding: 10px;
  transition: all 0.2s;
  border-radius: 5px;
}
.nav-item a:hover {
  background-color: rgba(0, 0, 0, 0.05);
}
.icon-container {
  width: 24px;
  height: 24px;
  border: 2px solid var(--color-border);
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}
.avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.profile-icon {
  border-radius: 50%;
  overflow: hidden;
}
.nav-item a span,
.more-options span {
  display: block;
}
.home-icon::before {
  content: '';
  width: 60%;
  height: 60%;
  border-left: 2px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
  transform: rotate(-45deg) translateY(-2px) translateX(2px);
}
.home-icon::after {
  content: '';
  position: absolute;
  width: 2px;
  height: 40%;
  background: var(--color-border);
  bottom: 0;
  right: 50%;
  transform: translateX(1px);
}
.search-icon::before {
  content: '';
  width: 60%;
  height: 60%;
  border: 2px solid var(--color-border);
  border-radius: 50%;
  margin-right: 5px;
  transform: translateY(-2px);
}
.search-icon::after {
  content: '';
  position: absolute;
  width: 2px;
  height: 40%;
  background: var(--color-border);
  bottom: 0;
  right: 0;
  transform: rotate(45deg);
  transform-origin: bottom right;
}
.bell-icon::before {
  content: '';
  width: 60%;
  height: 60%;
  border: 2px solid var(--color-border);
  border-radius: 50% 50% 0 0;
  border-bottom: none;
}
.bell-icon::after {
  content: '';
  position: absolute;
  width: 10px;
  height: 5px;
  background: var(--color-border);
  bottom: 0;
  border-radius: 5px 5px 0 0;
}
.plus-icon::before,
.plus-icon::after {
  content: '';
  position: absolute;
  width: 70%;
  height: 2px;
  background: var(--color-border);
}
.plus-icon::after {
  transform: rotate(90deg);
}
.more-options {
  cursor: pointer;
  padding: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.2rem;
  transition: background-color 0.2s;
  border-radius: 5px;
}
.more-options:hover,
.more-options:focus {
  background-color: rgba(0, 0, 0, 0.05);
  outline: none;
}
.more-icon {
  width: 24px;
  height: 24px;
  border: 2px solid var(--color-border);
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 0 4px;
  border-radius: 5px;
}
.more-icon::before,
.more-icon::after {
  content: '';
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: var(--color-border);
  box-shadow: 0 -8px 0 var(--color-border);
}

/* Pop-up do menu "Mais" */
.more-options-wrapper {
  position: relative;
  display: inline-block;
}

.pop-up {
  position: absolute;
  bottom: 110%;
  right: 0;
  background-color: var(--color-bg-light);
  border: 2px solid var(--color-border);
  border-radius: 5px;
  box-shadow: 0 -8px 16px rgba(0,0,0,0.2);
  z-index: 2000;
  min-width: 250px;
  padding: var(--spacing-sm) 0;
}

.pop-up ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.pop-up-item {
  padding: 8px 16px;
}

.pop-up-item a {
  text-decoration: none;
  color: var(--color-dark-text);
  display: flex;
  align-items: center;
  gap: 10px;
}

.pop-up-item a:hover {
  background-color: var(--color-lilac);
  color: var(--color-border);
}

/* Ícones simples usando emojis para o pop-up */
.settings-icon::before {
  content: '⚙️';
  font-size: 18px;
}

.logout-icon::before {
  content: '🚪';
  font-size: 18px;
}

/* Responsivo */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    width: 100%;
    padding: 0 10px;
    border-bottom: none;
    border-top: 2px solid var(--color-border);
    background-color: var(--color-lilac);
    box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
    z-index: 1000;
  }

  .sidebar-top {
    flex-direction: row;
    align-items: center;
    gap: 0;
    width: 100%;
    justify-content: space-around;
  }

  .logo-text,
  .more-options span {
    display: none;
  }

  .nav-menu ul {
    display: flex;
    justify-content: space-around;
    align-items: center;
    width: 100%;
    margin: 0;
    padding: 0;
  }

  .nav-item {
    margin-bottom: 0;
  }

  .nav-item a {
    padding: 5px;
    font-size: 0;
    flex-direction: column;
    justify-content: center;
  }

  .nav-item a span {
    display: none;
  }

  .icon-container,
  .avatar {
    width: 28px;
    height: 28px;
    border-width: 1.5px;
  }
}
</style>
