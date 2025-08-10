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
    const token = localStorage.getItem('token');
    const response = await fetch('http://127.0.0.1:8000/api/v1/auth/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      throw new Error('Erro no logout');
    }

    localStorage.removeItem('token');
    window.location.href = '/login';

  } catch (err) {
    alert('Não foi possível sair: ' + err.message);
  }
};

export const getUserByUsername = async (username) => {
  if (!username) throw new Error('Username é obrigatório para buscar usuário');

  const response = await fetch(`http://127.0.0.1:8000/api/v1/${username}`, {
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Não foi possível carregar o usuário: ${username}`);
  }

  return response.json();
};

export const checkIfFollowing = async (username) => {
  const token = localStorage.getItem('token');
  if (!token) throw new Error('Token de autenticação não encontrado.');

  const res = await fetch(`http://127.0.0.1:8000/api/v1/user/${encodeURIComponent(username)}/followers`, {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) throw new Error('Erro ao buscar seguidores');

  const json = await res.json();

  if (json.status !== 'success' || !Array.isArray(json.dados)) {
    return false;
  }

  const myUser = await getMyUserData();
  return json.dados.some(follower => follower.id === myUser.usuario.id);
};

export const followUser = async (username) => {
  const token = localStorage.getItem('token');
  if (!token) throw new Error('Token de autenticação não encontrado.');

  const res = await fetch(`http://127.0.0.1:8000/api/v1/user/${username}/follow`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) throw new Error('Erro ao seguir usuário');
  return await res.json();
};

export const unfollowUser = async (username) => {
  const token = localStorage.getItem('token');
  if (!token) throw new Error('Token de autenticação não encontrado.');

  const res = await fetch(`http://127.0.0.1:8000/api/v1/user/${username}/unfollow`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) throw new Error('Erro ao deixar de seguir usuário');
  return await res.json();
};
