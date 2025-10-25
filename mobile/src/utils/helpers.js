/**
 * Fonctions utilitaires pour l'application mobile
 */

// Formater un montant en euros
export const formatMontant = (montant) => {
  if (montant === null || montant === undefined) return '0,00 €';
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(montant);
};

// Formater une date
export const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

// Formater une date courte
export const formatDateShort = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR');
};

// Calculer un pourcentage
export const calculatePercentage = (part, total) => {
  if (!total || total === 0) return 0;
  return Math.round((part / total) * 100);
};

// Traduire les statuts
export const translateStatus = (status) => {
  const translations = {
    planification: 'Planification',
    en_cours: 'En cours',
    suspendu: 'Suspendu',
    termine: 'Terminé',
    annule: 'Annulé',
    en_negociation: 'En négociation',
    accorde: 'Accordé',
    verse_partiel: 'Versé partiellement',
    verse_total: 'Versé totalement',
    refuse: 'Refusé',
    prevue: 'Prévue',
    engagee: 'Engagée',
    payee: 'Payée',
  };
  return translations[status] || status;
};

// Obtenir la couleur pour un statut
export const getStatusColor = (status) => {
  const colors = {
    planification: '#95a5a6',
    en_cours: '#3498db',
    suspendu: '#f39c12',
    termine: '#27ae60',
    annule: '#e74c3c',
    en_negociation: '#3498db',
    accorde: '#27ae60',
    verse_partiel: '#f39c12',
    verse_total: '#27ae60',
    refuse: '#e74c3c',
    prevue: '#95a5a6',
    engagee: '#f39c12',
    payee: '#27ae60',
  };
  return colors[status] || '#95a5a6';
};

// Obtenir la couleur pour une barre de progression
export const getProgressColor = (percentage) => {
  if (percentage >= 90) return '#e74c3c';
  if (percentage >= 75) return '#f39c12';
  return '#27ae60';
};
