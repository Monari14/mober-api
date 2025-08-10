export const getMyUserData = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    throw new Error('Token de autenticação não encontrado.');
  }

  const response = await fetch('http://127.0.0.1:8000/api/v1/user/me', {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Erro na requisição: ${response.statusText}`);
  }

  const json = await response.json();
  if (json.status !== 'success' || !json.dados) {
    throw new Error('Resposta da API inválida ou incompleta.');
  }

  return json.dados;
};

export async function getMyAvatar() {
  const token = localStorage.getItem('token');
  if (!token) {
    throw new Error('Token de autenticação não encontrado.');
  }

  const res = await fetch('http://127.0.0.1:8000/api/v1/user/me/avatar', {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) throw new Error('Erro ao buscar avatar');
  return await res.json();
}

export async function logout () {
  try {
    const token = localStorage.getItem('token'); // se usa token no localStorage
    const response = await fetch('http://127.0.0.1:8000/api/v1/auth/logout', {
      method: 'POST', // normalmente logout é POST
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      throw new Error('Erro no logout');
    }

    // Limpa token e redireciona
    localStorage.removeItem('token');
    window.location.href = '/login';

  } catch (err) {
    alert('Não foi possível sair: ' + err.message);
  }
};