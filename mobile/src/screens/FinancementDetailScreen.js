import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, Alert } from 'react-native';
import { Card, Title, Text, Divider, ProgressBar } from 'react-native-paper';
import { useRoute } from '@react-navigation/native';
import { financementsService } from '../services/api';
import {
  formatMontant,
  formatDate,
  calculatePercentage,
  translateStatus,
  getStatusColor,
} from '../utils/helpers';

export default function FinancementDetailScreen() {
  const [financement, setFinancement] = useState(null);
  const [loading, setLoading] = useState(true);
  const route = useRoute();
  const { id } = route.params;

  useEffect(() => {
    loadFinancement();
  }, [id]);

  const loadFinancement = async () => {
    try {
      const response = await financementsService.getById(id);
      if (response.success) {
        setFinancement(response.financement);
      }
    } catch (error) {
      console.error('Error loading financement:', error);
      Alert.alert('Erreur', 'Impossible de charger les données');
    } finally {
      setLoading(false);
    }
  };

  if (loading || !financement) {
    return (
      <View style={styles.centerContainer}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  const percentage = calculatePercentage(
    financement.montant_verse,
    financement.montant_prevu
  );
  const restant = financement.montant_prevu - financement.montant_verse;

  return (
    <ScrollView style={styles.container}>
      <Card style={styles.card}>
        <Card.Content>
          <Title>{financement.financeur_nom}</Title>
          <Text style={styles.type}>Type: {financement.financeur_type}</Text>
          <Divider style={{ marginVertical: 10 }} />
          <Text style={styles.label}>Montant prévu</Text>
          <Text style={styles.value}>{formatMontant(financement.montant_prevu)}</Text>
          <Text style={styles.label}>Montant versé</Text>
          <Text style={styles.value}>{formatMontant(financement.montant_verse)}</Text>
          <Text style={styles.label}>Restant à verser</Text>
          <Text style={[styles.value, { color: restant > 0 ? '#f39c12' : '#27ae60' }]}>
            {formatMontant(restant)}
          </Text>
          <ProgressBar
            progress={percentage / 100}
            color={percentage >= 100 ? '#27ae60' : '#f39c12'}
            style={styles.progressBar}
          />
          <Text style={styles.percentage}>{percentage}% versé</Text>
        </Card.Content>
      </Card>

      {financement.versements && financement.versements.length > 0 && (
        <Card style={styles.card}>
          <Card.Content>
            <Title>Historique des versements</Title>
            {financement.versements.map((versement, index) => (
              <View key={index} style={styles.versementItem}>
                <View style={styles.versementHeader}>
                  <Text style={styles.versementMontant}>
                    {formatMontant(versement.montant)}
                  </Text>
                  <Text style={styles.versementDate}>
                    {formatDate(versement.date_versement)}
                  </Text>
                </View>
                {versement.reference && (
                  <Text style={styles.versementReference}>
                    Réf: {versement.reference}
                  </Text>
                )}
                {versement.notes && (
                  <Text style={styles.versementNotes}>{versement.notes}</Text>
                )}
              </View>
            ))}
          </Card.Content>
        </Card>
      )}
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
  type: {
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
  versementItem: {
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  versementHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  versementMontant: {
    fontWeight: 'bold',
    fontSize: 16,
    color: '#27ae60',
  },
  versementDate: {
    color: '#7f8c8d',
  },
  versementReference: {
    color: '#7f8c8d',
    fontSize: 14,
    marginTop: 3,
  },
  versementNotes: {
    color: '#95a5a6',
    fontSize: 12,
    marginTop: 3,
  },
});
