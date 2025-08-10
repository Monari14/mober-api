<template>
  <div class="login-container">
    <h2>Login</h2>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="login">Email ou Username:</label>
        <input 
          id="login" 
          type="text" 
          v-model="login" 
          placeholder="Digite seu email ou username"
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

      <button type="submit" :disabled="loading">
        {{ loading ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>
    <p>Não tem uma conta?</p><a href="/signup">Cadastre-se</a>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const login = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const router = useRouter()

const handleLogin = async () => {
  errorMessage.value = ''

  if (!login.value || !password.value) {
    errorMessage.value = 'Por favor, preencha todos os campos.'
    return
  }

  loading.value = true

  try {
    const response = await fetch('http://127.0.0.1:8000/api/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        login: login.value,
        password: password.value
      })
    })

    const data = await response.json()

    if (!response.ok) {
      // 401 ou 422 ou outros erros
      errorMessage.value = data.message || 'Erro no login'
    } else {
      // Login OK
      const token = data.token

      // Salva o token no localStorage
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
/* Mesmo estilo do exemplo anterior */
.login-container {
  max-width: 320px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: Arial, sans-serif;
  background-color: #333;
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
