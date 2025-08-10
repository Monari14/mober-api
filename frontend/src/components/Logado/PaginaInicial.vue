<template>
  <div class="main-layout sketch-style">
    <Sidebar />

    <main class="profile-main-content">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Carregando dados...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <p>Ocorreu um erro: {{ error }}</p>
      </div>

      <transition name="fade">
        <div v-if="!loading && !error" class="profile-content">
          <div class="profile-header">
            <div class="avatar-container">
              <div class="avatar-sketch"></div>
              <img
                :src="usuario.avatar_url || '/default-avatar.png'"
                :alt="`Avatar de ${usuario.username || 'Usuário'}`"
                class="avatar"
              />
            </div>
            <div class="user-info">
              <div class="user-main-info">
                <div class="profile-details">
                  <h1 class="username">{{ usuario.username || 'username' }}</h1>
                  <h2 class="name">{{ usuario.nome || 'Usuário' }}</h2>
                  <p class="bio" v-if="usuario.bio">{{ usuario.bio }}</p>
                </div>
                <div class="profile-actions">
                  <button class="action-button follow-button">Follow</button>
                  <button class="action-button more-button">
                    <div class="dots-sketch"></div>
                  </button>
                </div>
              </div>
              <div class="stats">
                <div class="stat">
                  <span class="stat-number">{{
                    usuario.stats?.mober_count ?? 0
                  }}</span>
                  <span class="stat-label">mobers</span>
                </div>
                <div class="stat">
                  <span class="stat-number">{{
                    usuario.stats?.seguidores ?? 0
                  }}</span>
                  <span class="stat-label">seguidores</span>
                </div>
                <div class="stat">
                  <span class="stat-number">{{
                    usuario.stats?.seguindo ?? 0
                  }}</span>
                  <span class="stat-label">seguindo</span>
                </div>
              </div>
            </div>
          </div>

          <div
            class="moments-grid masonry"
            v-if="dados.momentos && dados.momentos.length > 0"
          >
            <div
              v-for="momento in dados.momentos"
              :key="momento.id"
              class="moment-item"
            >
              <img
                :src="momento.fotos[0]?.url"
                :alt="`Foto do momento ${momento.id}`"
                class="moment-photo"
              />
            </div>
          </div>
          <div v-else class="no-moments">
            <p>Nenhum mober publicado ainda.</p>
          </div>
        </div>
      </transition>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getMyUserData } from '@/services/apiService';
import Sidebar from '@/components/Reutilizavel/Sidebar.vue';

const usuario = ref({
  nome: '',
  username: '',
  bio: '',
  avatar_url: '',
  stats: {
    seguindo: 0,
    seguidores: 0,
    mober_count: 0,
    likes_count: 0,
  },
});
const dados = ref({ momentos: [] });
const loading = ref(false);
const error = ref(null);

onMounted(async () => {
  loading.value = true;
  error.value = null;

  try {
    const apiData = await getMyUserData();
    usuario.value = apiData.usuario || {};
    dados.value = apiData || { momentos: [] };
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Importando fontes de escrita manual */
@import url('https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&family=Indie+Flower&display=swap');

/* Variáveis para o estilo sketch */
:root {
  --color-lilac: #d0bfff;
  --color-border: #2c2c2c;
  --color-dark-text: #2c2c2c;
  --color-bg-light: #ffffff;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 32px;
}

/* Estilo geral da página */
.main-layout {
  display: flex;
  min-height: 100vh;
  min-width: 70vw;
  background-color: #ffffff;
  font-family: 'Kalam', cursive;
  color: var(--color-dark-text);
}

.profile-main-content {
  flex-grow: 1;
  padding: var(--spacing-lg);
  overflow-y: auto;
  border-left: 2px solid var(--color-border);
}

.profile-content {
  max-width: 50vw;
  margin: 0 auto;
}

/* Transições */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Estados de carregamento e erro */
.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 32px; /* Aumentado de 16px para 32px */
  height: 500px; /* Aumentado de 400px para 500px */
  font-size: 1.5rem;
  font-family: 'Indie Flower', cursive;
}
.spinner {
  border: 4px dashed var(--color-border);
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Header do perfil */
.profile-header {
  display: flex;
  gap: 48px; /* Aumentado de 32px para 48px */
  margin-bottom: 48px; /* Aumentado de 32px para 48px */
  padding: 24px; /* Aumentado de 16px para 24px */
  background-color: var(--color-lilac);
  border-bottom: 2px solid var(--color-border);
  position: relative;
}

.avatar-container {
  position: relative;
  width: 120px;
  height: 120px;
  margin-right: 32px; /* Espaçamento extra à direita */
}
.avatar-sketch {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 2px dashed var(--color-border);
  border-radius: 50%;
  z-index: 1;
}
.avatar-sketch::before,
.avatar-sketch::after {
  content: '';
  position: absolute;
  width: 70%;
  height: 2px;
  background: var(--color-border);
  top: 50%;
  left: 50%;
  transform-origin: center;
}
.avatar-sketch::before {
  transform: translate(-50%, -50%) rotate(45deg);
}
.avatar-sketch::after {
  transform: translate(-50%, -50%) rotate(-45deg);
}
.avatar {
  border: 2px solid #2c2c2c;
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  display: block;
  z-index: 2;
  opacity: 0.8; /* Suaviza a foto para o estilo */
}

.user-info {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  flex-grow: 1;
  gap: 24px; /* Aumentado de 16px para 24px */
}

.user-main-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 16px; /* Aumentado de 8px para 16px */
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 12px; /* Aumentado de 8px para 12px */
}

.username {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}
.name {
  font-size: 1.2rem;
  font-weight: 400;
  margin: 0;
}
.bio {
  font-size: 1rem;
  margin-top: var(--spacing-sm);
  line-height: 1.4;
  font-family: 'Indie Flower', cursive;
  max-width: 400px;
}

.profile-actions {
  display: flex;
  gap: 16px; /* Aumentado de 8px para 16px */
}
.action-button {
  font-family: 'Kalam', cursive;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 8px 20px;
  border: 2px solid var(--color-border);
  background-color: transparent;
  cursor: pointer;
  border-radius: 5px;
  position: relative;
}
.follow-button {
  background-color: var(--color-dark-text);
  color: var(--color-bg-light);
}
.more-button {
  width: 45px;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dots-sketch {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 0 4px;
}
.dots-sketch::before,
.dots-sketch::after {
  content: '';
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background-color: var(--color-dark-text);
  box-shadow: 0 0 0 var(--color-dark-text);
}
.dots-sketch::before {
  box-shadow: 0 0 0 var(--color-dark-text), 0 -8px 0 var(--color-dark-text);
}

.stats {
  display: flex;
  gap: 48px; /* Aumentado de 32px para 48px */
  font-size: 1.2rem;
  margin-top: 32px; /* Aumentado de 16px para 32px */
}
.stat {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.stat-number {
  font-weight: 700;
}
.stat-label {
  font-weight: 400;
  color: var(--color-dark-text);
}

/* Grid de Momentos - Estilo Masonry */
.moments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  grid-auto-rows: 1fr;
  gap: 1px; /* Aumentado de 8px para 16px */
}
.moment-item {
  position: relative;
  overflow: hidden;
}

.moment-photo {
  position: relative;
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  z-index: 2;
  transition: transform 0.3s ease;
  opacity: 0.8;
}
.moment-item:hover .moment-photo {
  transform: scale(1.05);
}

.no-moments {
  text-align: center;
  font-size: 1.2rem;
  padding: var(--spacing-lg);
  font-family: 'Indie Flower', cursive;
}

/* Responsivo */
@media (max-width: 768px) {
  .profile-main-content {
    padding: var(--spacing-md);
  }
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--spacing-md);
  }
  .user-info {
    text-align: center;
    align-items: center;
  }
  .user-main-info {
    flex-direction: column;
    align-items: center;
  }
  .profile-details {
    align-items: center;
  }
  .profile-actions {
    justify-content: center;
  }
  .stats {
    justify-content: center;
  }
}

</style>