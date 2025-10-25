import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, Alert } from 'react-native';
import { Card, Title, Text, ProgressBar, Button, Divider } from 'react-native-paper';
import { useNavigation, useRoute } from '@react-navigation/native';
import { postesService } from '../services/api';
import {
  formatMontant,
  formatDate,
  calculatePercentage,
  getProgressColor,
  translateStatus,
  getStatusColor,
} from '../utils/helpers';

export default function PosteDetailScreen() {
  const [poste, setPoste] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigation = useNavigation();
  const route = useRoute();
  const { id } = route.params;

  useEffect(() => {
    loadPoste();
  }, [id]);

  const loadPoste = async () => {
    try {
      const response = await postesService.getById(id);
      if (response.success) {
        setPoste(response.poste);
      }
    } catch (error) {
      console.error('Error loading poste:', error);
      Alert.alert('Erreur', 'Impossible de charger les données');
    } finally {
      setLoading(false);
    }
  };

  if (loading || !poste) {
    return (
      <View style={styles.centerContainer}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  const percentage = calculatePercentage(poste.budget_consomme, poste.budget_alloue);
  const restant = poste.budget_alloue - poste.budget_consomme;

  return (
    <ScrollView style={styles.container}>
      <Card style={styles.card}>
        <Card.Content>
          <Title>{poste.nom}</Title>
          {poste.description && (
            <Text style={styles.description}>{poste.description}</Text>
          )}
          <Divider style={{ marginVertical: 10 }} />
          <Text style={styles.label}>Budget alloué</Text>
          <Text style={styles.value}>{formatMontant(poste.budget_alloue)}</Text>
          <Text style={styles.label}>Budget consommé</Text>
          <Text style={styles.value}>{formatMontant(poste.budget_consomme)}</Text>
          <Text style={styles.label}>Restant</Text>
          <Text style={[styles.value, { color: restant < 0 ? '#e74c3c' : '#27ae60' }]}>
            {formatMontant(restant)}
          </Text>
          <ProgressBar
            progress={percentage / 100}
            color={getProgressColor(percentage)}
            style={styles.progressBar}
          />
          <Text style={styles.percentage}>{percentage}% consommé</Text>
        </Card.Content>
      </Card>

      <Card style={styles.card}>
        <Card.Content>
          <View style={styles.sectionHeader}>
            <Title>Dépenses</Title>
            <Button
              mode="contained"
              compact
              onPress={() =>
                navigation.navigate('DepenseForm', { poste_id: id })
              }
            >
              Ajouter
            </Button>
          </View>

          {!poste.depenses || poste.depenses.length === 0 ? (
            <Text style={styles.emptyText}>Aucune dépense</Text>
          ) : (
            poste.depenses.map((depense, index) => (
              <View key={index} style={styles.depenseItem}>
                <Text style={styles.depenseName}>{depense.description}</Text>
                <Text style={styles.depenseFournisseur}>{depense.fournisseur}</Text>
                <View style={styles.depenseFooter}>
                  <Text style={styles.depenseMontant}>
                    {formatMontant(depense.montant)}
                  </Text>
                  <Text style={styles.depenseDate}>
                    {formatDate(depense.date_depense)}
                  </Text>
                </View>
              </View>
            ))
          )}
        </Card.Content>
      </Card>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  card: {
    margin: 10,
  },
  description: {
    color: '#7f8c8d',
    marginTop: 5,
  },
  label: {
    fontSize: 12,
    color: '#7f8c8d',
    marginTop: 10,
  },
  value: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2c3e50',
  },
  progressBar: {
    marginTop: 15,
    height: 10,
    borderRadius: 5,
  },
  percentage: {
    textAlign: 'center',
    marginTop: 5,
    color: '#7f8c8d',
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  depenseItem: {
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  depenseName: {
    fontWeight: 'bold',
    fontSize: 16,
  },
  depenseFournisseur: {
    color: '#7f8c8d',
    fontSize: 14,
    marginTop: 2,
  },
  depenseFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 5,
  },
  depenseMontant: {
    fontWeight: 'bold',
    color: '#e74c3c',
  },
  depenseDate: {
    color: '#7f8c8d',
  },
  emptyText: {
    color: '#95a5a6',
    textAlign: 'center',
    padding: 10,
  },
});
