import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Configuration de l'API - À modifier selon votre serveur
const API_BASE_URL = 'http://192.168.1.100/gestion-chantiers/api';

// Instance axios
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Intercepteur pour ajouter le token à chaque requête
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('userToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expiré ou invalide
      AsyncStorage.removeItem('userToken');
      AsyncStorage.removeItem('userData');
      // Rediriger vers login (géré par l'app)
    }
    return Promise.reject(error);
  }
);

// Services API

export const authService = {
  login: async (username, password) => {
    const response = await api.post('/auth.php', { username, password });
    return response.data;
  },

  getUser: async () => {
    const response = await api.get('/auth.php');
    return response.data;
  },
};

export const statsService = {
  getStats: async () => {
    const response = await api.get('/stats.php');
    return response.data;
  },
};

export const chantiersService = {
  getAll: async () => {
    const response = await api.get('/chantiers.php');
    return response.data;
  },

  getById: async (id) => {
    const response = await api.get(`/chantiers.php?id=${id}`);
    return response.data;
  },

  create: async (data) => {
    const response = await api.post('/chantiers.php', data);
    return response.data;
  },

  update: async (id, data) => {
    const response = await api.put(`/chantiers.php?id=${id}`, data);
    return response.data;
  },

  delete: async (id) => {
    const response = await api.delete(`/chantiers.php?id=${id}`);
    return response.data;
  },
};

export const postesService = {
  getByChantier: async (chantierId) => {
    const response = await api.get(`/postes.php?chantier_id=${chantierId}`);
    return response.data;
  },

  getById: async (id) => {
    const response = await api.get(`/postes.php?id=${id}`);
    return response.data;
  },

  create: async (data) => {
    const response = await api.post('/postes.php', data);
    return response.data;
  },

  update: async (id, data) => {
    const response = await api.put(`/postes.php?id=${id}`, data);
    return response.data;
  },
};

export const depensesService = {
  create: async (data) => {
    const response = await api.post('/depenses.php', data);
    return response.data;
  },

  update: async (id, data) => {
    const response = await api.put(`/depenses.php?id=${id}`, data);
    return response.data;
  },
};

export const financeursService = {
  getAll: async () => {
    const response = await api.get('/financeurs.php');
    return response.data;
  },

  create: async (data) => {
    const response = await api.post('/financeurs.php', data);
    return response.data;
  },
};

export const financementsService = {
  getByChantier: async (chantierId) => {
    const response = await api.get(`/financements.php?chantier_id=${chantierId}`);
    return response.data;
  },

  getById: async (id) => {
    const response = await api.get(`/financements.php?id=${id}`);
    return response.data;
  },

  create: async (data) => {
    const response = await api.post('/financements.php', data);
    return response.data;
  },
};

export const versementsService = {
  create: async (data) => {
    const response = await api.post('/versements.php', data);
    return response.data;
  },
};

export default api;
