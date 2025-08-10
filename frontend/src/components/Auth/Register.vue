<template>
  <div class="signup-container">
    <h2>Sign-up</h2>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="name">Name:</label>
        <input
          id="name"
          type="text"
          v-model="name"
          placeholder="Digite seu nome"
          required
        />
      </div>

      <div class="form-group">
        <label for="username">Username:</label>
        <input
          id="username"
          type="text"
          v-model="username"
          placeholder="Digite seu username"
          required
        />
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input
          id="email"
          type="email"
          v-model="email"
          placeholder="Digite seu email"
          required
        />
      </div>

      <div class="form-group">
        <label for="password">Senha:</label>
        <input
          id="password"
          type="password"
          v-model="password"
          placeholder="Digite sua senha"
          required
        />
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirme sua senha:</label>
        <input
          id="password_confirmation"
          type="password"
          v-model="password_confirmation"
          placeholder="Confirme sua senha"
          required
        />
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
      </button>
    </form>

    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const name = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')

const router = useRouter()

const handleLogin = async () => {
  errorMessage.value = ''

  if (
    !name.value ||
    !username.value ||
    !email.value ||
    !password.value ||
    !password_confirmation.value
  ) {
    errorMessage.value = 'Por favor, preencha todos os campos.'
    return
  }

  if (password.value !== password_confirmation.value) {
    errorMessage.value = 'As senhas não coincidem.'
    return
  }

  loading.value = true

  try {
    const response = await fetch('http://127.0.0.1:8000/api/v1/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: name.value,
        username: username.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      errorMessage.value = data.message || 'Erro no cadastro'
    } else {
      const token = data.token

      localStorage.setItem('token', token)

      router.push('/')
    }
  } catch (error) {
    errorMessage.value = 'Erro na conexão com a API'
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.signup-container {
  max-width: 320px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: Arial, sans-serif;
  background-color: #333;
  color: white;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 5px;
  font-weight: bold;
}

input {
  padding: 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  border: none;
  color: white;
  font-size: 16px;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background-color: #7aa7e9;
  cursor: not-allowed;
}

.error {
  color: red;
  margin-top: 10px;
  text-align: center;
}
</style>
